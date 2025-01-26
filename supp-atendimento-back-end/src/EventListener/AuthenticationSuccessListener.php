<?php
// src/EventListener/AuthenticationSuccessListener.php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use App\Entity\User;  // Entidade do usuário comum
use App\Entity\Attendant;  // Entidade do atendente

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();

        // Verifica se o usuário é um atendente
        if ($user instanceof Attendant) {
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
        }
        // Verifica se o usuário é um usuário comum
        elseif ($user instanceof User) {
            $data['success'] = true;
            $data['data'] = [
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    // Adicione outros campos relevantes do usuário comum
                ],
                'token' => $data['token']
            ];
        }

        // Remove o token da raiz do objeto (opcional, dependendo da sua preferência)
        unset($data['token']);

        // Atualiza os dados do evento
        $event->setData($data);
    }
}
