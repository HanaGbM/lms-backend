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
        
        // Validate Mailjet credentials
        if (empty($apiKey) || empty($apiSecret)) {
            Log::error('Mailjet credentials are missing in EmailService');
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
                        'Name' => env('MAIL_FROM_NAME', 'LMS'),
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
                    'status_code' => $response->getStatus()
                ]);
                return false;
            }

            Log::info('Email sent successfully via Mailjet template', [
                'to' => $to,
                'template_id' => $templateId
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Exception sending email via Mailjet', [
                'to' => $to,
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
