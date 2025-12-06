<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/orders', name: 'app_orders')]
    public function index(): Response
    {
        return $this->render('order/index.html.twig');
    }

    #[Route('/order/{id}', name: 'app_order_show')]
    public function show(int $id): Response
    {
        return $this->render('order/show.html.twig', ['id' => $id]);
    }
}
