<?php

namespace App\Tests\Controller\Equipment;

use App\Entity\Equipment;
use App\Helper\Utils;
use App\Manager\EquipmentManager;
use App\Tests\EmisysApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class EquipmentControllerTest extends EmisysApiTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->getService(EquipmentManager::class);
        //$this->repository = $this->getService(ItemStockRepository::class);
    }

    public function testListEquipment(): void
    {
        $url = self::getUri("equipments");
        $response = $this->callApi(self::GET, $url);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonResponseFormat($response->getContent());
    }

    public function testPostEquipment(): void
    {
        $data = [
            'name'        => 'iPhone Pro max',
            'number'      => 'iPhonePro-max',
            'description' => 'iPhone de la plus haute qualité',
            'category'    => 'Téléphone',
        ];

        $url = self::getUri("equipments");
        $response = $this->callApi(self::POST, $url, $data);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonResponseFormat($response->getContent());
    }

    private function createEquipment(): Equipment
    {
        $equipment = new Equipment();
        $equipment->setId(Utils::generateuuidv4(16));
        $equipment->setName('iPhone Pro max');
        $equipment->setNumber('iPhonePro-max');
        $equipment->setCategory('Téléphone');
        $equipment->setDescription('iPhone de la plus haute qualité');

        $this->em->persist($equipment);
        $this->em->flush();
        return $equipment;
    }

    public function testPutEquipment(): void
    {
        $equipment = $this->createEquipment();

        $data = [
            'id'          => $equipment->getId(),
            'name'        => $equipment->getName(),
            'number'      => $equipment->getNumber(),
            'description' => $equipment->getDescription(),
            'category'    => $equipment->getCategory(),
        ];

        $url = self::getUri("equipments/" . $equipment->getId());
        $response = $this->callApi(self::PUT, $url, $data);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonResponseFormat($response->getContent());
    }

    public function testDeleteEquipment(): void
    {
        $equipment = $this->createEquipment();

        $url = self::getUri("equipments/" . $equipment->getId());
        $response = $this->callApi(self::DELETE, $url);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
