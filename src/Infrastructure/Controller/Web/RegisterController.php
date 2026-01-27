<?php

namespace App\Infrastructure\Controller\Web;

use App\Application\UseCase\RegisterUserUseCase;
use App\Domain\Exception\UserAlreadyExistsException;
use App\Domain\Port\EmailInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUserUseCase,
        private readonly EmailInterface $emailProvider
    ) {
    }

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            if (empty($email) || empty($password)) {
                $this->addFlash('error', 'L\'email et le mot de passe sont requis.');
                return $this->render('security/register.html.twig');
            }

            try {
                $userId = uniqid('user_', true);
                $this->registerUserUseCase->execute($userId, $email, $password);

                $result = $this->emailProvider->sendEmail(
                    $email,
                    'Bienvenue sur TierList',
                    '<h1>Merci pour votre inscription !</h1><p>Bienvenue sur TierList.</p>'
                );
                if (!$result['success']) {
                    $this->addFlash('warning', 'Inscription réussie, mais l\'email de bienvenue n\'a pas pu être envoyé.');
                }

                $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('login');
            } catch (UserAlreadyExistsException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription.');
            }
        }

        return $this->render('security/register.html.twig');
    }
}

