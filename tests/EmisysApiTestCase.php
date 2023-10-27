<?php

namespace App\Tests;

use Symfony\Component\HttpFoundation\Response;

abstract class EmisysApiTestCase extends EmisysWebTestCase
{
    protected const GET = "GET";
    protected const PATCH = "PATCH";
    protected const POST = "POST";
    protected const PUT = "PUT";
    protected const DELETE = "DELETE";

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function callApi(string $method, string $uri, $content = "", array $parameters = [], array $files = [])
    {
        $server = [];

        if (!is_string($content)) {
            $server['CONTENT_TYPE'] = "application/json";
            $content = json_encode($content);
        }

        $apiUri = "/api" . $uri;
        $this->client->request(
            $method,
            $apiUri,
            $parameters,
            $files,
            $server,
            $content,
            false
        );
        $this->lastUrl = $this->client->getRequest()->getUri();
        return $this->client->getResponse();
    }

    protected function callApiV1(string $method, string $uri, $content = "")
    {
        return $this->callApi("1", $method, $uri, $content);
    }

    protected function callApiV1Success(string $method, string $uri, $content = "")
    {
        $response = $this->callApi("1", $method, $uri, $content);
        $this->assertTrue($response->isSuccessful());
        return $response;
    }

    protected function checkJson(Response $response)
    {
        $json = json_decode($response->getContent(), true);
        $this->assertNotFalse($json);
        $this->assertIsArray($json);
        return $json;
    }

    public const URI = null;

    protected function assertJsonResponseFormat(string $json)
    {
        $data = json_decode($json, true);

        $this->assertIsArray($data, "Invalid json response: $json");
        $this->assertArrayHasKey("code", $data, "Wrong json response format (missing code): $json");
        $this->assertArrayHasKey("message", $data, "Wrong json response format (missing message): $json");
        $this->assertArrayHasKey("data", $data, "Wrong json response format (missing data): $json");
        return $data;
    }

    protected function checkApiJsonErrorCode(string $jsonString, string $errorCode)
    {
        $json = $this->assertJsonResponseFormat($jsonString);
        $this->assertArrayHasKey('meta', $json);
        $meta = $json['meta'];
        $this->assertArrayHasKey('errors', $meta);
        $errors = $meta['errors'];
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertArrayHasKey("code", $error);
        $this->assertEquals($errorCode, $error['code']);
        return $json;
    }

    protected function checkApiJsonSuccess(string $jsonString)
    {
        $json = $this->assertJsonResponseFormat($jsonString);
        $meta = $json['meta'];
        $errors = $meta['errors'];
        $this->assertEmpty($errors);
        return $json;
    }

    protected function assertEqualsResponseKey(Response $response, string $key, $value): void
    {
        $data = $this->getResponseArray($response, $key);
        $this->assertEquals($data, $value);
    }

    protected function assertEqualsResponseKeys(Response $response, array $keys): void
    {
        foreach ($keys as $key => $value) {
            $data = $this->getResponseArray($response, $key);
            $this->assertEquals($data, $value);
        }
    }

    protected function assertResponseKeysExist(array $array, array $keys): void
    {
        foreach ($keys as $key) {
            $arrayKeys = explode(".", $key);
            foreach ($arrayKeys as $index => $subkey) {
                $subkey = ($subkey) ? $subkey : "0";
                $this->assertArrayHasKey($subkey, $array);
            }
        }
    }

    protected static function getUri(?string $path = null, array $params = []): string
    {
        $url = rtrim(static::URI, '/');
        if ($path !== null && strlen($path) > 0) {
            $url .= '/' . ltrim($path, '/');
        }

        $paramList = [];
        foreach ($params as $key => $value) {
            $paramList[] = $key . "=" . rawurlencode($value);
        }
        if (!empty($paramList)) {
            $url .= "?" . join("&", $paramList);
        }

        return $url;
    }

    protected function getResponseArray(Response $response, string $keys = null)
    {
        $array = json_decode($response->getContent(), true);
        $this->assertIsArray($array);
        $arrayKeys = explode(".", $keys);

        if (!empty($arrayKeys)) {
            foreach ($arrayKeys as $key) {
                $key = ($key) ? $key : "0";
                $this->assertIsArray($array, "Not an array found at key level $key");
                $this->assertArrayHasKey($key, $array);
                $array = $array[$key];
            }
        }

        return $array;
    }

    protected function findEntity(string $entity)
    {
        return $this
            ->getEntityManager()
            ->getRepository($entity)
            ->findAll()[0];
    }
}
