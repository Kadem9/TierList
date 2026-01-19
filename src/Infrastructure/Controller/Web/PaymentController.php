<?php

namespace App\Infrastructure\Controller\Web;

use App\Domain\Port\PaymentProviderInterface;
use App\Domain\Port\UserRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentProviderInterface $paymentProvider,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    #[Route('/payment/subscribe', name: 'payment_subscribe', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function subscribe(): Response
    {
        /** @var DoctrineUser $doctrineUser */
        $doctrineUser = $this->getUser();

        if ($doctrineUser->isPremium()) {
            $this->addFlash('info', 'Vous êtes déjà Premium.');
            return $this->redirectToRoute('tier_list_index');
        }

        try {
            $successUrl = $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $cancelUrl = $this->generateUrl('tier_list_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $checkoutUrl = $this->paymentProvider->createCheckoutSession(
                $doctrineUser->getId(),
                $successUrl,
                $cancelUrl
            );

            return new Response(
                '<!DOCTYPE html><html><head><meta charset="UTF-8">
                <title>Redirection vers Stripe...</title>
                </head><body>
                <p>Redirection vers le paiement...</p>
                <script>window.location.href = "' . addslashes($checkoutUrl) . '";</script>
                <noscript><a href="' . htmlspecialchars($checkoutUrl) . '">Cliquez ici</a></noscript>
                </body></html>',
                200,
                ['Content-Type' => 'text/html']
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur Stripe : ' . $e->getMessage());
            return $this->redirectToRoute('tier_list_index');
        }
    }

    #[Route('/payment/success', name: 'payment_success', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function success(): Response
    {
        /** @var DoctrineUser $doctrineUser */
        $doctrineUser = $this->getUser();
        $user = $doctrineUser->toDomain();

        $user->setPremium(true);
        $this->userRepository->update($user);

        $this->addFlash('success', 'Paiement réussi ! Vous êtes maintenant Premium.');

        return $this->redirectToRoute('tier_list_index');
    }
}
