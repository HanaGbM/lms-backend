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
                Log::error('Failed to send email via Mailjet template', [
                    'to' => $to,
                    'template_id' => $templateId,
                    'response' => $response->getData(),
                    'status_code' => $response->getStatus(),
                    'body' => $response->getBody()
                ]);
                return false;
            }

            $responseData = $response->getData();
            Log::info('Email sent successfully via Mailjet template', [
                'to' => $to,
                'template_id' => $templateId,
                'message_id' => $responseData['Messages'][0]['To'][0]['MessageID'] ?? 'unknown',
                'message_uuid' => $responseData['Messages'][0]['To'][0]['MessageUUID'] ?? 'unknown',
                'full_response' => $responseData
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Exception while sending email via Mailjet', [
                'to' => $to,
                'template_id' => $templateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
