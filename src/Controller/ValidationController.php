<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\LikeRecette;
use App\Entity\Recette;
use App\Entity\SauvergardeRecette;
use App\Entity\User;
use App\Form\CommentaireType;
use App\Form\RecetteType;
use App\Repository\LikeRecetteRepository;
use App\Repository\RecetteRepository;
use App\Repository\SauvergardeRecetteRepository;
use App\Service\DeleteService;
use App\Service\EntityService;
use App\Service\StatsService;
use App\Service\StatusToggleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('admin/',name:'admin.')]
final class ValidationController extends AbstractController
{

    public function __construct(
        private StatsService $statsService ,
        private EntityService $entityService,
        private DeleteService $deleteService,
    ) {
        $this->entityService = $entityService;
        $this->deleteService = $deleteService;
        $this->statsService = $statsService;
    }

   



    #[Route('/{id}/toggle_statut', name: 'toggle_statut', methods: ['GET'],requirements: ['id' => '\d+'])]
    public function toggleStatus(Recette $recette, StatusToggleService $statusToggleService): Response
    {
        // Basculer le statut
        $statusToggleService->toggleStatus($recette, 'isActive');

        // Rediriger vers la indexe des catégories
        return $this->redirectToRoute('admin.recette.index');
    }



    #[Route('recette/validation', name:'recette.validation',methods:['GET','POST'])]
    public function recettesValid( ): Response{
        $titrePage = 'Validation Recettes';
        $recetteValidations = $this->entityService->findBy(Recette::class,false,'DESC');
        $stats = $this->statsService->getEntityStats(Recette::class);


        return $this->render('validations/recettes/recette-validation.html.twig',[
            'recettes'=>$recetteValidations,
            'titrePage'=>$titrePage,
            'stats'=>$stats,
 

        ]);



    }

    #[Route('/{id}/delete', name: 'recette.validation.delete', methods: ['POST'], requirements: ['id' => '\d+'])]   
    public function delete(Request $request, Recette $recette) : Response
   {
       $this->deleteService->deleteWithCsrf($recette, 'delete' . $recette->getId(), $request);

       $this->addFlash('success', 'La recette  a été  supprimé a été supprimé avec succès.');

       return $this->redirectToRoute('admin.recette.validation');
   }

   #[Route('/{id}/delete', name: 'commentaire.validation.delete', methods: ['POST'], requirements: ['id' => '\d+'])]   
    public function deleteCom(Request $request, Commentaire $commentaire) : Response
   {
       $this->deleteService->deleteWithCsrf($commentaire, 'delete' . $commentaire->getId(), $request);

       $this->addFlash('success', 'Le commentaire a été supprimé avec succès.');

       return $this->redirectToRoute('admin.recette.validation');
   }






}
