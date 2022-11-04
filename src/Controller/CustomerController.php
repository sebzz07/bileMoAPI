<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class CustomerController extends AbstractController
{
    #[Route('/users/{userId}/customers', name: 'customerList', methods: ['GET'])]
    #[Entity('user', options: ['id' => 'userId'])]
    public function getCustomerList(User $user, UserRepository $userRepository, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customerList = $customerRepository->findBy(["users" => $user]);

        if (empty($customerList)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $jsonCustomerList = $serializer->serialize($customerList, 'json', ['groups' => 'getCustomers']);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    #[Route('/users/{userId}/customers/{customerId}', name: 'customerDetails', methods: ['GET'])]
    public function getCustomerDetails(int $userId, int $customerId, UserRepository $userRepository, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->findBy(['id' => $userId]);
        $customerDetails = $customerRepository->findBy(['id' => $customerId, "users" => $user]);

        if (empty($customerDetails)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $jsonCustomerList = $serializer->serialize($customerDetails, 'json', ['groups' => 'getCustomerDetails']);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    #[Route('/users/{userId}/customers/{customerId}', name: 'deleteCustomer', methods: ['DELETE'])]
    public function deleteCustomer(int $userId, int $customerId, UserRepository $userRepository, CustomerRepository $customerRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->findOneBy(['id' => $userId]);
        $customer = $customerRepository->findOneBy(['id' => $customerId, "users" => $user]);
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/users/{id}/customers', name:"createCustomer", methods: ['POST'])]
    public function createCustomer(User $user, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setUser($user);

        $errors = $validator->validate($customer);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($customer);
        $entityManager->flush();

        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getCustomers']);

        $location = $urlGenerator->generate('api_customerDetails', ['customerId' => $customer->getId(), 'userId' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/users/{userId}/customers/{customerId}', name:"createCustomer", methods: ['PUT'])]
    public function updateCustomer(int $userId, int $customerId,UserRepository $userRepository, CustomerRepository $customerRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {

        $user = $userRepository->findBy(['id' => $userId]);
        $currentCustomer = $customerRepository->findOneBy(['id' => $customerId, "users" => $user]);

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

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
