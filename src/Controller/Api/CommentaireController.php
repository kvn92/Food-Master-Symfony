<?php

namespace App\Controller\Api;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CommentaireController extends AbstractController
{
    #[Route('/ap/commentaires/{projetId}', name: 'get_commentaires', methods: ['GET'])]
    public function getCommentaires(CommentaireRepository $repo, int $projetId, SerializerInterface $serializer): JsonResponse
    {
        $commentaires = $repo->findBy(['projet' => $projetId], ['createdAt' => 'DESC']);
        return new JsonResponse($serializer->serialize($commentaires, 'json'), 200, [], true);
    }
}