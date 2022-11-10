<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api', name: 'api_')]
class CustomerController extends AbstractController
{
    #[Route('/customers', name: 'customerList', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function getCustomerList(Request $request, CustomerRepository $customerRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();
        // $customerList = $customerRepository->findBy(["user" => $user]);

        $offset = $request->get('offset', 1);
        $limit = $request->get('limit', 3);
        // $customerList = $customerRepository->findAllWithPagination($userId, $offset, $limit);

        $idCache = "getAllCustomers-" . $offset . "-" . $limit . "-userId" . $userId;
        $customerList = $cachePool->get($idCache, function (ItemInterface $item) use ($customerRepository, $offset, $limit, $userId) {
            $item->tag(["customersCache", "customersCache-". $userId]);
            return $customerRepository->findAllWithPagination($userId, $offset, $limit);
        });

        if (empty($customerList)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $jsonCustomerList = $serializer->serialize($customerList, 'json', ['groups' => 'getCustomers']);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

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
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($customer);
        $entityManager->flush();
        $cachePool->invalidateTags(["customersCache-". $user->getId()]);
        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getCustomers']);

        $location = $urlGenerator->generate('api_customerDetails', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/customers/{id}', name: 'customerDetails', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function getCustomerDetails(Customer $customer, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $customerDetails = $customerRepository->findOneBy(['id' => $customer->getId(), "user" => $user]);

        if (null === $customerDetails) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $jsonCustomerList = $serializer->serialize($customerDetails, 'json', ['groups' => 'getCustomerDetails']);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    #[Route('/customers/{id}', name: 'deleteCustomer', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function deleteCustomer(Customer $customer, CustomerRepository $customerRepository, EntityManagerInterface $entityManager, TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $customer = $customerRepository->findOneBy(['id' => $customer->getId(), "user" => $user]);
        $cachePool->invalidateTags(["customersCache-". $user->getId()]);
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('customers/{id}', name:"updateCustomer", methods: ['PUT'])]
    #[IsGranted('ROLE_USER', message: "You don't have enough rights")]
    public function updateCustomer(Customer $customer, UserRepository $userRepository, CustomerRepository $customerRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentCustomer = $customerRepository->findOneBy(['id' => $customer->getId(), "users" => $user]);

        if (empty($currentCustomer)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $updatedCustomer = $serializer->deserialize($request->getContent(),
            Customer::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomer]);

        $errors = $validator->validate($updatedCustomer
        );

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($updatedCustomer);
        $entityManager->flush();
        $cachePool->invalidateTags(["customersCache-". $user->getId()]);
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
