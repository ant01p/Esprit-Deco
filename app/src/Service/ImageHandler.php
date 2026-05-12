<?php
namespace App\Service;

use App\Entity\Product;
use App\Entity\Image;
use App\Repository\ProductRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class ImageHandler
{
    private string $projectDir;
    private RequestStack $requestStack;
    private SluggerInterface $slugger;

    public function __construct(string $projectDir, RequestStack $requestStack, SluggerInterface $slugger)
    {
        $this->projectDir = $projectDir;
        $this->requestStack = $requestStack;
        $this->slugger = $slugger;
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

    public function uploadFiles( UploadedFile $imageFile,): Image  
       
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move(
                $this->projectDir . '/public/images/product',
                $newFilename
            );
        } catch (\Exception $e) {    
            $this->requestStack
                ->getSession()
                ->getFlashBag()
                ->add('danger', 'Erreur lors de l\'upload de l\'image.');

            throw $e;
        }

        $image = new Image();

        $image->setPath('images/product/' . $newFilename);
        $image->setIsPrincipal(true);

        return $image;
    }
}