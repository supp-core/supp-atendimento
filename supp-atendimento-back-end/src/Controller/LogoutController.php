<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
    public function __construct(private KernelInterface $kernel) {}

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse([
            'success' => true,
            'message' => 'Logout realizado'
        ]);

        $response->headers->setCookie(
            Cookie::create('jwt_token')
                ->withValue('')
                ->withExpires(time() - 3600)
                ->withPath('/')
                ->withSecure($this->kernel->getEnvironment() === 'prod')
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_STRICT)
        );

        return $response;
    }
}
