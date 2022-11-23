<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api', name: 'api_')]
#[IsGranted('ROLE_USER', message: "You don't have enough rights")]
class PhoneController extends AbstractController
{
    /**
     * This route with the verb GET give you the list of our phones.
     *
     * @throws InvalidArgumentException
     */
    #[Route('/phones', name: 'phoneList', methods: ['GET'])]
    public function getPhoneList(Request $request, PhoneRepository $phoneRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $offset = intval($request->get('offset', 1));
        $limit = intval($request->get('limit', 3));

        $idCache = 'getAllCustomers-'.$offset.'-'.$limit;

        $phoneList = $cachePool->get($idCache, function (ItemInterface $item) use ($phoneRepository, $serializer, $offset, $limit) {
            $item->tag(['phonesCache']);

            $queryBuilder = $phoneRepository->findAllQueryBuilder();
            $pagerfanta = new Pagerfanta(
                new QueryAdapter($queryBuilder)
            );
            $pagerfanta->setMaxPerPage($limit);
            $pagerfanta->setCurrentPage($offset);
            $context = SerializationContext::create()->setGroups(['getCustomers']);

            return $serializer->serialize($pagerfanta, 'json', $context);
        });

        return new JsonResponse($phoneList, Response::HTTP_OK, [], true);
    }

    /**
     * This route with the verb GET returns to you the phone's details of the entered id.
     */
    #[Route('/phones/{id}', name: 'phoneDetails', methods: ['GET'])]
    public function getDetailBook(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        $jsonPhone = $serializer->serialize($phone, 'json');

        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }
}
