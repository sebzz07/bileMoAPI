<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api', name: 'api_')]
class CustomerController extends AbstractController
{
    /**
     * This route with the verb GET give you the list of your customers.
     *
     * @OA\Response(
     *     response=200,
     *     description="Give you the list of your customers",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Customer::class, groups={"getCustomers"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="offset",
     *     in="query",
     *     description="The offset we want to obtain",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="The number of elements to be retrieved",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Customers")
     *
     * @throws InvalidArgumentException
     */
    #[Route('/customers', name: 'customerList', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function getCustomerList(Request $request, CustomerRepository $customerRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();
        if (null === $userId) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $offset = intval($request->get('offset', 1));
        $limit = intval($request->get('limit', 3));

        $idCache = 'getAllCustomers-'.$offset.'-'.$limit.'-userId'.$userId;
        $jsonCustomerList = $cachePool->get($idCache, function (ItemInterface $item) use ($customerRepository, $serializer, $offset, $limit, $userId) {
            $item->tag(['customersCache', 'customersCache-'.$userId]);

            $queryBuilder = $customerRepository->findAllQueryBuilder($userId);
            $pagerfanta = new Pagerfanta(
                new QueryAdapter($queryBuilder)
            );
            $pagerfanta->setMaxPerPage($limit);
            $pagerfanta->setCurrentPage($offset);
            $context = SerializationContext::create()->setGroups(['getCustomers']);

            return $serializer->serialize($pagerfanta, 'json', $context);
        });

        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    /**
     * This route with the verb POST allows you to create a new customer.
     *
     * @OA\RequestBody(@Model(type=Customer::class, groups={"createCustomer"}))
     * @OA\Response(
     *     response=201,
     *     description="Give you the list of your customers",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Customer::class, groups={"getCustomerList"}))
     *     )
     * )
     * @OA\Tag(name="Customers")
     *
     * @throws InvalidArgumentException
     */
    #[Route('/customers', name: 'createCustomer', methods: ['POST'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function createCustomer(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var Customer $customer */
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setUser($user);
        $errors = $validator->validate($customer);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($customer);
        $entityManager->flush();
        $cachePool->invalidateTags(['customersCache-'.$user->getId()]);
        $context = SerializationContext::create()->setGroups(['getCustomers']);
        $jsonCustomer = $serializer->serialize($customer, 'json', $context);

        $location = $urlGenerator->generate('api_customerDetails', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * This route with the verb GET returns to you the customer's details of the entered id.
     *
     * @OA\Tag(name="Customers")
     */
    #[Route('/customers/{id}', name: 'customerDetails', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function getCustomerDetails(Customer $customer, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $customerDetails = $customerRepository->findOneBy(['id' => $customer->getId(), 'user' => $user]);

        if (null === $customerDetails) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $context = SerializationContext::create()->setGroups(['getCustomers']);
        $jsonCustomerList = $serializer->serialize($customerDetails, 'json', $context);

        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    /**
     * This route with the verb DELETE, delete the customer of the specified id.
     *
     * @OA\Tag(name="Customers")
     *
     * @throws InvalidArgumentException
     */
    #[Route('/customers/{id}', name: 'deleteCustomer', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function deleteCustomer(Customer $customer, CustomerRepository $customerRepository, EntityManagerInterface $entityManager, TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $customer = $customerRepository->findOneBy(['id' => $customer->getId(), 'user' => $user]);
        if (null === $customer) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $cachePool->invalidateTags(['customersCache-'.$user->getId()]);
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * This route with the verb PUT allows you to update the customer of the specified id.
     *
     * @OA\Tag(name="Customers")
     * @OA\RequestBody(@Model(type=Customer::class, groups={"createCustomer"}))
     *
     * @throws InvalidArgumentException
     */
    #[Route('/customers/{id}', name: 'updateCustomer', methods: ['PUT'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function updateCustomer(Customer $customer, CustomerRepository $customerRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $updatedCustomer = $customerRepository->findOneBy(['id' => $customer->getId(), 'user' => $user]);

        if (null === $updatedCustomer) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        /** @var Customer $newCustomer */
        $newCustomer = $serializer->deserialize(
            $request->getContent(),
            Customer::class,
            'json'
        );
        if (null === $newCustomer->getEmail() || null === $newCustomer->getLastName() || null === $newCustomer->getFirstName()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $updatedCustomer->setEmail($newCustomer->getEmail());
        $updatedCustomer->setLastName($newCustomer->getLastName());
        $updatedCustomer->setFirstName($newCustomer->getFirstName());

        $errors = $validator->validate(
            $updatedCustomer
        );

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($updatedCustomer);
        $entityManager->flush();
        $cachePool->invalidateTags(['customersCache-'.$user->getId()]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
