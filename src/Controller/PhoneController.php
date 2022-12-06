<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


#[Route('/api', name: 'api_')]
#[IsGranted('ROLE_USER', message: "You don't have enough rights")]
class PhoneController extends AbstractController
{
    /**
     * This route with the verb GET give you the list of our phones.
     * @OA\Response(
     *     response=200,
     *     description="Give you the list of phones",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Phone::class, groups={"getPhones"}))
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
     * @OA\Tag(name="Phones")
     *
     * @throws InvalidArgumentException
     */
    #[Route('/phones', name: 'phoneList', methods: ['GET'])]
    public function getPhoneList(Request $request, PhoneRepository $phoneRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $offset = $request->query->getInt('offset', 1);
        $limit = $request->query->getInt('limit', 3);

        $idCache = 'getAllCustomers-'.$offset.'-'.$limit;

        $jsonPhoneList = $cachePool->get($idCache, function (ItemInterface $item) use ($phoneRepository, $serializer, $offset, $limit) {
            $item->tag(['phonesCache']);

            $queryBuilder = $phoneRepository->findAllQueryBuilder();
            $pagerfanta = new Pagerfanta(
                new QueryAdapter($queryBuilder)
            );
            $pagerfanta->setMaxPerPage($limit);
            $pagerfanta->setCurrentPage($offset);
            $context = SerializationContext::create()->setGroups(['getPhones']);

            return $serializer->serialize($pagerfanta, 'json', $context);
        });

        return new JsonResponse($jsonPhoneList, Response::HTTP_OK, [], true);
    }

    /**
     * This route with the verb GET returns to you the phone's details of the entered id.
     *
     * @OA\Tag(name="Phones")
     */
    #[Route('/phones/{id}', name: 'phoneDetails', methods: ['GET'])]
    public function getDetailBook(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        $jsonPhone = $serializer->serialize($phone, 'json');

        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }
}
