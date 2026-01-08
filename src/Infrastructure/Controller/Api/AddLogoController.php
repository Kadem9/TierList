<?php

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\AddLogoUseCase;
use App\Domain\Exception\LogoAlreadyExistsException;
use App\Domain\Exception\LogoLimitReachedException;
use App\Infrastructure\Controller\Api\DTO\AddLogoRequest;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Logos')]
class AddLogoController extends AbstractController
{
    public function __construct(
        private readonly AddLogoUseCase $addLogoUseCase
    ) {
    }

    #[Route('/api/logos', name: 'api_add_logo', methods: ['POST'])]
    #[OA\Post(summary: 'Ajouter un nouveau logo')]
    #[OA\RequestBody(
        description: 'Données du logo à ajouter',
        required: true,
        content: new Model(type: AddLogoRequest::class)
    )]
    #[OA\Response(
        response: 201,
        description: 'Logo créé avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Logo ajouté avec succès.')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Limite de dix logos atteinte',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'La limite de 10 logos a été atteinte.')
            ]
        )
    )]
    #[OA\Response(
        response: 409,
        description: 'Le logo existe déjà',
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

