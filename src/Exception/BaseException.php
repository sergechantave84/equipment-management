<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BaseException extends \Exception
{
    private $data;
    private $statusCode;

    /**
     * BaseException constructor.
     * @param mixed          $data
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $data,
        $message = "",
        $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->setData($data);
        $this->setStatusCode($code);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode): void
    {
        $this->statusCode = $statusCode;
    }
}
