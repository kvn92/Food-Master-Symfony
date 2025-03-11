<?php 
// src/Service/StatusToggleService.php
namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;

class StatusToggleService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function toggleStatus(object $entity, string $statusProperty): void
    {
        $getter = '' . ucfirst($statusProperty);
        $setter = 'set' . ucfirst($statusProperty);

        if (!method_exists($entity, $getter) || !method_exists($entity, $setter)) {
            throw new \InvalidArgumentException(sprintf(
                "L'entité %s ne contient pas de méthode %s ou %s.",
                get_class($entity),
                $getter,
                $setter
            ));
        }

        // Bascule l'état du statut
        $currentStatus = $entity->$getter();
        $entity->$setter(!$currentStatus);

        // Sauvegarde dans la base de données
        $this->entityManager->flush();
        
    }
}
