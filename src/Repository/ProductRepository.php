<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findPrePublic() {
        return $this->createQueryBuilder('p')
            ->andWhere('p.public_date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPostPublic() {
        return $this->createQueryBuilder('p')
            ->andWhere('p.public_date <= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByNameSubstring($str) {
        $arr = $this->findAll();
        $returnArray = array();
        forEach($arr as $prod) {
            $name = $prod->getName();
            if (str_contains(strtoupper($name), strtoupper($str))) { $returnArray[] = $prod; }
        }

        return $returnArray;
        // return $this->createQueryBuilder('p')
        //     ->andWhere('strpos(p.name, :str) !== false')
        //     //str_contains(p.name, :str)')
        //     ->setParameter('str', $str)
        //     ->orderBy('p.name', 'DESC')
        //     ->getQuery()
        //     ->getResult()
        // ;
    }



    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
