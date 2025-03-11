<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DeleteService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrfTokenManager
    ) {
    }

    /**
     * Vérifie le token CSRF et supprime une entité de la base de données.
     *
     * @param object  $entity   L'entité à supprimer
     * @param string  $tokenId  L'identifiant unique pour le CSRF
     * @param Request $request  La requête HTTP contenant le token CSRF
     */
    public function deleteWithCsrf(object $entity, string $tokenId, Request $request): void
    {
        $token = new CsrfToken($tokenId, $request->request->get('_token'));

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new AccessDeniedException('Token CSRF invalide.');
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
