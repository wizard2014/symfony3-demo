<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Genus;
use Doctrine\ORM\EntityRepository;

class GenusNoteRepository extends EntityRepository
{
    public function finaAllRecentNotesForGenus(Genus $genus)
    {
        return $this->createQueryBuilder('genus_note')
                    ->andWhere('genus_note.genus = :genus')
                    ->andWhere('genus_note.createdAt > :recentDate')
                    ->setParameter('genus', $genus)
                    ->setParameter('recentDate', new \DateTime('-3 months'))
                    ->getQuery()
                    ->execute();
    }
}
