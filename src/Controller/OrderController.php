<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/orders', name: 'app_orders')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        return $this->render('order/index.html.twig', [
            'orders' => $user->getOrders(),
        ]);
    }

    #[Route('/order/checkout', name: 'app_order_checkout')]
    public function checkout(CartService $cartService, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cartItems = $cartService->getFullCart();

        if (empty($cartItems)) {
            return $this->redirectToRoute('app_cart');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setTotal($cartService->getTotal());

        foreach ($cartItems as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];

            // Check stock again just in case
            if ($product->getStock() < $quantity) {
                $this->addFlash('danger', 'Stock insuffisant pour le produit : ' . $product->getName());
                return $this->redirectToRoute('app_cart');
            }

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($product->getPrice());
            
            $order->addItem($orderItem);

            // Decrease stock
            $product->setStock($product->getStock() - $quantity);
        }

        $em->persist($order);
        $em->flush();

        $cartService->clear();

        $this->addFlash('success', 'Votre commande a été validée avec succès !');

        return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
    }

    #[Route('/order/{id}', name: 'app_order_show')]
    public function show(Order $order): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($order->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
