<?php

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Model\Logo;
use App\Domain\Model\TierCategory;
use App\Domain\Model\TierListItem;
use App\Domain\Port\LogoRepositoryInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tier_list_items')]
class DoctrineTierListItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DoctrineTierList::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DoctrineTierList $tierList;

    #[ORM\Column(type: 'string', length: 36)]
    private string $logoId;

    #[ORM\Column(type: 'string', enumType: TierCategory::class)]
    private TierCategory $tierCategory;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTierList(): DoctrineTierList
    {
        return $this->tierList;
    }

    public function setTierList(DoctrineTierList $tierList): void
    {
        $this->tierList = $tierList;
    }

    public function getLogoId(): string
    {
        return $this->logoId;
    }

    public function setLogoId(string $logoId): void
    {
        $this->logoId = $logoId;
    }

    public function getTierCategory(): TierCategory
    {
        return $this->tierCategory;
    }

    public function setTierCategory(TierCategory $tierCategory): void
    {
        $this->tierCategory = $tierCategory;
    }

    public function toDomain(LogoRepositoryInterface $logoRepository): ?TierListItem
    {
        $logo = $logoRepository->findByInternalId($this->logoId);
        if ($logo === null) {
            return null;
        }

        return new TierListItem($logo, $this->tierCategory);
    }

    public static function fromDomain(TierListItem $item, DoctrineTierList $tierList): self
    {
        $doctrineItem = new self();
        $doctrineItem->tierList = $tierList;
        $doctrineItem->logoId = $item->getLogo()->getId();
        $doctrineItem->tierCategory = $item->getTierCategory();

        return $doctrineItem;
    }
}

