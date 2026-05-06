<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Image;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class AdminProductController extends AbstractController
{
    #[Route('/admin/product/new', name: 'admin_product_new')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response {

        $product = new Product();
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images/product',
                        $newFilename
                    );

                    $image = new Image();
                    $image->setPath('images/product/' . $newFilename);
                    $image->setAlt($product->getTitle());
                    $image->setIsPrincipal(true);
                    $image->setProduct($product);

                    $entityManager->persist($image);
                } catch (FileException $entityManager) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été ajouté.');

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/new-product.html.twig', [
            'form' => $form,
        ]);
    }
}
