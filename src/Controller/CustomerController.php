<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class CustomerController extends AbstractController
{
    #[Route('/users/{userId}/customers', name: 'customerList')]
    public function getCustomerList(int $userId, UserRepository $userRepository, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->findBy(['id' => $userId]);
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
    public function deleteCustomer(int $userId, int $customerId, UserRepository $userRepository, CustomerRepository $customerRepository, EntityManagerInterface $EntityManager): JsonResponse
    {
        $user = $userRepository->findOneBy(['id' => $userId]);
        $customer = $customerRepository->findOneBy(['id' => $customerId, "users" => $user]);
        $EntityManager->remove($customer);
        $EntityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
