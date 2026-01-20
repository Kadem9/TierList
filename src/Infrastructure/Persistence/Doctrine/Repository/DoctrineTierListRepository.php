<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Model\TierList;
use App\Domain\Model\User;
use App\Domain\Port\LogoRepositoryInterface;
use App\Domain\Port\TierListRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineTierList;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineTierListItem;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineTierListRepository implements TierListRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LogoRepositoryInterface $logoRepository
    ) {
    }

    public function save(TierList $tierList): void
    {
        // Récupérer le DoctrineUser
        $doctrineUser = $this->entityManager
            ->getRepository(DoctrineUser::class)
            ->find($tierList->getUser()->getId());

        if ($doctrineUser === null) {
            throw new \RuntimeException('User not found');
        }

        // Vérifier si une tier list existe déjà pour cet utilisateur
        $existingDoctrineTierList = $this->entityManager
            ->getRepository(DoctrineTierList::class)
            ->findOneBy(['user' => $doctrineUser]);

        if ($existingDoctrineTierList !== null) {
            // Supprimer ts les items existants (orphanRemoval s'occupe de la suppression)
            foreach ($existingDoctrineTierList->getItems()->toArray() as $item) {
                $existingDoctrineTierList->removeItem($item);
            }

            // Mettre à jour la tier list existante
            $existingDoctrineTierList->setId($tierList->getId());
            $doctrineTierList = $existingDoctrineTierList;

            // Ajouter les nouveaux items
            foreach ($tierList->getItems() as $item) {
                $doctrineItem = DoctrineTierListItem::fromDomain(
                    $item,
                    $doctrineTierList
                );
                $doctrineTierList->addItem($doctrineItem);
            }
        } else {
            // Créer une nouvelle tier list (fromDomain() crée déjà les items)
            $doctrineTierList = DoctrineTierList::fromDomain($tierList, $doctrineUser);
            $this->entityManager->persist($doctrineTierList);
        }

        $this->entityManager->flush();
    }

    public function findByUser(User $user): ?TierList
    {
        $doctrineUser = $this->entityManager
            ->getRepository(DoctrineUser::class)
            ->find($user->getId());

        if ($doctrineUser === null) {
            return null;
        }

        $doctrineTierList = $this->entityManager
            ->getRepository(DoctrineTierList::class)
            ->findOneBy(['user' => $doctrineUser]);

        if ($doctrineTierList === null) {
            return null;
        }

        return $doctrineTierList->toDomain($this->logoRepository);
    }

    public function getGlobalStatistics(): array
    {
        $query = $this->entityManager->createQuery(
            'SELECT i.logoId, i.tierCategory, COUNT(i.id) as count
             FROM App\Infrastructure\Persistence\Doctrine\Entity\DoctrineTierListItem i
             GROUP BY i.logoId, i.tierCategory'
        );

        $results = $query->getResult();

        $stats = [];
        $totalVotesPerLogo = [];

        foreach ($results as $row) {
            $logoId = $row['logoId'];
            $category = $row['tierCategory']->value;
            $count = (int) $row['count'];

            if (!isset($stats[$logoId])) {
                $stats[$logoId] = ['S' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
                $totalVotesPerLogo[$logoId] = 0;
            }

            $stats[$logoId][$category] = $count;
            $totalVotesPerLogo[$logoId] += $count;
        }

        $formattedStats = [];
        foreach ($stats as $logoId => $categories) {
            $total = $totalVotesPerLogo[$logoId];
            $logo = $this->logoRepository->findByInternalId($logoId);

            if ($logo === null) {
                continue;
            }

            $percentages = [];
            $dominantCategory = 'S';
            $maxPercent = 0;

            foreach ($categories as $cat => $count) {
                $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                $percentages[$cat] = $percent;

                if ($percent > $maxPercent) {
                    $maxPercent = $percent;
                    $dominantCategory = $cat;
                }
            }

            $formattedStats[] = [
                'logo' => $logo,
                'totalVotes' => $total,
                'percentages' => $percentages,
                'dominantCategory' => $dominantCategory,
                'dominantPercent' => $maxPercent,
            ];
        }

        usort($formattedStats, fn($a, $b) => $b['totalVotes'] <=> $a['totalVotes']);

        return $formattedStats;
    }
}

