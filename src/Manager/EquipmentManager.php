<?php

namespace App\Manager;

use App\Entity\Customer;
use App\Entity\Equipment;
use App\Enum\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Helper\Utils;

/**
 * CustomerManager class
 */
class EquipmentManager extends BaseManager
{
    //TODO: UserPasswordEncoderInterface is deprecated since symfony 5.3 : use UserPasswordHasherInterface instead
    private UserPasswordEncoderInterface $encoder;

    /**
     * Undocumented function
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $encoder
    ) {
        parent::__construct($entityManager, Customer::class);
        $this->encoder = $encoder;
    }

    /**
     * @param bool|null $isCreate
     * @return Customer
     */
    public function saveCustomer(?bool $isCreate = false): Customer
    {
        $customer = null;
        if ($isCreate) {
            $customer = new Customer();
            $customer->setCodeClient(Utils::generateuuidv4(16));
            $customer->setCreatedAt(new \DateTime());
            $customer->setUpdatedAt(new \DateTime());
        }
        $customer->setUpdatedAt(new \DateTime());
        if ($isCreate) {
            $this->save($customer);
        }
        $this->flush();

        return $customer;
    }

    /**
     * @param Customer|null $customer
     * @param $data
     * @param string $userGroup
     * @param bool|null $isCreate
     *
     * @return Equipment
     */
    public function saveUser(?Customer $customer, $data, string $userGroup, ?bool $isCreate = false): Equipment
    {
        $role = $customer ? UserType::ROLE_CUSTOMER : UserType::ROLE_FREE;
        if ($isCreate) {
            $user = new Equipment();
        } else {
            $user = $this->entityManager->getRepository(Equipment::class)->findOneBy(['email' => $data->email]);
        }
        $hash = $this->encoder->encodePassword($user, $data->password);
        $user->setId(Utils::generateuuidv4(16))
             ->setFirstName($data->firstName)
             ->setLastName($data->lastName)
             ->setEmail($data->email)
             ->setPassword($hash)
             ->setCustomer($customer)
             ->setGroupUser($customer ? $userGroup : null)
             ->setRoles([$role]);

        if ($isCreate) {
            $this->save($user);
        }
        $this->flush();

        return $user;
    }
}
