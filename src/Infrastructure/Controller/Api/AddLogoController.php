<?php

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\AddLogoUseCase;
use App\Domain\Exception\LogoAlreadyExistsException;
use App\Domain\Exception\LogoLimitReachedException;
use App\Domain\Port\LogoProviderInterface;
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
        private readonly AddLogoUseCase $addLogoUseCase,
        private readonly LogoProviderInterface $logoProvider
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

        if (!isset($data['name']) || empty($data['name'])) {
            return new JsonResponse(
                ['error' => 'Le champ name est requis.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $name = trim($data['name']);

            // Utiliser le nom comme domaine pour générer l'URL du logo
            // Le LogoProvider vérifie automatiquement la disponibilité via l'API logo.dev
            $domain = $this->normalizeDomain($name);

            // Générer l'ID automatiquement (UUID v4)
            $id = $this->generateUuid();

            // Générer l'URL du logo automatiquement via LogoProviderInterface
            // L'API logo.dev vérifie la disponibilité et retourne l'URL appropriée
            $url = $this->logoProvider->getLogoUrl($domain);

            // Passer l'objet complet au use case
            $this->addLogoUseCase->execute(
                $id,
                $name,
                $url
            );

            return new JsonResponse(
                [
                    'message' => 'Logo ajouté avec succès.',
                    'data' => [
                        'id' => $id,
                        'name' => $name,
                        'url' => $url,
                    ]
                ],
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

    // Cette fonction normalise le nom pr l'utiliser comme domaine, par exemple adidas : adidas.com
    private function normalizeDomain(string $name): string
    {
        // Nettoyé le nom (minuscules, supprimer espaces)
        $normalized = strtolower(trim($name));

        // Si c'est déjà un domaine (contient un point), le retourner tel quel
        if (strpos($normalized, '.') !== false) {
            return $normalized;
        }

        // Sinon, ajouter .com par défaut
        return $normalized . '.com';
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }
}

