<?php

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Model\TierList;
use App\Domain\Port\LogoRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tier_lists')]
class DoctrineTierList
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\OneToOne(targetEntity: DoctrineUser::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, unique: true, onDelete: 'CASCADE')]
    private DoctrineUser $user;

    /**
     * @var Collection<int, DoctrineTierListItem>
     */
    #[ORM\OneToMany(mappedBy: 'tierList', targetEntity: DoctrineTierListItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUser(): DoctrineUser
    {
        return $this->user;
    }

    public function setUser(DoctrineUser $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Collection<int, DoctrineTierListItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(DoctrineTierListItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setTierList($this);
        }
    }

    public function removeItem(DoctrineTierListItem $item): void
    {
        $this->items->removeElement($item);
    }

    public function toDomain(LogoRepositoryInterface $logoRepository): TierList
    {
        $user = $this->user->toDomain();
        $tierList = new TierList($this->id, $user);

        foreach ($this->items as $item) {
            $domainItem = $item->toDomain($logoRepository);
            if ($domainItem !== null) {
                $tierList->addItem($domainItem);
            }
        }

        return $tierList;
    }

    public static function fromDomain(TierList $tierList, DoctrineUser $doctrineUser): self
    {
        $doctrineTierList = new self();
        $doctrineTierList->id = $tierList->getId();
        $doctrineTierList->user = $doctrineUser;

        foreach ($tierList->getItems() as $item) {
            $doctrineItem = DoctrineTierListItem::fromDomain($item, $doctrineTierList);
            $doctrineTierList->addItem($doctrineItem);
        }

        return $doctrineTierList;
    }
}

