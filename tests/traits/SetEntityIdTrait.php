<?php

namespace App\Tests\traits;

trait SetEntityIdTrait
{
    /**
     * Set the ID of an object created during a test without having to store
     * it into the db.
     *
     * @param object $entity
     * @param int|null $id
     */
    protected function setEntityId(object $entity, ?int $id)
    {
        $reflClass = new \ReflectionClass($entity);
        do {
            if ($reflClass->hasProperty("id")) {
                $reflId = $reflClass->getProperty("id");
                $reflId->setAccessible(true);
                $reflId->setValue($entity, $id);
                break;
            }
            $reflClass = $reflClass->getParentClass();
        } while ($reflClass);
    }

    /**
     * @deprecated Use setEntityId(). Its name better matches the function's purpose.
     * @param object $entity
     * @param int|null $id
     */
    protected function setId(object $entity, ?int $id)
    {
        $this->setEntityId($entity, $id);
    }
}
