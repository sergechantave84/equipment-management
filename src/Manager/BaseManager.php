<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class BaseManager.
 */
abstract class BaseManager
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    protected \Doctrine\ORM\EntityRepository $repository;

    /**
     * @var string
     */
    protected string $class;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string $class
     */
    public function __construct(EntityManagerInterface $entityManager, string $class)
    {
        $this->entityManager = $entityManager;
        $this->class = $class;
        $this->repository = $this->entityManager->getRepository($this->class);
    }

    /**
     * @param mixed $entity
     *
     * @return mixed
     */
    public function save($entity)
    {
        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * @param mixed $entity
     *
     * @return mixed
     */
    public function saveAndFlush($entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * @param mixed $entity
     *
     * @return bool
     */
    public function delete($entity): bool
    {
        $this->entityManager->remove($entity);
        $this->flushAndClear();

        return true;
    }

    /**
     * @return void
     */
    public function flushAndClear()
    {
        $this->entityManager->flush();
    }

    /**
     * @return mixed
     */
    public function createNew()
    {
        $class = $this->class;

        return new $class();
    }

    /**
     * @return array|object[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param mixed $id
     *
     * @return ?object
     */
    public function find($id): ?object
    {
        return $this->repository->findOneBy(['id' => $id]);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     * @return array|object[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $criteria
     *
     * @return ?object
     */
    public function findOneBy(array $criteria): ?object
    {
        return  $this->repository->findOneBy($criteria);
    }

    /**
     * Begin Transaction
     */
    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * Commit
     */
    public function commit()
    {
        $this->entityManager->commit();
    }

    /**
     * Rollback
     */
    public function rollback()
    {
        $this->entityManager->rollback();
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        return $this->repository;
    }
}
