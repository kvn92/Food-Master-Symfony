<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserFollow;
use App\Repository\UserFollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/follow', name: 'follow.')]
class FollowController extends AbstractController
{
    #[Route('/toggle/{id}', name: 'toggle', methods: ['POST'])]
    public function toggleFollow(User $user, UserFollowRepository $userFollowRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $currentUser = $this->getUser();

        // Vérifie si l'utilisateur est bien connecté
        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour suivre quelqu’un.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifie si l'utilisateur essaie de se suivre lui-même
        if ($currentUser === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas vous suivre vous-même.');
            return $this->redirect($request->headers->get('referer') ?: 'app_home');
        }

        // Vérifie si l'utilisateur suit déjà cette personne
        $existingFollow = $userFollowRepository->findOneBy([
            'follower' => $currentUser,
            'following' => $user,
        ]);

        if ($existingFollow) {
            // Si l'utilisateur suit déjà, on le supprime (désabonnement)
            $entityManager->remove($existingFollow);
            $entityManager->flush();
            $this->addFlash('success', 'Vous ne suivez plus ' . $user->getPseudo() . '.');
        } else {
            // Sinon, on crée un nouvel abonnement (suivre)
            $follow = new UserFollow();
            $follow->setFollower($currentUser);
            $follow->setFollowing($user);
            $follow->setCreateAt(new \DateTimeImmutable());

            $entityManager->persist($follow);
            $entityManager->flush();
            $this->addFlash('success', 'Vous suivez maintenant ' . $user->getPseudo() . '.');
        }

        // Redirige vers la page précédente
        return $this->redirect($request->headers->get('referer') ?: 'app_home');
    }
}
