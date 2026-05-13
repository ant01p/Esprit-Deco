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
use App\Repository\OrderRepository;

final class OrderController extends AbstractController
{   
    public function __construct(
        private CartHandler $cartHandler,
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository
    )
    {
    }

    #[Route('/order/delivery-address', name: 'app_order_delivery_address')]
    public function deliveryAddress(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartHandler->getCart();

        $order = $this->orderRepository->findOneBy(['user' => $this->getUser(), 'status' => 'pending']);

        if($order) {
            $address = $order->getAddress();
        } else {
            $address = new Address();
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();

            if (!$order) {
                $order = new Order();
                $order->setUser($this->getUser());
                $order->setStatus('pending');
                $order->setCreatedAt(new \DateTimeImmutable());
                $order->setTotalAmount($this->cartHandler->getTotal());
            }

            $order->setAddress($address);

            $address->setOrderpurchase($order);

            $this->entityManager->persist($order);
            $this->entityManager->persist($address);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_order_validate');
        }

        return $this->render('order/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/order/validate', name: 'app_order_validate')]
    public function validate(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartHandler->getCart();

        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide. Veuillez ajouter des produits avant de valider une commande.');
            return $this->redirectToRoute('app_item_index');
        }

        $order = $this->orderRepository->findOneBy(['user' => $this->getUser(), 'status' => 'pending']);

        if (!$order) {
            return $this->redirectToRoute('app_order_delivery_address');
        }

        return $this->render('order/validate.html.twig', [
            'order' => $order,
            'cart' => $cart,
            'total' => $this->cartHandler->getTotal(),
        ]);
    }

    #[Route('/order/confirm', name: 'app_order_confirm')]
    public function confirm(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // TODO: Paiement Stripe sera implémenté en 2)c)

        return $this->redirectToRoute('app_item_index');
    }
}
