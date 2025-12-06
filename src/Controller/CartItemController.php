<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartItemController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cartService): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home_redirect');
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal()
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(int $id, CartService $cartService): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home_redirect');
        }

        $cartService->add($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(int $id, CartService $cartService): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home_redirect');
        }

        $cartService->remove($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrease/{id}', name: 'cart_decrease')]
    public function decrease(int $id, CartService $cartService): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home_redirect');
        }

        $cartService->decrease($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(CartService $cartService): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home_redirect');
        }

        $cartService->clear();
        return $this->redirectToRoute('app_cart');
    }
}
