<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Service\DeleteService;
use App\Service\EntityService;
use App\Service\StatsService;
use App\Service\StatusToggleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin/ingredient', name: 'admin.ingredient.')]
final class IngredientController extends AbstractController
{

    public function __construct( private EntityService $entityService,private StatsService $statsService, private DeleteService $deleteServices)
    {
        $this->statsService = $statsService;
        $this->entityService = $entityService;
        $this->deleteServices = $deleteServices;
        

    }
   

    #[Route('', name:'index')]
    public function index(): Response
    {

        $titre = 'Liste des ingredient';
        $ingredients = $this->entityService->findAll(Ingredient::class);

        $stats = $this->statsService->getEntityStats(Ingredient::class);
        return $this->render('ingredient/index.html.twig', [
            'titrePage' => $titre,
            'stats'=> $stats,
            'ingredients' => $ingredients
        ]);
    }

    #[Route('/new', name:'new', methods:['POST','GET'])]
    public function new(Request $request):Response{

        $titrePage = 'Ajouter un ingrédient';
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient, ['is_edit' => false]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityService->new($ingredient);
                $this->addFlash('success', 'Ingredient ajoutée avec succès.');
                return $this->redirectToRoute('admin.ingredient.index');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
    
        return $this->render('ingredient/new.html.twig', [
            'titrePage'=>$titrePage,
            'form' => $form->createView(),
        ]);
    }

    #[Route('{id}/edit', name:'edit', methods:['POST','GET'])]

    public function edit(Request $request,Ingredient $ingredient) : Response{
        
         $titrePage = 'Ajouter un ingrédient';
    
        $form = $this->createForm(IngredientType::class, $ingredient,['is_edit'=>true]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            try {
                $this->entityService->edit($ingredient);
                $this->addFlash('success', 'Mise à jour avec succès.');
                return $this->redirectToRoute('admin.ingredient.index');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }   
        return $this->render('ingredient/edit.html.twig', [
            'titrePage'=>$titrePage,
            'form' => $form->createView(),
            
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Ingredient $ingredient): Response
    {
        // Vérifie si l'utilisateur existe
        

        try {
            // Utilise DeleteService pour vérifier le CSRF et supprimer l'utilisateur
            $this->deleteServices->deleteWithCsrf($ingredient, 'delete_ingredient_' . $ingredient->getId(), $request);
    
            $this->addFlash('success', 'ingredient supprimé avec succès.');
        } catch (\Exception $e) {
            // Gestion d'erreur si la suppression échoue
            $this->addFlash('error', "Erreur lors de la suppression de l'ingredient.");
        }
    
        return $this->redirectToRoute('admin.ingredient.index');
    }
    
 
    #[Route('/toggle/{id}', name: 'toggle.status', methods: ['GET'])]
    public function toggleStatus(Ingredient $ingredient, StatusToggleService $statusToggleService): RedirectResponse
    {
        // Bascule le statut de l'ingrédient
        $statusToggleService->toggleStatus($ingredient, 'isActive');

        // Ajout d'un message flash
        $this->addFlash('success', sprintf("Le statut de l'ingrédient %s a été mis à jour.", $ingredient->getIngredient()));

        // Redirection vers la liste des ingrédients (ou autre page)
        return $this->redirectToRoute('admin.ingredient.index');
    }

}
