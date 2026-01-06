<?php

namespace App\Domain\Model;

class TierListItem
{
    private Logo $logo;
    private TierCategory $tierCategory;

    public function __construct(Logo $logo, TierCategory $tierCategory)
    {
        $this->logo = $logo;
        $this->tierCategory = $tierCategory;
    }

    public function getLogo(): Logo
    {
        return $this->logo;
    }

    public function getTierCategory(): TierCategory
    {
        return $this->tierCategory;
    }

    public function setTierCategory(TierCategory $tierCategory): void
    {
        $this->tierCategory = $tierCategory;
    }
}

