<?php
namespace App\Service;

use App\Entity\Product;

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
}