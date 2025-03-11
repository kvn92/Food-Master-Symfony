<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MailController extends AbstractController
{
    #[Route('/test-mail', name: 'test_mail')]
    public function sendMail(MailerInterface $mailer): Response
    {
        $fromEmail = $this->getParameter('mailer_from');
        $email = (new Email())
            ->from($fromEmail) // Expéditeur fictif
            ->to('kevin.nguebdob@gmail.com') // Destinataire fictif
            ->subject('Test Mailer Symfony')
            ->text('Ceci est un email de test.')
            ->html('<p>Ceci est un <strong>email de test</strong>.</p>');

        $mailer->send($email);

        return new Response('Email envoyé avec succès ! Vérifie MailHog ou Mailtrap.');
    }
}