<?php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use App\Entity\User;
use App\Entity\Attendant;

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();
        $token = $data['token'];

        // Para atendentes
        if ($user instanceof Attendant) {
            $responseData = [
                'success' => true,
                'data' => [
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
                    'token' => $token
                ]
            ];
        }
        // Para usuários comuns
        elseif ($user instanceof User) {
            $responseData = [
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->getId(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail(),
                        'roles' => $user->getRoles()
                    ],
                    'token' => $token
                ]
            ];
        }

        $event->setData($responseData);
    }
}