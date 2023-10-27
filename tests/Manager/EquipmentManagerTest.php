<?php

namespace App\Tests\Manager;

use App\Entity\Equipment;
use App\Manager\EquipmentManager;
use App\Tests\EmisysKernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class EquipmentManagerTest extends EmisysKernelTestCase
{
    /** @var EquipmentManager */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $service = self::$container->get(EquipmentManager::class);
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->service);
    }

    public function testCheckParamsIfExist(): void
    {
        $request = $this->createRequest();
        $return = $this->service->checkParamsIfExist($request);
        $this->assertArrayHasKey(
            'code',
            $return,
            "Wrong response format (missing code): " . json_encode($return)
        );
        $this->assertArrayHasKey(
            'message',
            $return,
            "Wrong response format (missing message): " . json_encode($return)
        );
        $this->assertArrayHasKey(
            'data',
            $return,
            "Wrong response format (missing data): " . json_encode($return)
        );
    }

    public function testSaveEquipment(): void
    {
        $request = $this->createRequest();
        $return = $this->service->checkParamsIfExist($request);
        $equipment = $this->service->saveEquipment($return['data'], null);
        $this->assertTrue(
            $equipment instanceof Equipment,
            "saveEquipment() doit retourner un objet Equipment"
        );
    }

    private function createRequest(): Request
    {
        $request = new Request();
        $server['CONTENT_TYPE'] = "application/json";
        $request->initialize(
            [],
            [],
            [],
            [],
            [],
            $server,
            json_encode(
                [
                    'name'        => 'iPhone Pro max',
                    'number'      => 'iPhonePro-max',
                    'description' => 'iPhone de la plus haute qualité',
                    'category'    => 'Téléphone',
                ]
            )
        );

        return $request;
    }
}
