<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {
        return $this->render('service/index.html.twig');
    }

    #[Route('/service/{id}', name: 'app_service_show')]
    public function show(int $id): Response
    {
        return $this->render('service/show.html.twig', ['id' => $id]);
    }
}
