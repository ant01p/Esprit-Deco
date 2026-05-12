<?php

namespace App\Controller;

use App\Entity\Address;
use App\Service\CartHandler;
use App\Entity\Order;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{   
    public function __construct(
        private CartHandler $cartHandler,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/order/delivery-address', name: 'app_order_delivery_address')]
    public function deliveryAddress(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartHandler->getCart();

        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide.');

            return $this->redirectToRoute('app_item_index');
        }

        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();

            $order = new Order();

            $order->setUser($this->getUser());
            $order->setStatus('pending');
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setTotalAmount($this->cartHandler->getTotal());
            
            $address->setOrderpurchase($order);

            $this->entityManager->persist($order);
            $this->entityManager->persist($address);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_item_index');
        }
       
        return $this->render('order/index.html.twig', [
            'form' => $form,
        ]);
    }
}
