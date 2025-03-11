<?php 

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class AccessDeniedSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAccessDenied(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Vérifie si l'exception est un AccessDeniedException
        if ($exception instanceof AccessDeniedException) {
            // Ajoute un message flash pour informer l'utilisateur
            $request = $event->getRequest();
            $request->getSession()->getFlashBag()->add('error', 'Accès refusé. Veuillez vous connecter.');

            // Redirige vers la page de connexion
            $response = new RedirectResponse($this->router->generate('app_home'));
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onAccessDenied',
        ];
    }
}
