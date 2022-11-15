<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api', name: 'api_')]
#[IsGranted('ROLE_USER', message: "You don't have enough rights")]
class PhoneController extends AbstractController
{
    #[Route('/phones', name: 'phoneList', methods: ['GET'])]
    public function getPhoneList(Request $request, PhoneRepository $phoneRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $offset = $request->get('offset', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllCustomers-" . $offset . "-" . $limit;

        $phoneList = $cachePool->get($idCache, function (ItemInterface $item) use ($phoneRepository, $offset, $limit) {
            $item->tag(["phonesCache"]);
            return $phoneRepository->findAllWithPagination($offset, $limit);
        });

        if (empty($phoneList)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $context = SerializationContext::create()->setGroups(['getPhones']);
        $jsonCustomerList = $serializer->serialize($phoneList, 'json', $context);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    #[Route('/phones/{id}', name: 'phoneDetails', methods: ['GET'])]
    public function getDetailBook(Phone $phone, SerializerInterface $serializer): JsonResponse {

        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }
}
