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
                    'quantity' => $quantity
                ];
            }
        }

        return $cartWithData;
    }

    public function getTotalQuantity(): int
    {
        $session = $this->requestStack->getSession();

        $cart = $session->get('cart', []);

        return array_sum($cart);
    }

}