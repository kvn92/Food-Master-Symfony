<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Recette;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RecetteController extends AbstractController
{
    #[Route('/api/recette', name: 'api_recette', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $recettes = $entityManager->getRepository(Recette::class)->findAll();

        if (!$recettes) {
            return $this->json([], 200);
        }

        // Transformer les objets en JSON
        $data = [];
        foreach ($recettes as $recette) {
            $data[] = [
                'id' => $recette->getId(),
                'titre' => $recette->getTitre() ,
                'preparation' => $recette->getPreparation(),
                'photo' => $recette->getPhoto(),
                'niveau' =>$recette->getNiveau(),
                'viande'=>$recette->getViande(),
                'repas'=>$recette->getRepas(),
                'duree' => $recette->getDuree(),
                'createdAt' => $recette->getCreatedAt()?->format('Y-m-d H:i:s'),              
            ];
        }

        return $this->json($data, 200,[],['groups' => 'recette:read']);
    }




    

    #[Route('/api/recette', name: 'api_recette_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ): JsonResponse {
        try {
            $data = $request->request->all(); // Récupère les champs JSON et FormData
            $photo = $request->files->get('photo'); // Récupère l'image envoyée

            // 🔍 Log des données reçues
            $logger->info('Données reçues pour une nouvelle recette:', $data);

            // ✅ Vérification des champs obligatoires
            if (!isset($data['user'], $data['titre'], $data['preparation'], $data['duree'])) {
                return $this->json(['message' => 'Données invalides : certains champs sont manquants.'], Response::HTTP_BAD_REQUEST);
            }

            // ✅ Vérification de l'utilisateur
            $user = $entityManager->getRepository(User::class)->find($data['user']);
            if (!$user) {
                return $this->json(['message' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
            }

            // ✅ Création de la recette
            $recette = new Recette();
            $recette->setUser($user);
            $recette->setTitre(trim(strtolower($data['titre'])));
            $recette->setPhoto((string)  $data['photo']);
            $recette->setPreparation($data['preparation']);
            $recette->setNiveau((int) $data['niveau']);
            $recette->setRepas((int) $data['repas']);
            $recette->setViande((int) $data['viande']);
            $recette->setDuree((int) $data['duree']);
            $recette->setIsActive(false);
            $recette->setCreatedAt(new \DateTimeImmutable());

            // ✅ Gestion de l'upload d'image
            if ($photo) {
                $uploadsDir = $this->getParameter('photo_directory'); // Défini dans `services.yaml`
                $newFileName = uniqid() . '.' . $photo->guessExtension();

                try {
                    $photo->move($uploadsDir, $newFileName);
                    $recette->setPhoto($newFileName);
                } catch (FileException $e) {
                    return $this->json(['message' => 'Erreur lors de l\'upload de l\'image.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return $this->json(['message' => 'L\'image est obligatoire.'], Response::HTTP_BAD_REQUEST);
            }

            // ✅ Validation de l'entité
            $errors = $validator->validate($recette);
            if (count($errors) > 0) {
                return $this->json(['message' => 'Données invalides.', 'errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            // ✅ Sauvegarde en base de données
            $entityManager->persist($recette);
            $entityManager->flush();

            return $this->json([
                'message' => 'Recette ajoutée avec succès.',
                'id' => $recette->getId()
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de l\'ajout de la recette.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/recette/{id}', name: 'api_recette_detail', methods: ['GET'])]
    public function detail(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // 🔹 Récupérer la recette par ID
        $recette = $entityManager->getRepository(Recette::class)->find($id);
        
        if (!$recette) {
            return $this->json([
                'message' => 'Recette non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }
    
        try {
            // ✅ Création du tableau de données
            $data = [
                'id' => $recette->getId(),
                'titre' => $recette->getTitre(),
                'preparation' => $recette->getPreparation(),
                'photo' => $recette->getPhoto(),
                'is_active' => $recette->isActive(),
                'created_at' => $recette->getCreatedAt()?->format('Y-m-d H:i:s'),
                'niveau' => $recette->getNiveauLabel(),
                'duree' => $recette->getDuree(),
                'repas' => $recette->getRepas()?->name ?? 'Non défini', // ✅ Évite une erreur si `null`
                'viande' => $recette->getViande(),
            ];
    
            return $this->json($data, Response::HTTP_OK, [
                'Content-Type' => 'application/json'
            ]);
    
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur interne lors de la récupération',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    

        #[Route('/api/recette/{id}', name: 'api_recette_edit', methods: ['PUT', 'PATCH'])]
        public function edit(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
        {
            // ✅ Récupérer les données JSON envoyées par Angular
            $data = json_decode($request->getContent(), true);
        
            if (!isset($data['user'])) {
                return $this->json(['message' => 'Erreur: l\'utilisateur est obligatoire'], JsonResponse::HTTP_BAD_REQUEST);
            }
        
            // ✅ Récupérer l'utilisateur depuis la base de données
            $user = $entityManager->getRepository(User::class)->find($data['user']);
            
            if (!$user) {
                return $this->json(['message' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }

            // ✅ Récupérer la recette en base de données
            $recette = $entityManager->getRepository(Recette::class)->find($id);
            if (!$recette) {
                return $this->json(['message' => 'Recette non trouvée'], JsonResponse::HTTP_NOT_FOUND);
            }
        
            // ✅ Modifier uniquement les champs envoyés
            if (isset($data['titre'])) {
                $recette->setTitre($data['titre']);
            }
            if (isset($data['preparation'])) {
                $recette->setPreparation($data['preparation']);
            }
            if (isset($data['photo'])) {
                $recette->setPhoto($data['photo']);
            }
            if (isset($data['niveau'])) {
                $recette->setNiveau($data['niveau']);
            }
            if (isset($data['repas'])) {
                $recette->setRepas($data['repas']);
            }
            if (isset($data['viande'])) {
                $recette->setViande($data['viande']);
            }
            if (isset($data['duree'])) {
                $recette->setDuree($data['duree']);
            }
        
            // ✅ Modifier l'utilisateur si envoyé
            if (isset($data['user'])) {
                $user = $entityManager->getRepository(User::class)->find($data['user']);
                if (!$user) {
                    return $this->json(['message' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
                }
                $recette->setUser($user);
            }
        
            $entityManager->flush(); // ✅ Sauvegarder les modifications
        
            return $this->json([
                'message' => 'Recette mise à jour avec succès',
                'recette' => [
                    'id' => $recette->getId(),
                    'titre' => $recette->getTitre(),
                    'preparation' => $recette->getPreparation(),
                    'photo' => $recette->getPhoto(),
                    'niveau' => $recette->getNiveau(),
                    'repas' => $recette->getRepas(),
                    'viande' => $recette->getViande(),
                    'duree' => $recette->getDuree(),
                    'user' => $recette->getUser()->getId(),
                ]
            ], JsonResponse::HTTP_OK);
        }
        
    #[Route('/api/recette/{id}', name: 'api_recette_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $recette = $entityManager->getRepository(Recette::class)->find($id);
    
        if (!$recette) {
            return $this->json(['message' => 'Recette non trouvée'], Response::HTTP_NOT_FOUND);
        }
    
        try {
            $entityManager->remove($recette);
            $entityManager->flush();
    
            return $this->json(['message' => 'Recette supprimée avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}