<?php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use App\Entity\User;
use App\Entity\Attendant;
use Doctrine\ORM\EntityManagerInterface;

class AuthenticationSuccessListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();
        $token = $data['token'];

        // Para atendentes
        if ($user instanceof User && $user->isIsAttendant()) {

            $attendant = $this->entityManager->getRepository(\App\Entity\Attendant::class)
            ->findOneBy(['user' => $user]);

               $responseData = [
                'success' => true,
                'data' => [
                    'attendant' => [
                        'id' => $user->getId(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail(),
                        'attendant' => [ 
                            'function' => $attendant->getFunction(),
                            'sector' => [
                                'id' => $attendant->getSector()?->getId(),
                                'name' => $attendant->getSector()?->getName()
                        ],
                       ]
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