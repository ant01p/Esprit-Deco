<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandler
{
    public function __construct( private RequestStack $requestStack, private ProductRepository $productRepository )  
    {
    }

    public function add(int $id): void
    {
        // récupération de la session
        $session = $this->requestStack->getSession();

        // on récup panier ou créa d'un tableau vide
        $cart = $session->get('cart', []);

        // si produit existe on augmente quantité
        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            //ajoute prod de quantié 1
            $cart[$id] = 1;
        }

        //sauvegarde en session
        $session->set('cart', $cart);
    }

    public function decrease(int $id): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        // si produit n'est pas dans le panier on ne fait rien
        if (!isset($cart[$id])) {
            return;
        }

        // on baisse la quantité
        $cart[$id]--;

        // si la quantité arrive a 0 on supprime produit du panier
        if ($cart[$id] <= 0) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);
    }

     public function remove(int $id): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        // supprime la ligne du panier
        unset($cart[$id]);

        $session->set('cart', $cart);
    }

    public function clear(): void
    {
        $session = $this->requestStack->getSession();

        //supprime le panier de la session
        $session->remove('cart');
    }

    //pour récup le pannier complet
    public function getCart(): array
    {
        $session = $this->requestStack->getSession();

        $cart = $session->get('cart', []);

        //contient produits + quantité
        $cartWithData = [];

        foreach ($cart as $productId => $quantity) {
            //récup prduit en bdd
            $product = $this->productRepository->find($productId);

            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'lineTotal' => $product->getPrice() * $quantity
                ];
            }
        }

        return $cartWithData;
    }

    //badge panier
    public function getTotalQuantity(): int
    {
        $session = $this->requestStack->getSession();

        $cart = $session->get('cart', []);

        return array_sum($cart);
    }

     public function getTotal(): float
    {
        $total = 0;

        // on récupère le panier complet avec les produits
        $cart = $this->getCart();

        foreach ($cart as $cartLine) {
            // on ajoute le total de chaque ligne au total général
            $total += $cartLine['lineTotal'];
        }

        return $total;
    }
}