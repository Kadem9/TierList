<?php

namespace App\Infrastructure\Controller\Api\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AddLogoRequest',
    description: 'Données pour ajouter un nouveau logo',
    required: ['id', 'name', 'url']
)]
class AddLogoRequest
{
    #[OA\Property(property: 'id', type: 'string', description: 'Identifiant unique du logo (UUID)', example: '123e4567-e89b-12d3-a456-426614174000')]
    public string $id;

    #[OA\Property(property: 'name', type: 'string', description: 'Nom du logo (domaine de l\'entreprise)', example: 'example.com')]
    public string $name;

    #[OA\Property(property: 'url', type: 'string', description: 'URL du logo', example: 'https://example.com/logo.png')]
    public string $url;
}

