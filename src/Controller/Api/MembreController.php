<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')] // Préfixe commun pour toutes les routes
class MembreController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Afficher le profil de l'utilisateur connecté avec son token JWT
     */
    #[Route('/profile', name: 'api_profile', methods: ['GET'], requirements: ['_format' => 'json'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function getProfile(JWTTokenManagerInterface $jwtManager): JsonResponse
{
    $user = $this->getUser();

    if (!$user instanceof User) {
        return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
    }

    $token = $jwtManager->create($user);

    return $this->json([
        'message' => 'Welcome',
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'roles' => $user->getRoles(),
        'token' => $token
    ]);
}
    /**
     * Mettre à jour les informations du profil utilisateur
     */
    #[Route('/profile/update', name: 'api_profile_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Vérifie que l'utilisateur est authentifié
    public function updateProfile(Request $request, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $this->entityManager->flush();

        // Générer un nouveau token JWT après mise à jour du profil
        $token = $jwtManager->create($user);

        return new JsonResponse([
            'message' => 'Profile Modification réussie',
            'token' => $token // Renvoie le nouveau token mis à jour
        ]);
    }
}
