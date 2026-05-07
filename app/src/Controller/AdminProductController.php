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
    #[Route('/admin/product/form/{id}', name: 'admin_product_form', defaults: ['id' => null])]
    public function form(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageHandler $imageHandler,
        ?Product $product = null
    ): Response {
        $isNew = false;

        if (!$product) {
            $product = new Product();
            $isNew = true;
        }

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

            if ($isNew) {
                $entityManager->persist($product);
            }

            $entityManager->flush();

            if ($isNew) {
                $this->addFlash('success', 'Le produit a bien été ajouté.');
            } else {
                $this->addFlash('success', 'Le produit a bien été modifié.');
            }

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/form-product.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'isNew' => $isNew,
        ]);
    }
}