<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EntityService
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;
    private TokenStorageInterface $tokenStorage;
    private AuthorizationCheckerInterface $authChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker
    ) {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
    }

    /**
     * ✅ Récupérer l'utilisateur connecté en toute sécurité
     */
    private function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();
        return ($token && $token->getUser() instanceof UserInterface) ? $token->getUser() : null;
    }

    /**
     * ✅ Créer une nouvelle entité
     */
    public function new(object $entity, ?UploadedFile $file = null, string $uploadDir = ''): void
    {
        // 🔒 Vérification du rôle administrateur
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            throw new \Exception('Accès refusé : Vous devez être administrateur.');
        }

        // 🔒 Vérification et assignation de l'utilisateur connecté
        if (method_exists($entity, 'setUser')) {
            $user = $this->getUser();
            if (!$user) {
                throw new \Exception('Vous devez être connecté pour effectuer cette action.');
            }
            $entity->setUser($user);
        }

        // 📂 Gestion des fichiers uploadés
        if ($file && $uploadDir) {
            $this->validateFile($file);
            $fileName = uniqid('', true) . '.' . $file->guessExtension();
            $destination = $this->params->get('kernel.project_dir') . '/public/uploads/' . $uploadDir;

            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }

            $file->move($destination, $fileName);

            if (method_exists($entity, 'setPhoto')) {
                $entity->setPhoto($fileName);
            }
        }

        // 💾 Enregistrement
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * ✅ Modifier une entité
     */
    public function edit(object $entity, ?UploadedFile $file = null, string $uploadDir = ''): void
    {
        // 🔒 Vérification du rôle administrateur
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            throw new \Exception('Accès refusé : Vous devez être administrateur.');
        }

        // 🔒 Vérification et assignation de l'utilisateur connecté
        if (method_exists($entity, 'setUser')) {
            $user = $this->getUser();
            if (!$user) {
                throw new \Exception('Vous devez être connecté pour modifier cette entité.');
            }
            $entity->setUser($user);
        }

        // 📂 Gestion des fichiers uploadés
        if ($file && $uploadDir) {
            $this->validateFile($file);
            $fileName = uniqid('', true) . '.' . $file->guessExtension();
            $destination = $this->params->get('kernel.project_dir') . '/public/uploads/' . $uploadDir;

            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }

            $file->move($destination, $fileName);

            if (method_exists($entity, 'setPhoto')) {
                $entity->setPhoto($fileName);
            }
        }

        // 💾 Enregistrement
        $this->entityManager->flush();
    }

    /**
     * ✅ Récupérer une entité par ID
     */
    public function find(string $entityClass, int $id): ?object
    {
        return $this->entityManager->getRepository($entityClass)->find($id);
    }

    /**
     * ✅ Récupérer toutes les entités inactives d'un type donné
     */
    public function findBy(string $entityClass,bool $boolean, $order): array
    {
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            throw new \Exception('Accès refusé : seul un administrateur peut voir toutes les entités.');
        }

        return $this->entityManager->getRepository($entityClass)->findBy(['isActive' => $boolean], ['createdAt' => $order]);
    }

    /**
     * ✅ Lister toutes les entités d'un type donné
     */
    public function findAll(string $entityClass): array
    {
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            throw new \Exception('Accès refusé : seul un administrateur peut voir toutes les entités.');
        }

        return $this->entityManager->getRepository($entityClass)->findAll();
    }

    /**
     * ✅ Validation des fichiers uploadés
     */
    private function validateFile(UploadedFile $file, array $allowedMimeTypes = ['image/jpeg', 'image/png']): void
    {
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('Format de fichier non autorisé.');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \Exception('Fichier trop volumineux (max 2MB).');
        }

        if (preg_match('/\.(php|exe|sh|bat|cmd)$/i', $file->getClientOriginalName())) {
            throw new \Exception('Nom de fichier interdit.');
        }
    }
}
