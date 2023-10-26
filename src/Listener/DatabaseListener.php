<?php

namespace App\Listener;

use App\Entity\Equipment;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;

class DatabaseListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Equipment) {
            $entity->setCreatedAt(new \DateTime());
            $entity->setDeleted(false);
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Equipment) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }
}
