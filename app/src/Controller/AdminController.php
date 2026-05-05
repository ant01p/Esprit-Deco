<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/product', name: 'admin_product_index')]
    public function productIndex(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $products = $productRepository->findAllWithCategories();

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/product/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $entityManager ): Response {
   
        //sécurité CSRF
        if (!$this->isCsrfTokenValid('delete_product_' . $product->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Action invalide.');
            return $this->redirectToRoute('admin_product_index');
        }

        //suppression des images du dossier public/images
        foreach ($product->getImages() as $image) {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/' . $image->getPath();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        //suppression produit + images en BDD (cascade)
        $entityManager->remove($product);
        $entityManager->flush();

        //message flash
        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('admin_product_index');
    }
}

