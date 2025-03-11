<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\DeleteService;
use App\Service\EntityService;
use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('admin/membres',name:'admin.membre.')]
final class AdminMembreController extends AbstractController
{

    public function __construct(private EntityService $entityService, private DeleteService $deleteService)
    {
        $this->entityService = $entityService;
        $this->deleteService = $deleteService;
    }

    #[Route('', name:'index',methods:['GET','POST'])]
    public function index( StatsService $statsService): Response
    { $titre = 'Membres';
        $titrePage = strtoupper($titre);

        $users = $this->entityService->findAll(User::class);
        $stats = $statsService->getEntityStats(User::class); 


        return $this->render('admin/membres/liste-membre.html.twig', [
            'membres'=>$users,
            'stats'=>$stats,
            'titre'=>$titre,
            'titrePage'=> $titrePage,
        ]);
    }


    #[Route('/new', name: 'new', methods: ['POST','GET'])]
    public function new(Request $request, EntityService $entityService, UserPasswordHasherInterface $passwordHasher): Response
    {
        $titre = 'Ajout d’un utilisateur';
        $titrePage = strtoupper($titre);
        
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            try {
                // Hashage du mot de passe
                $plainPassword = $form->get('password')->getData();
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }
    
                // Ajout via EntityService
                $entityService->new($user);
    
                $this->addFlash('success', 'Utilisateur ajouté avec succès.');
                return $this->redirectToRoute('admin.membre.index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }
    
        return $this->render('admin/membres/new.html.twig', [
            'titre' => $titre,
            'titrePage' => $titrePage,
            'form' => $form->createView(),
        ]);
    }
    



    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')] // Protection (supprime si tous les utilisateurs peuvent voir)
    public function show(User $user): Response
    {
        return $this->render('admin/membres/show.html.twig', [
            'titre' => 'Profil',
            'titrePage' => 'Profil',
            'user' => $user,
        ]);
    }



    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request, 
        EntityService $entityService, 
        User $user, 
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // 🔒 Vérifier si l'utilisateur connecté est admin ou édite son propre profil
        if (!$this->isGranted('ROLE_ADMIN') && $user !== $this->getUser()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
    
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // 🔐 Vérifier si l'utilisateur a saisi un nouveau mot de passe
                $plainPassword = $form->get('password')->getData();
                if ($plainPassword) { 
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }
    
                // ✅ Mise à jour via EntityService
                $entityService->edit($user);
    
                $this->addFlash('success', 'Membre mis à jour avec succès.');
                return $this->redirectToRoute('admin.membres'); // ✅ Redirection correcte
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            }
        }
    
        return $this->render('admin/membres/edit.html.twig', [
            'titre' => 'Modifier',
            'titrePage' => 'Modifier un membre',
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
    
        
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, User $user): Response
    {
        try {
            // Utilise DeleteService pour vérifier le CSRF et supprimer l'utilisateur
            $this->deleteService->deleteWithCsrf($user, 'delete_user_' . $user->getId(), $request);
    
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            // Gestion d'erreur si la suppression échoue
            $this->addFlash('error', "Erreur lors de la suppression de l'utilisateur.");
        }
    
        return $this->redirectToRoute('admin.membres');
    }
        



    #[Route('{id}/toggle-statut',name:'toggle_statut')]

    public function toggle(): Response {

       return $this->redirectToRoute('admin.membres');
    }
    }



