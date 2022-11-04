<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class PhoneController extends AbstractController
{
    #[Route('/phones', name: 'phoneList', methods: ['GET'])]
    public function getPhoneList(PhoneRepository $phoneRepository, SerializerInterface $serializer): JsonResponse
    {
        $phoneList = $phoneRepository->findAll();
        $jsonPhoneList = $serializer->serialize($phoneList, 'json',['groups' => 'getPhones']);
        return new JsonResponse($jsonPhoneList, Response::HTTP_OK, [], true);
    }

    #[Route('/phones/{id}', name: 'detailPhone', methods: ['GET'])]
    public function getDetailBook(Phone $phone, SerializerInterface $serializer): JsonResponse {

        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }
}
