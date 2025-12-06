<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_redirect')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home_admin');
        }

        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('home_user');
        }

        return $this->redirectToRoute('app_login');
    }
}
