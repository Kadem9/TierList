<?php

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Model\Logo;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'logos')]
class DoctrineLogo
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 500)]
    private string $url;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function toDomain(): Logo
    {
        return new Logo(
            $this->id,
            $this->name,
            $this->url
        );
    }

    public static function fromDomain(Logo $logo): self
    {
        $doctrineLogo = new self();
        $doctrineLogo->id = $logo->getId();
        $doctrineLogo->name = $logo->getName();
        $doctrineLogo->url = $logo->getUrl();

        return $doctrineLogo;
    }
}

