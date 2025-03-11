<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Entity\User;
use App\Repository\RecetteRepository;
use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods:['GET'])]
    public function index(RecetteRepository $recetteRepository ,StatsService $statsService, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManagerInterface): Response
    {
        $recettesDerniereSortie =  $recetteRepository->findBy(['isActive'=>true],['createdAt'=>'DESC'],4);
        $stats = $statsService->getEntityStats(Recette::class);
        
        $recettesPopulaires = $recetteRepository->findMostLikedRecette();
        

        #$user = new User();
        #$t =  $hasher->hashPassword($user,'adminn');
       #$user->setPseudo('edwin')->setPassword($t)->setEmail('edwin@gmail.com')->setRoles(['ROLE_ADMIN']);
       #$entityManagerInterface->persist($user);
      #$entityManagerInterface->flush();
     

        return $this->render('home/index.html.twig', [
            'stats'=>$stats,
            'recettesDerniereSortie'=>$recettesDerniereSortie,
            'recettesPopulaires'=>$recettesPopulaires
          
        ]);
    }
}
