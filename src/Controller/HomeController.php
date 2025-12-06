<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_redirect')]
    public function index(ProductRepository $productRepository, ServiceRepository $serviceRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'products' => $productRepository->findAll(),
            'services' => $serviceRepository->findAll(),
        ]);
    }
}
