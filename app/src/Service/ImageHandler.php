<?php
namespace App\Service;

use App\Entity\Product;
use App\Entity\Image;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageHandler
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function deleteFiles(Product $product): void
    {
        //suppression des images du dossier public/images
        foreach ($product->getImages() as $image) {
            $imagePath = $this->projectDir . '/public/' . $image->getPath();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }

    public function uploadFiles(Product $product, UploadedFile $imageFile, SluggerInterface $slugger, EntityManager $entityManager): void
    {
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->projectDir . '/public/images/product',
                    $newFilename
                );

                $image = new Image();
                $image->setPath('images/product/' . $newFilename);
                $image->setAlt($product->getTitle());
                $image->setIsPrincipal(true);
                $image->setProduct($product);

                $entityManager->persist($image);

            } catch (FileException $entityManager) {
                throw new \Exception('Erreur lors de l\'upload de l\'image.');
            }
        }
    }
}