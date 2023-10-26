<?php

namespace App\Manager;

use App\Entity\Equipment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Helper\Utils;

class EquipmentManager extends BaseManager
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Equipment::class);
    }

    public function checkParamsIfExist(Request $request): array
    {
        $body = json_decode($request->getContent());
        $code = Response::HTTP_OK;
        $messageOK = 'Paramètres name, number et description récupérés';
        $messageError = 'Les paramètres suivants sont obligatoires:';
        if (!property_exists($body, 'name')) {
            $messageError .= PHP_EOL . '- name: nom de l\'équipement';
            $code = Response::HTTP_PRECONDITION_REQUIRED;
        }
        if (!property_exists($body, 'number')) {
            $messageError .= PHP_EOL . '- number: numéro de l\'équipement';
            $code = Response::HTTP_PRECONDITION_REQUIRED;
        }
        if (!property_exists($body, 'description')) {
            $messageError .= PHP_EOL . '- description: description de l\'équipement';
            $code = Response::HTTP_PRECONDITION_REQUIRED;
        }
        $return = [
            'code'    => $code,
            'message' => $messageOK,
            'data'    => $body,
        ];
        if ($code === Response::HTTP_PRECONDITION_REQUIRED) {
            $return['message'] = $messageError;
        }

        return $return;
    }

    public function saveEquipment($body, ?string $id): Equipment
    {
        if ($id) {
            $equipment = $this->find($id);
        } else {
            $equipment = new Equipment();
            $equipment->setId(Utils::generateuuidv4(16));
        }
        $equipment->setName($body->name)
                  ->setCategory(property_exists($body, 'category') ? $body->category : null)
                  ->setNumber($body->number)
                  ->setDescription($body->description)
        ;

        if (!$id) {
            $this->save($equipment);
        }
        $this->flush();

        return $equipment;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function deleteEquipment(Request $request): array
    {
        $id = $request->attributes->get('id');
        $equipment = $this->find($id);
        if ($equipment instanceof Equipment) {
            $equipment->setDeleted(true);
            $this->flush();
            $return = [
                'code'    => Response::HTTP_OK,
                'message' => 'Equipement supprimé avec succès',
            ];
        } else {
            $return = [
                'code'    => Response::HTTP_NOT_FOUND,
                'message' => 'Aucun équipement trouvé avec l\'id ' . $id,
            ];
        }

        return $return;
    }

    public function listEquipment(Request $request): array
    {
        $nbLinePerPage = $request->get('nbLinePerPage');
        if (!$nbLinePerPage || $nbLinePerPage < 1) {
            $nbLinePerPage = $_ENV['NUMBER_LINE_PER_PAGE'];
        }
        $page = $request->get('page');
        if (!$page || $page < 1) {
            $page = $_ENV['PAGE'];
        }
        $name = $request->get('name');
        $category = $request->get('category');

        return $this->repository->listEquipment($nbLinePerPage, $page, $name, $category);
    }
}
