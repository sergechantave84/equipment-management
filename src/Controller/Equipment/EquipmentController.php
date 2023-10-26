<?php

namespace App\Controller\Equipment;

use App\Controller\BaseController;
use App\Manager\EquipmentManager;
use App\Entity\Equipment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * @Route("/api")
 */
class EquipmentController extends BaseController
{
    public EntityManagerInterface $entityManager;
    public EquipmentManager $equipmentManager;

    public function __construct(EntityManagerInterface $entityManager, EquipmentManager $equipmentManager)
    {
        $this->entityManager = $entityManager;
        $this->equipmentManager = $equipmentManager;
    }

    /**
     * @Route("/equipments", name="post_equipment", methods={"POST"})
     *
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns modified equipment",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="code"),
     *        @OA\Property(property="message"),
     *        @OA\Property(property="data", ref=@Model(type=Equipment::class, groups={"equipment_read"}))
     *     )
     * )
     * @OA\Response(
     *     response=Response::HTTP_PRECONDITION_REQUIRED,
     *     description="Returns a detailed error message of precondition not met",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="code"),
     *        @OA\Property(property="message"),
     *        @OA\Property(property="data", ref=@Model(type=Equipment::class, groups={"equipment_read"}))
     *     )
     * )
     * @OA\RequestBody(@Model(type=Equipment::class, groups={"equipment_create"}))
     * @OA\Tag(name="equipment")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function postEquipment(Request $request): JsonResponse
    {
        $return = $this->equipmentManager->checkParamsIfExist($request);
        if (Response::HTTP_OK === $return['code']) {
            $return['data'] = self::serializeData(
                $this->equipmentManager->saveEquipment($return['data'], null),
                ['equipment_read']
            );
            $return['message'] = 'Equipement créé avec succès';
        }

        return new JsonResponse($return, $return['code']);
    }

    /**
     * @Route("/equipments/{id}", name="put_equipment", methods={"PUT"})
     *
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns modified equipment",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="code"),
     *        @OA\Property(property="message"),
     *        @OA\Property(property="data", ref=@Model(type=Equipment::class, groups={"equipment_read"}))
     *     )
     * )
     * @OA\Response(
     *     response=Response::HTTP_PRECONDITION_REQUIRED,
     *     description="Returns a detailed error message of precondition not met",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="code"),
     *        @OA\Property(property="message"),
     *        @OA\Property(property="data", ref=@Model(type=Equipment::class, groups={"equipment_read"}))
     *     )
     * )
     * @OA\RequestBody(@Model(type=Equipment::class, groups={"equipment_update"}))
     * @OA\Tag(name="equipment")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function putEquipment(Request $request): JsonResponse
    {
        $return = $this->equipmentManager->checkParamsIfExist($request);
        if (Response::HTTP_OK === $return['code']) {
            $return['data'] = self::serializeData(
                $this->equipmentManager->saveEquipment($return['data'], $request->attributes->get('id')),
                ['equipment_read']
            );
            $return['message'] = 'Equipement modifié avec succès';
        }

        return new JsonResponse($return, $return['code']);
    }

    /**
     * @Route("/equipments/{id}", name="delete_equipment", methods={"DELETE"})
     *
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns a deletion confirmation message",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="code"),
     *        @OA\Property(property="message"),
     *     )
     * )
     * @OA\Response(
     *     response=Response::HTTP_NOT_FOUND,
     *     description="Return a resource error message not found",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="code"),
     *        @OA\Property(property="message"),
     *     )
     * )
     * @OA\Tag(name="equipment")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteEquipment(Request $request): JsonResponse
    {
        $return = $this->equipmentManager->deleteEquipment($request);

        return new JsonResponse($return, $return['code']);
    }

    /**
     * @Route("/equipments", name="list_equipment", methods={"GET"})
     *
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns the list of equipment",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="code"),
     *        @OA\Property(property="message"),
     *        @OA\Property(
     *            property="data",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Equipment::class, groups={"equipment_read"}))
     *        )
     *     )
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Equipment name field",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="category",
     *     in="query",
     *     description="Equipment category field",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="nbLinePerPage",
     *     in="query",
     *     description="Number lines per page",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="equipment")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function listEquipment(Request $request): JsonResponse
    {
        $return = [
            'code'    => Response::HTTP_OK,
            'message' => 'Liste des équipements',
            'data'    => self::serializeData($this->equipmentManager->listEquipment($request), ['equipment_read']),
        ];

        return new JsonResponse($return, Response::HTTP_OK);
    }
}
