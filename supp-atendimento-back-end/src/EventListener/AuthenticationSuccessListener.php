<?php
// src/EventListener/AuthenticationSuccessListener.php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use App\Entity\Attendant;

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();

        // Verificamos se o usuário é um Attendant
        if (!$user instanceof Attendant) {
            return;
        }

        // Estruturamos os dados que queremos retornar
        $data['success'] = true;
        $data['data'] = [
            'attendant' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'function' => $user->getFunction(),
                'sector' => [
                    'id' => $user->getSector()?->getId(),
                    'name' => $user->getSector()?->getName()
                ],
            ],
            'token' => $data['token']
        ];

        // Removemos o token da raiz do objeto
        unset($data['token']);

        // Atualizamos os dados do evento
        $event->setData($data);
    }
}