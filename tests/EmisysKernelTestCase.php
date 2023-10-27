<?php

namespace App\Tests;

use App\Tests\traits\SetEntityIdTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class EmisysKernelTestCase extends KernelTestCase
{
    use EmisysTestTrait {
        getEntityManager as protected traitGetEntityManager;
    }
    use SetEntityIdTrait;

    /**
     * The kernel was booted.
     * @var bool
     */
    private bool $kernelBooted = false;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->bootKernelIfNecessary();
        $this->initTrait();
    }

    private function bootKernelIfNecessary(): void
    {
        if (!$this->kernelBooted) {
            self::bootKernel();
            $this->kernelBooted = true;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if (property_exists($this, "repository")) {
            unset($this->repository);
        }
        $this->releaseTrait();
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        $this->bootKernelIfNecessary();
        return $this->traitGetEntityManager();
    }

    protected function setTestProject(string $projectName = "live"): void
    {
        $this->bootKernelIfNecessary();
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return T
     */
    protected function getService(string $className)
    {
        /** @var T $service */
        $service = self::$container->get($className);
        return $service;
    }
}
