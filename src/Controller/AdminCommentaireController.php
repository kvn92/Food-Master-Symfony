<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use App\Service\DeleteService;
use App\Service\EntityService;
use App\Service\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commentaire', name: 'admin.commentaire.')]
final class AdminCommentaireController extends AbstractController
{

    private DeleteService $deleteService;

    public function __construct(DeleteService $deleteService,private EntityService $entityService)
    {
        $this->deleteService = $deleteService;
        $this->entityService = $entityService;
    }
   
    #[Route('/validation', name:'validation', methods:['GET','POST'])]
    public function commentaireValid(StatsService $statsService): Response
    {
        $titrePage = 'Validation des commentaires';
        $commentaires = $this->entityService->findBy(Commentaire::class, false, 'DESC');
        $stats = $statsService->getEntityStats(Commentaire::class);
    
        if (empty($commentaires)) {
            $this->addFlash('info', 'Aucun commentaire en attente de validation.');
            return $this->redirectToRoute('admin.dashboard'); // Redirection vers une autre page
        }
    
        return $this->render('validations/commentaires/commentaire-validation.html.twig', [
            'titrePage' => $titrePage,
            'commentaires' => $commentaires,
            'stats' => $stats
        ]);
    }





    #[Route('/validation/{id}', name:'validation.show',methods:['GET','POST'])]
    public function commentaireValidShow(EntityService $entityService, int $id): Response{


        $titrePage = 'Validation des commentaires';
        $commentaire = $entityService->find(Commentaire::class,$id);

if (!$commentaire) {
        throw $this->createNotFoundException("Le commentaire avec l'ID $id n'existe pas.");
    }

        return $this->render('validations/commentaires/show.html.twig',[
        'commentaire'=>$commentaire
        ]);
    }


    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])] 
  #[IsGranted('ROLE_ADMIN')]

     public function delete(Request $request, Commentaire $commentaire) : Response
    {
        $this->deleteService->deleteWithCsrf($commentaire, 'delete' . $commentaire->getId(), $request);

        $this->addFlash('success', 'Le commentaire a été supprimé avec succès.');

        return $this->redirectToRoute('admin.recette.index');
    }
}