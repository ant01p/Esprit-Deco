<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ImageHandler;


final class AdminProductController extends AbstractController
{
    #[Route('/admin/product/new', name: 'admin_product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageHandler $imageHandler): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {

                $image = $imageHandler->uploadFiles($imageFile);
                $image->setAlt($product->getTitle());
                $product->addImage($image);

                $entityManager->persist($image);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été ajouté.');

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/new-product.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}