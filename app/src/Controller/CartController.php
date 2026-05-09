<?php

namespace App\Controller;

use App\Service\CartHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/panier/ajouter/{id}', name: 'cart_add')]
    public function add(int $id, CartHandler $cartHandler): RedirectResponse
    {
        //appel methode add pour ajouter le produit au panier
        $cartHandler->add($id);

        return $this->redirectToRoute('product_show', [
            'id' => $id
        ]);
    }
}