<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\EquipmentRepository")
 */
class Equipment
{
    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @Groups({"equipment_read", "equipment_update"})
     */
    private string $id;

    /**
     * @ORM\Column(type="string")
     * @Groups({"equipment_read", "equipment_create", "equipment_update"})
     * @Assert\NotBlank(message="Le nom de l'équipement doit être renseigné !")
     */
    private string $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"equipment_read", "equipment_create", "equipment_update"})
     */
    private ?string $category;

    /**
     * @ORM\Column(type="string")
     * @Groups({"equipment_read", "equipment_create", "equipment_update"})
     * @Assert\NotBlank(message="Le numéro de l'équipement doit être renseigné !")
     */
    private string $number;

    /**
     * @Groups({"equipment_read", "equipment_create", "equipment_update"})
     * @ORM\Column(type="text", length=16777215, options={"default"=""})
     */
    private string $description;

    /**
     * @ORM\Column(type="boolean", options={"default"=FALSE})
     */
    private bool $deleted;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"equipment_read"})
     */
    private DateTime $createdAt;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"=NULL})
     * @Groups({"equipment_read"})
     */
    private ?DateTime $updatedAt;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     *
     * @return $this
     */
    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     *
     * @return $this
     */
    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
