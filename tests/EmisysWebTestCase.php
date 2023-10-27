<?php

namespace App\Tests;

use App\Tests\traits\SetEntityIdTrait;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class EmisysWebTestCase extends WebTestCase
{
    use EmisysTestTrait;
    use SetEntityIdTrait;

    protected ?KernelBrowser $client = null;
    /**
     * Last requested URL.
     * @var string|null
     */
    protected ?string $lastUrl = null;
    /**
     * If true, no project_id is added to the url of any subsequent requests.
     * @var bool
     */
    protected bool $ignoreProjectId = false;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        /*
         * Client must be created now because it boots the kernel. Booting the kernel
         * clears the container. Clearing the container detaches the project setup by
         * the trait. Detaching the project means we get errors about unmanaged
         * entities during a flush.
         */
        $this->client = static::createClient();
        /*
         * Kernel must not be rebooted on every other request. Without the following
         * line, the first request of a test is processed just fine but the second
         * one in the same test detaches every entity object. Weird things happen
         * in the test after objects under test are detached. The solution is to
         * disable kernel reboot by the client.
         *
         * But the trick doesn't work anymore since doctrine 1.11. Entities are
         * always detached but they are not removed from the cache unless the kernel
         * is rebooted. When a test gets a form and then submits it, entities loaded
         * by the controller when the form was retrieved are kept around in the
         * detached state. When the form is submitted, the controller gets the
         * detached entities back from the cache and fails to process the submitted form
         * because the entities is received are detached.
         */
        //$this->client->disableReboot();
        $this->initTrait();
    }

    /**
     * @throws \ReflectionException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        /*unset($this->user);
        unset($this->userProfile);*/
        if ($this->client) {
            $this->unsetClassProperty($this->client, "history");
            $this->unsetClassProperty($this->client, "cookieJar");
            $this->unsetClassProperty($this->client, "server");
            $this->unsetClassProperty($this->client, "internalRequest");
            $this->unsetClassProperty($this->client, "request");
            $this->unsetClassProperty($this->client, "internalResponse");
            $this->unsetClassProperty($this->client, "response");
            $this->unsetClassProperty($this->client, "crawler");
            $this->unsetClassProperty($this->client, "insulated");
            $this->unsetClassProperty($this->client, "redirect");
            $this->unsetClassProperty($this->client, "followRedirects");
            unset($this->client);
        }
        $this->releaseTrait();
    }

    /**
     * @throws \ReflectionException
     */
    protected function unsetClassProperty(object $object, string $property, ?string $class = null): void
    {
        if (is_null($class)) {
            $class = $object;
        }
        $reflectionClass = new ReflectionClass($class);

        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, null);
    }

    protected function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        bool $changeHistory = true
    ): Response {
        $this->client->request(
            $method,
            $uri,
            $parameters,
            $files,
            $server,
            $content,
            $changeHistory
        );
        $this->lastUrl = $this->client->getRequest()->getUri();
        return $this->client->getResponse();
    }

    protected function checkRequestSuccess(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        $content = null,
        $changeHistory = true
    ): Response {
        $response = $this->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->assertTrue(
            $response->isSuccessful(),
            "Failed to load url $method $uri: " . $response->getStatusCode()
        );
        return $response;
    }

    protected function checkRequestNotFound(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        bool $changeHistory = true
    ) {
        $response = $this->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->assertFalse($response->isSuccessful(), "Unexpected success when loading url $method $uri");
        $responseCode = $response->getStatusCode();
        $this->assertEquals(
            404,
            $responseCode,
            "Invalid return code $responseCode when loading url $method $uri"
        );
        return $response;
    }

    protected function checkRequestRedirect(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        bool $changeHistory = true
    ) {
        $response = $this->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->assertFalse($response->isSuccessful(), "Unexpected success when loading url $method $uri");
        $responseCode = $response->getStatusCode();
        $this->assertTrue(
            $response->isRedirection(),
            "Not a redirection when loading url $method $uri: status code=$responseCode"
        );
        $this->assertEquals(
            302,
            $responseCode,
            "Invalid return code $responseCode when loading url $method $uri"
        );
        return $response;
    }

    protected function checkRequestMoved(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        bool $changeHistory = true
    ) {
        $response = $this->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->assertFalse($response->isSuccessful(), "Unexpected success when loading url $method $uri");
        $this->assertTrue($response->isRedirection(), "Not a redirection when loading url $method $uri");
        $responseCode = $response->getStatusCode();
        if ($responseCode !== 301/*permanent*/ && $responseCode !== 302/*temporary*/) {
            $this->fail("Invalid redirect code $responseCode when loading url $method $uri");
        }
        return $response;
    }

    protected function checkRequestErrorCode(
        string $method,
        int $errorCode,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        bool $changeHistory = true
    ) {
        $response = $this->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->assertFalse($response->isSuccessful(), "Unexpected success when loading url $method $uri");
        $responseCode = $response->getStatusCode();
        $this->assertEquals(
            $errorCode,
            $responseCode,
            "Wrong return code $responseCode when loading url $method $uri"
        );
        return $response;
    }

    protected function getRedirectUrl(Response $response): ?string
    {
        return $response->headers->get('Location');
    }

    protected function navigateGet(string $uri, array $requestParameters = [])
    {
        return $this->request('GET', $uri, $requestParameters);
    }

    protected function navigateAndPost(
        string $uri,
        array $requestParameters = [],
        array $formData = [],
        ?string $submitButton = null,
        ?string $formSelector = null
    ) {
        $response = $this->navigateGet($uri, $requestParameters);
        $this->assertTrue($response->isSuccessful(), "Failed to load url GET {$this->lastUrl}");

        return $this->submitPost($formData, $submitButton, $formSelector);
    }

    protected function submitPost(
        array $formData = [],
        ?string $submitButton = null,
        ?string $formSelector = null
    ) {
        $crawler = $this->client->getCrawler();

        if ($formSelector !== null) {
            $form = $crawler->filter($formSelector)->form();
        } else {
            if (is_null($submitButton)) {
                $submitButton = '//form//button[@type="submit"]';
            }
            $buttonCrawlerNode = $crawler->filterXPath($submitButton);
            $form = $buttonCrawlerNode->form();
        }

        $submitData = [];
        foreach ($form->all() as $item) {
            if ($item->hasValue()) {
                $submitData[$item->getName()] = $item->getValue();
            }
        }
        $finalData = array_replace($submitData, $formData);

        $this->client->submit($form, $finalData);

        return $this->client->getResponse();
    }

    protected function submitPostSuccess(array $formData = [], ?string $submitButton = null)
    {
        $response = $this->submitPost($formData, $submitButton);
        $this->assertTrue($response->isSuccessful(), "Failed to submit url POST {$this->lastUrl}");
        return $response;
    }

    protected function getService(string $className)
    {
        /** @var T $service */
        return self::$container->get($className);
    }
}
