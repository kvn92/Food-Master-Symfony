<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin', name: 'admin.')]

final class MembreController extends AbstractController
{
    



    #[Route('/{id}', name: 'profil',methods:['GET'],requirements:['id'=>'\d+'])]
    public function show(UserRepository $userRepository, int $id ): Response
    {
        $membre = $userRepository->find($id);
        if (!$membre) {
            throw $this->createNotFoundException("Membre non trouvé !");
        }
    
        $recettes = $membre->getRecettes();
        return $this->render('membre/profil.html.twig', [
            'membre' => $membre,
            'recettes' =>$recettes
        ]);
    }

    
}
