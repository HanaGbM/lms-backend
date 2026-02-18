<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Mailjet\Client;
use Mailjet\Resources;

class EmailService
{
    public function sendEmail($to, $subject, $templateId, $variables = [])
    {
        $apiKey = env('MAILJET_APIKEY');
        $apiSecret = env('MAILJET_APISECRET');

        if (empty($apiKey) || empty($apiSecret)) {
            Log::error('Mailjet credentials missing for template email', ['to' => $to]);
            return false;
        }

        $mj = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
        $mj->setConnectionTimeout((int) env('MAILJET_CONNECT_TIMEOUT', 10));
        $mj->setTimeout((int) env('MAILJET_TIMEOUT', 30));

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                        'Name' => env('MAIL_FROM_NAME', env('APP_NAME', 'LMS')),
                    ],
                    'To' => [
                        [
                            'Email' => $to,
                            'Name' => $variables['name'] ?? 'User',
                        ],
                    ],
                    'TemplateID' => $templateId,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => $variables,
                ],
            ],
        ];

        try {
            $response = $mj->post(Resources::$Email, ['body' => $body]);

            if (! $response->success()) {
                $responseData = $response->getData();
                Log::error('Failed to send email via Mailjet template', [
                    'to' => $to,
                    'template_id' => $templateId,
                    'response' => $responseData,
                    'status_code' => $response->getStatus(),
                ]);
                return false;
            }

            Log::info('Email sent successfully via Mailjet template', [
                'to' => $to,
                'template_id' => $templateId,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Exception sending email via Mailjet', [
                'to' => $to,
                'template_id' => $templateId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send an email with custom HTML and plain text (e.g. welcome, Blade-rendered).
     */
    public function sendHtmlEmail(string $to, string $subject, string $htmlContent, string $textContent, ?string $toName = null): bool
    {
        $apiKey = env('MAILJET_APIKEY');
        $apiSecret = env('MAILJET_APISECRET');

        if (empty($apiKey) || empty($apiSecret)) {
            Log::error('Mailjet credentials missing for HTML email', ['to' => $to]);
            return false;
        }

        $mj = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
        $mj->setConnectionTimeout((int) env('MAILJET_CONNECT_TIMEOUT', 10));
        $mj->setTimeout((int) env('MAILJET_TIMEOUT', 30));

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                        'Name' => env('MAIL_FROM_NAME', env('APP_NAME', 'LMS')),
                    ],
                    'To' => [
                        [
                            'Email' => $to,
                            'Name' => $toName ?? 'User',
                        ],
                    ],
                    'Subject' => $subject,
                    'TextPart' => $textContent,
                    'HTMLPart' => $htmlContent,
                ],
            ],
        ];

        try {
            $response = $mj->post(Resources::$Email, ['body' => $body]);

            if (! $response->success()) {
                $responseData = $response->getData();
                Log::error('Failed to send HTML email via Mailjet', [
                    'to' => $to,
                    'subject' => $subject,
                    'response' => $responseData,
                    'status_code' => $response->getStatus(),
                ]);
                return false;
            }

            Log::info('HTML email sent successfully via Mailjet', [
                'to' => $to,
                'subject' => $subject,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Exception sending HTML email via Mailjet', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
