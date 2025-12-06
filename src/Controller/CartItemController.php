<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartItemController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig');
    }

    #[Route('/cart/{id}', name: 'app_cart_show')]
    public function show(int $id): Response
    {
        return $this->render('cart/show.html.twig', ['id' => $id]);
    }
}
