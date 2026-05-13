<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class ItemController extends AbstractController
{
    #[Route('/', name: 'app_item_index')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        $categories = $categoryRepository->findAll();
        $categoryId = $request->query->getInt('category', 0);

        if ($categoryId > 0) {
            $found = array_filter($categories, fn($c) => $c->getId() === $categoryId);
            if (!$found) {
                return $this->redirectToRoute('app_item_index');
            }
            $products = $productRepository->findByCategoryWithPrincipalImage($categoryId);
        } else {
            $products = $productRepository->findAllWithPrincipalImage();
        }

        return $this->render('item/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'currentCategoryId' => $categoryId,
        ]);
    }

    #[Route('/produit/{id}', name: 'product_show')]
    public function show(int $id, ProductRepository $productRepository, Request $request): Response
    {
        $product = $productRepository->findOneWithCategoryAndImages($id);
        //btn retour
        $back = $request->query->get('back', $this->generateUrl('app_item_index'));

        if (!$back || !str_starts_with($back, '/')) {
            $back = $this->generateUrl('app_item_index');
        }

        return $this->render('item/show.html.twig', [
            'product' => $product,
            'back' => $back,
        ]);
    }
}
