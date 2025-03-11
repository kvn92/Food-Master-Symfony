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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('admin/recette',name:'admin.recette.')]
final class RecetteController extends AbstractController
{

    public function __construct(
        private EntityService $entityService,
        private DeleteService $deleteService
    ) {
        $this->entityService = $entityService;
    }

   
    #[Route('', name:'index',methods:['GET','POST'])]
    public function index( StatsService $statsService): Response
    {
        $titre = 'Recettes';
        $titrePage = strtoupper($titre);

        $recettes = $this->entityService->findAll(Recette::class);

        if (!$recettes) {
            throw $this->createNotFoundException("La recette demandée n'existe pas.");
        }
    
        $stats = $statsService->getEntityStats(Recette::class); 



        return $this->render('recette/index.html.twig', [
            'recettes'=>$recettes,
            'stats'=>$stats,
            'titre'=>$titre,
            'titrePage'=> $titrePage,

        ]);
    }

    #[Route('/new',name:'new',methods:['POST','GET'])]
    #[IsGranted('ROLE_ADMIN')] 
    public function new(Request $request): Response
    {
        $titre = 'Ajouter une recette';
        $titrePage = strtoupper($titre);


        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette, ['is_edit' => true]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('photo')->getData();

            try {
                $this->entityService->new($recette,$file,'recettes');
                $this->addFlash('success', 'Recette ajoutée avec succès.');
               
                return $this->redirectToRoute('admin.recette.index');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
    
        return $this->render('recette/new.html.twig', [
            'titre'=>$titre,
            'titrePage'=> $titrePage,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}',name:'show',methods:['POST','GET'],requirements:['id'=>'\d+'])]
    public function show(Request $request,int $id, Security $security): Response
    {

        $recette = $this->entityService->find(Recette::class, $id);
        if (!$recette) {
            throw $this->createNotFoundException('Recette non trouvée.');
        }

        $commentaires = $this->entityService->findAll(Commentaire::class);


        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            try{
                $user = $security->getUser();
               $commentaire->setRecette($recette)->setUser($user); 
             $this->entityService->new($commentaire);
            
            $this->addFlash('success', 'Commentaire  ajouté avec succès.');
            return $this->redirectToRoute('admin.recette.index');


        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
            
          

        }
        return $this->render('recette/show.html.twig', [
        'recette' => $recette,
        'form'=>$form,
        'commentaires'=>$commentaires
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Recette $recette, Request $request, EntityService $entityService): Response
    {
        $titrePage = $recette->getTitre();

        // On passe une option 'is_edit' pour gérer les différences dans le formulaire
        $form = $this->createForm(RecetteType::class, $recette, ['is_edit' => true]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('photo')->getData();
    
            try {
                $entityService->edit($recette, $file, 'recettes');
                $this->addFlash('success', 'Recette modifiée avec succès.');
                return $this->redirectToRoute('admin.recette.index');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
    
        return $this->render('recette/edit.html.twig', [
            'form' => $form->createView(),
            'recette' => $recette,
            'titrePage'=> $titrePage 
        ]);
    }






    #[Route('/{id}/toggle_statut', name: 'toggle_statut', methods: ['GET'],requirements: ['id' => '\d+'])]
    public function toggleStatus(Recette $recette, StatusToggleService $statusToggleService): Response
    {
        // Basculer le statut
        $statusToggleService->toggleStatus($recette, 'isActive');

        // Rediriger vers la indexe des catégories
        return $this->redirectToRoute('admin.recette.index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]   
     public function delete(Request $request, Recette $recette) : Response
    {
        $this->deleteService->deleteWithCsrf($recette, 'delete' . $recette->getId(), $request);

        $this->addFlash('success', 'La recette a été supprimée avec succès.');

        return $this->redirectToRoute('admin.recette.index');
    }





    #[Route('/{id}/like', name: 'like', methods: ['POST','GET'])]
    public function toggleLike(
        int $id, 
        RecetteRepository $recetteRepository, 
        LikeRecetteRepository $likeRecetteRepository, 
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $recette = $recetteRepository->find($id);
        $user = $security->getUser();
    
        if (!$recette) {
            throw $this->createNotFoundException('Recette introuvable.');
        }
    
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour liker une recette.');
            return $this->redirectToRoute('recette.show', ['id' => $id]);
        }
    
        // Vérifier si l'utilisateur a déjà liké cette recette
        $like = $likeRecetteRepository->findOneBy(['user' => $user, 'recette' => $recette]);
    
        if ($like) {
            // Supprimer le like
            $entityManager->remove($like);
            $this->addFlash('success', 'Like retiré.');
        } else {
            // Ajouter un like
            $like = new LikeRecette();
            $like->setUser($user);
            $like->setRecette($recette);
            $entityManager->persist($like);
            $this->addFlash('success', 'Recette likée.');
        }
    
        $entityManager->flush();
    
        return $this->redirectToRoute('recette.show', ['id' => $id]);
    }


    #[Route('/{id}/sauvegarder', name: 'sauvegarder', methods: ['POST','GET'])]
    public function toggleSauvegarde(
        int $id, 
        RecetteRepository $recetteRepository, 
        SauvergardeRecetteRepository $sauvergardeRecetteRepository, 
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $recette = $recetteRepository->find($id);
        $user = $security->getUser();
    
        if (!$recette) {
            throw $this->createNotFoundException('Recette introuvable.');
        }
    
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour liker une recette.');
            return $this->redirectToRoute('recette.show', ['id' => $id]);
        }
    
        // Vérifier si l'utilisateur a déjà liké cette recette
        $sauvegarder = $sauvergardeRecetteRepository->findOneBy(['user' => $user, 'recette' => $recette]);
    
        if ($sauvegarder) {
            // Supprimer le like
            $entityManager->remove($sauvegarder);
            $this->addFlash('success', 'Like retiré.');
        } else {
            // Ajouter un like
            $sauvegarder = new SauvergardeRecette();
            $sauvegarder->setUser($user);
            $sauvegarder->setRecette($recette);
            $sauvegarder->setIsActive(true);
            $entityManager->persist($sauvegarder);
            $this->addFlash('success', 'Recette dans les favoris.');
        }
    
        $entityManager->flush();
    
        return $this->redirectToRoute('recette.show', ['id' => $id]);
    }





}
