<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReglementController extends AbstractController
{
    #[Route('/conditions-generales', name: 'cgu')]
    public function cgu(): Response
    {
        $datePublication = '27 février 2025';
        return $this->render('reglement/condition-generale.html.twig',[
            'datePublication'=>$datePublication
        ]);
    }

    #[Route('/mentions-legales', name: 'mentions.legales')]
    public function mention(): Response
    {
        $datePublication = '27 février 2025';
        return $this->render('reglement/condition-generale.html.twig',[
            'datePublication'=>$datePublication
        ]);
    }
}
