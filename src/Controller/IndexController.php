<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home")
     *
     * @return JsonResponse
     */
    public function home()
    {
        return new Response("Bienvenue dans la page d\'accueil", Response::HTTP_OK);
    }
}
