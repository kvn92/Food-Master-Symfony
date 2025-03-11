<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Recette;
use App\Entity\User;
use App\Service\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin',name:'admin.')]
final class DashboardController extends AbstractController
{


    #[Route('', name: 'dashboard',methods:['GET','POST'])]
    public function dashboard(StatsService $statsService): Response
    {
        $titrePage = "Tableau de Bord Administrateur";
        $statsRecettes = $statsService->getEntityStats(Recette::class); 
        $statsMembres = $statsService->getEntityStats(User::class);
        $statsCommentaires = $statsService->getEntityStats(Commentaire::class);

        return $this->render('admin/dashboard.html.twig', [
            'titrePage'=>$titrePage,
            'statsRecettes' => $statsRecettes,
            'statsMembres'=>$statsMembres,
            'statsCommentaires'=>$statsCommentaires
        ]);
    }



    #[Route('', name: 'setting')]
    public function setting(): Response
    {
        return $this->render('admin_dashboard/index.html.twig', [
            'controller_name' => 'AdminDashboardController',
        ]);
    }
}
