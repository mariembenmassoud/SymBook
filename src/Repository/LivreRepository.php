<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    /**
     * Recherche de livres par titre, auteur et catégorie.
     *
     * @param string|null $search Texte de recherche (titre ou auteur)
     * @param int|null $categorieId ID de la catégorie
     * @return Livre[]
     */
    public function findBySearch(?string $search, ?int $categorieId): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.categorie', 'c')
            ->addSelect('c');

        if ($search) {
            $qb->andWhere('l.title LIKE :search OR l.auteur LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($categorieId) {
            $qb->andWhere('c.id = :cat')
               ->setParameter('cat', $categorieId);
        }

        return $qb->getQuery()->getResult();
    }
}
