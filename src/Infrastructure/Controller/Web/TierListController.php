<?php

namespace App\Infrastructure\Controller\Web;

use App\Application\UseCase\ClassifyLogoUseCase;
use App\Application\UseCase\ExportTierListPdfUseCase;
use App\Domain\Model\TierCategory;
use App\Domain\Port\LogoProviderInterface;
use App\Domain\Port\LogoRepositoryInterface;
use App\Domain\Port\PdfStorageInterface;
use App\Domain\Port\TierListRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TierListController extends AbstractController
{
    public function __construct(
        private readonly LogoRepositoryInterface $logoRepository,
        private readonly TierListRepositoryInterface $tierListRepository,
        private readonly ClassifyLogoUseCase $classifyLogoUseCase,
        private readonly ExportTierListPdfUseCase $exportTierListPdfUseCase,
        private readonly LogoProviderInterface $logoProvider,
        private readonly PdfStorageInterface $pdfStorage
    ) {
    }

    #[Route('/tier-list', name: 'tier_list_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var DoctrineUser $doctrineUser */
        $doctrineUser = $this->getUser();
        $user = $doctrineUser->toDomain();

        $allLogos = $this->logoRepository->findAll();
        $tierList = $this->tierListRepository->findByUser($user);

        $logosByCategory = [
            'S' => [],
            'A' => [],
            'B' => [],
            'C' => [],
            'D' => [],
        ];

        $classifiedLogoIds = [];
        if ($tierList !== null) {
            foreach ($tierList->getItems() as $item) {
                $category = $item->getTierCategory()->value;
                $logosByCategory[$category][] = $item->getLogo();
                $classifiedLogoIds[] = $item->getLogo()->getId();
            }
        }

        $unclassifiedLogos = array_filter(
            $allLogos,
            fn($logo) => !in_array($logo->getId(), $classifiedLogoIds)
        );

        return $this->render('tier_list/index.html.twig', [
            'logosByCategory' => $logosByCategory,
            'unclassifiedLogos' => $unclassifiedLogos,
            'logoProvider' => $this->logoProvider,
            'isPremium' => $doctrineUser->isPremium(),
        ]);
    }

    #[Route('/tier-list/move', name: 'tier_list_move', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function move(Request $request): Response
    {
        /** @var DoctrineUser $doctrineUser */
        $doctrineUser = $this->getUser();
        $user = $doctrineUser->toDomain();

        $logoId = $request->request->get('logoId');
        $category = $request->request->get('category');
        $isAjax = $request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json';

        if ($logoId === null || $category === null) {
            if ($isAjax) {
                return $this->json(['error' => 'Paramètres requis'], 400);
            }
            $this->addFlash('error', 'Les paramètres logoId et category sont requis.');
            return $this->redirectToRoute('tier_list_index');
        }

        try {
            $tierCategory = TierCategory::from($category);
            $this->classifyLogoUseCase->execute($user, $logoId, $tierCategory);

            if ($isAjax) {
                return $this->json(['success' => true]);
            }
            $this->addFlash('success', 'Logo déplacé avec succès.');
        } catch (\ValueError $e) {
            if ($isAjax) {
                return $this->json(['error' => 'Catégorie invalide'], 400);
            }
            $this->addFlash('error', 'Catégorie invalide.');
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->json(['error' => $e->getMessage()], 400);
            }
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('tier_list_index');
    }

    #[Route('/tier-list/export', name: 'tier_list_export', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function export(): Response
    {
        /** @var DoctrineUser $doctrineUser */
        $doctrineUser = $this->getUser();
        $user = $doctrineUser->toDomain();

        try {
            $filename = $this->exportTierListPdfUseCase->execute($user);
            $pdfContent = $this->pdfStorage->getFileContent($filename);

            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'ma-tier-list.pdf'
            ));

            return $response;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'export : ' . $e->getMessage());
            return $this->redirectToRoute('tier_list_index');
        }
    }
}

