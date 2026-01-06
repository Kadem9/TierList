<?php

namespace App\Domain\Model;

class TierList
{
    private string $id;
    private User $user;
    /** @var TierListItem[] */
    private array $items;

    public function __construct(string $id, User $user)
    {
        $this->id = $id;
        $this->user = $user;
        $this->items = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return TierListItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(TierListItem $item): void
    {
        $this->items[] = $item;
    }

    public function removeItem(TierListItem $item): void
    {
        $this->items = array_filter(
            $this->items,
            fn(TierListItem $i) => $i !== $item
        );
    }
}

