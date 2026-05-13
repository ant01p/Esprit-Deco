<?php

namespace App\Controller;

use App\Entity\Address;
use App\Service\CartHandler;
use App\Entity\Order;
use App\Form\AddressType;
use App\Service\StripeService;
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
        private OrderRepository $orderRepository,
        private StripeService $stripeService
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

        $order = $this->orderRepository->findOneBy(['user' => $this->getUser(), 'status' => 'pending']);

        if (!$order) {
            return $this->redirectToRoute('app_order_delivery_address');
        }

        try {
            $session = $this->stripeService->createCheckoutSession(
                $order,
                $this->generateUrl('app_payment_success', [], 0),
                $this->generateUrl('app_payment_cancel', [], 0)
            );

            $order->setStripeSessionId($session->id);
            $this->entityManager->flush();

            return $this->redirect($session->url);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la session de paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_order_validate');
        }
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function paymentSuccess(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $order = $this->orderRepository->findOneBy(['user' => $this->getUser(), 'status' => 'pending']);

        if ($order) {
            $session = $this->stripeService->retrieveSession($order->getStripeSessionId());
            $order->setStripePaymentIntentId($session->payment_intent);
            $order->setStatus('completed');
            $this->entityManager->flush();
            $this->cartHandler->clear();
            $this->addFlash('success', 'Votre commande a été validée avec succès!');
        }

        return $this->redirectToRoute('app_item_index');
    }

    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function paymentCancel(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->addFlash('warning', 'Paiement annulé. Votre commande est toujours en attente de paiement.');
        return $this->redirectToRoute('app_order_validate');
    }
}
