<?php

namespace App\Controller;

use App\Service\CartHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;  

class CartController extends AbstractController
{
    #[Route('/panier/ajouter/{id}', name: 'cart_add')]
    public function add(int $id, CartHandler $cartHandler): JsonResponse
    {
        //appel methode add pour ajouter le produit au panier
        $cartHandler->add($id);

        return $this->json([
            // On renvoie le nouveau HTML du panier.
            'html' => $this->renderView('cart/_content.html.twig'),

            // On renvoie le nombre total d'articles pour mettre à jour le badge.
            'totalQuantity' => $cartHandler->getTotalQuantity(),
        ]);
    }

    #[Route('/panier/baisser/{id}', name: 'cart_decrease')]
    public function decrease(int $id, CartHandler $cartHandler): JsonResponse 
    {
        $cartHandler->decrease($id);

        return $this->json([
            // On renvoie le nouveau HTML du panier.
            'html' => $this->renderView('cart/_content.html.twig'),

            // On renvoie le nombre total d'articles pour mettre à jour le badge.
            'totalQuantity' => $cartHandler->getTotalQuantity(),
        ]);
    }

    #[Route('/panier/supprimer/{id}', name: 'cart_remove')]
    public function remove(int $id, CartHandler $cartHandler): JsonResponse
    {
        $cartHandler->remove($id);
        return $this->json([
            // On renvoie le nouveau HTML du panier.
            'html' => $this->renderView('cart/_content.html.twig'),

            // On renvoie le nombre total d'articles pour mettre à jour le badge.
            'totalQuantity' => $cartHandler->getTotalQuantity(),
        ]);
    }

    #[Route('/panier/clear', name: 'cart_clear')]
    public function clear(CartHandler $cartHandler): JsonResponse
    {
        $cartHandler->clear();
        return $this->json([
            // On renvoie le nouveau HTML du panier.
            'html' => $this->renderView('cart/_content.html.twig'),

            // On renvoie le nombre total d'articles pour mettre à jour le badge.
            'totalQuantity' => $cartHandler->getTotalQuantity(),
        ]);
    }
}