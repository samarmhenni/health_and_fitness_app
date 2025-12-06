<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $requestStack;
    private $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    public function add(int $id): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $product = $this->productRepository->find($id);

        if (!$product) {
            return;
        }

        $currentQuantity = $cart[$id] ?? 0;

        if ($currentQuantity < $product->getStock()) {
            $cart[$id] = $currentQuantity + 1;
        }

        $session->set('cart', $cart);
    }

    public function remove(int $id): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);
    }

    public function decrease(int $id): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $session->set('cart', $cart);
    }

    public function getFullCart(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartWithData;
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getFullCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        return $total;
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove('cart');
    }
}
