<?php

namespace App\Listener;

use App\Enum\CodeResponseType;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $code = $exception->getCode();
        $code = in_array($code, CodeResponseType::getAll(), true) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;
        $file = $exception->getFile();
        $error = [
            'code'    => $code,
            'message' => $exception->getMessage(),
            'file'    => $file,
            'line'    => $exception->getLine(),
        ];
        $response = new JsonResponse($error, $code);
        $event->setResponse($response);
    }
}
