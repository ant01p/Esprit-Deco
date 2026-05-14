<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
     
    //index
    public function findWithFilters(int $categoryId, ?string $priceFilter): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->leftJoin('p.images', 'i', 'WITH', 'i.isPrincipal = true')
            ->addSelect('i');

        if ($categoryId > 0) {
            $qb->andWhere('c.id = :categoryId')->setParameter('categoryId', $categoryId);
        }

        match ($priceFilter) {
            'lt100'      => $qb->andWhere('p.price < 100'),
            '100to500'   => $qb->andWhere('p.price >= 100 AND p.price <= 500'),
            '500to1000'  => $qb->andWhere('p.price > 500 AND p.price <= 1000'),
            'gt1000'     => $qb->andWhere('p.price > 1000'),
            default      => null,
        };

        return $qb->getQuery()->getResult();
    }

    //show
    public function findOneWithCategoryAndImages(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->leftJoin('p.images', 'i')
            ->addSelect('i')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //admin-products
    public function findAllWithCategories(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult()
        ;
    }
    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
