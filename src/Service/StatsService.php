<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class StatsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Récupère les statistiques pour n'importe quelle entité avec un champ "isActive"
     *
     * @param string $entityClass Nom de l'entité (ex: App\Entity\Salles)
     * @param string $statusField Nom du champ booléen (ex: 'isActive')
     * @return array ['total' => int, 'actives' => int, 'inactives' => int]
     */
    public function getEntityStats(string $entityClass, string $statusField = 'isActive'): array
    {
        $repository = $this->entityManager->getRepository($entityClass);

        $total = $repository->count([]);
        $active = $repository->count([$statusField => true]);
        $inactive = $repository->count([$statusField => false]);

        return [
            'total' => $total,
            'actives' => $active,
            'inactives' => $inactive
        ];
    }
}
