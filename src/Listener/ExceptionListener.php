<?php

namespace App\Listener;

use App\Enum\CodeResponseType;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListener
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        $uri = $request->getRequestUri();
        $exception = $event->getThrowable();
        $code = $exception->getCode();
        $code = in_array($code, CodeResponseType::getAll(), true) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;
        $data = $exception->getData();
        $file = $exception->getFile();
        $error = [
            'code'    => $code,
            'message' => $exception->getMessage(),
            'file'    => $file,
            'line'    => $exception->getLine(),
            'data'    => $data,
        ];
        $response = new JsonResponse($error, $code);
        $event->setResponse($response);
    }
}
