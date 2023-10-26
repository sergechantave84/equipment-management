<?php

namespace App\Controller;

use App\Helper\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class BaseController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    public static function serializeData($data, array $context)
    {
        $serializer = Utils::getJsonSerializer();

        return $serializer->normalize(
            $data,
            'json',
            Utils::setContext($context)
        );
    }
}
