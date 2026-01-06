<?php

namespace App\Infrastructure\Controller\Api\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AddLogoRequest',
    description: 'Données pour ajouter un nouveau logo',
    required: ['name']
)]
class AddLogoRequest
{
    #[OA\Property(property: 'name', type: 'string', description: 'Nom de l\'entreprise (ex: Adidas, Google, Nike)', example: 'Adidas')]
    public string $name;
}

