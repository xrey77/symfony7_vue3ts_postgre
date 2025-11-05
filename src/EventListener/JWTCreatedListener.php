<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Events;

class JWTInvalidListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {

        return [
            'lexik_jwt_authentication.on_jwt_invalid' => 'onJWTInvalid',
            'lexik_jwt_authentication.on_jwt_expired' => 'onJWTExpired',
            'lexik_jwt_authentication.on_jwt_not_found' => 'onJWTNotFound',
        ];
    }

    #[AsEventListener(event: Events::JWT_INVALID)] // Ensure you are subscribed to the correct event    
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = new JsonResponse(['message' => 'Bearer Token is not valid.'],401);
        $event->setResponse($response);
    }

    #[AsEventListener(event: Events::JWT_EXPIRED)] // Ensure you are subscribed to the correct event
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $response = new JsonResponse(['message' => 'Bearer Token is already expired.'],401);
        $event->setResponse($response);
    }

    #[AsEventListener(event: Events::JWT_NOT_FOUND)] // Ensure you are subscribed to the correct event    
    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $response = new JsonResponse(['message' => 'Please enter Bearer Token.'],401);
        $event->setResponse($response);
    }

}