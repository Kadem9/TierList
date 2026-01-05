<?php

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\AddLogoUseCase;
use App\Domain\Exception\LogoAlreadyExistsException;
use App\Domain\Exception\LogoLimitReachedException;
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

