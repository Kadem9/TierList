<?php

namespace App\Infrastructure\Controller\Web;

use App\Application\UseCase\ChangePasswordUseCase;
use App\Domain\Port\TierListRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly TierListRepositoryInterface $tierListRepository,
        private readonly ChangePasswordUseCase $changePasswordUseCase
    ) {
    }

    #[Route('/profile', name: 'profile_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var DoctrineUser $user */
        $user = $this->getUser();
        $tierList = $this->tierListRepository->findByUser($user->toDomain());

        $stats = [
            'totalLogosClassified' => $tierList ? count($tierList->getItems()) : 0,
            'memberSince' => $user->getId(),
        ];

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    #[Route('/profile/password', name: 'profile_change_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function changePassword(Request $request): Response
    {
        /** @var DoctrineUser $user */
        $user = $this->getUser();

        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('profile_index');
        }

        try {
            $this->changePasswordUseCase->execute($user, $currentPassword, $newPassword);
            $this->addFlash('success', 'Mot de passe modifié avec succès.');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('profile_index');
    }
}
