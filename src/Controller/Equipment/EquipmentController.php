<?php

namespace App\Controller\Equipment;

use App\Entity\Equipment;
use App\Helper\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class EquipmentController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    public EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/post-tracemap", name="posttracemap")
     *
     * @param Request $request
     *
     * @return array|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function posttracemap(Request $request)
    {
        $body = json_decode($request->getContent());
        if (!property_exists($body, 'uid') || !property_exists($body, 'url')) {
            return [
                'code'    => Response::HTTP_BAD_REQUEST,
                'message' => 'Les paramètres url et uid sont obligatoires',
            ];

        }
        $traceMap = new Equipment();
        /*$traceMap->setUid($body->uid);
        $traceMap->setUrl($body->url);
        $this->entityManager->persist($traceMap);
        $this->entityManager->flush();*/
        $serializer = Utils::getJsonSerializer();

        return new JsonResponse($serializer->normalize(
            $traceMap,
            'json',
            Utils::setContext(['tracemap_create'])
        ));
    }
}
