<?php

namespace App\Infrastructure\Service\Email;

use App\Domain\Port\EmailInterface;
use Mailjet\Client;
use Mailjet\Resources;

class MailjetAdapter implements EmailInterface
{
    private Client $mailjet;

    public function __construct(private readonly string $keyPublic, private readonly string $keyPrivate, private readonly string $expeditorEmail, private readonly string $expeditorName)
    {
        $this->mailjet = new Client($keyPublic, $keyPrivate, true, ['version' => 'v3.1']);
    }

    public function sendEmail(string $to, string $subject, string $body): array
    {
        $message = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->expeditorEmail,
                        'Name' => $this->expeditorName
                    ],
                    'To' => [
                        ['Email' => $to, 'Name' => '']
                    ],
                    'Subject' => $subject,
                    'HTMLPart' => $body,
                ]
            ]
        ];

        $response = $this->mailjet->post(Resources::$Email, ['body' => $message]);
        $success = $response->getStatus() >= 200 && $response->getStatus() < 300;

        return [
            'success' => $success,
            'status' => $response->getStatus(),
            'error' => $success ? null : ($response->getReasonPhrase() ?? "Erreur lors de l'envoi de l'email.")
        ];
    }
}
