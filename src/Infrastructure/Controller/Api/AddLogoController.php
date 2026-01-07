<?php

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\AddLogoUseCase;
use App\Domain\Exception\LogoAlreadyExistsException;
use App\Domain\Exception\LogoLimitReachedException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AddLogoController extends AbstractController
{
    public function __construct(
        private readonly AddLogoUseCase $addLogoUseCase
    ) {
    }

    #[Route('/api/logos', name: 'api_logos_add', methods: ['POST'])]
    #[OA\Tag(name: 'Logos')]
    #[OA\RequestBody(
        description: 'Données du logo à ajouter',
        required: true,
        content: new OA\JsonContent(
            required: ['id', 'name', 'url'],
            properties: [
                new OA\Property(property: 'id', type: 'string', description: 'Identifiant unique du logo (UUID)', example: '123e4567-e89b-12d3-a456-426614174000'),
                new OA\Property(property: 'name', type: 'string', description: 'Nom du logo (domaine de l\'entreprise)', example: 'example.com'),
                new OA\Property(property: 'url', type: 'string', description: 'URL du logo', example: 'https://example.com/logo.png'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Logo ajouté avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Logo ajouté avec succès.')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Erreur de validation ou limite de 10 logos atteinte',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'La limite de 10 logos a été atteinte.')
            ]
        )
    )]
    #[OA\Response(
        response: 409,
        description: 'Logo déjà existant',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Un logo avec l\'ID "123e4567-e89b-12d3-a456-426614174000" existe déjà.')
            ]
        )
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['id']) || !isset($data['name']) || !isset($data['url'])) {
            return new JsonResponse(
                ['error' => 'Les champs id, name et url sont requis.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->addLogoUseCase->execute(
                $data['id'],
                $data['name'],
                $data['url']
            );

            return new JsonResponse(
                ['message' => 'Logo ajouté avec succès.'],
                Response::HTTP_CREATED
            );
        } catch (LogoLimitReachedException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (LogoAlreadyExistsException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        }
    }
}

