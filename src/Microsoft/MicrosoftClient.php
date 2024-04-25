<?php

namespace Canzell\Microsoft;

use Canzell\Http\Clients\HttpClient;
use GuzzleHttp\Client as GuzzleClient;

class MicrosoftClient extends HttpClient
{

    private $config;

    public function __construct()
    {
        // Setup Configuration
        $this->config = config('microsoft-client');
        
        // Fetch auth token
        $client = new GuzzleClient();
        $res = $client->post("https://login.microsoftonline.com/{$this->config['tenant_id']}/oauth2/v2.0/token", [
            'form_params' => [
                'scope' => $this->config['scope'],
                'grant_type' => 'client_credentials',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
            ]
        ]);
        $token = json_decode($res->getBody())->access_token;

        // Configure Guzzle Client
        $client = new GuzzleClient([
            'base_uri' => "https://graph.microsoft.com/{$this->config['version']}/",
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);

        // Pass Guzzle client to parent
        parent::__construct($client);
    }

    public function mail(
        array|string $to,
        string $subject,
        string $body,
        array $attachments = [],
        ?string $from = null,
        array|string $cc = [],
        array|string $bcc = [],
        ?string $content_type = 'html',
        string $importance = 'normal',
        bool $save_to_sent = true,
        bool $request_read_receipt = false
    ) {
        // Resolve Parameters
        if ($from === null) $from = env('MAIL_FROM_ADDRESS');
        if (is_string($to)) $to = [$to];
        if (is_string($cc)) $cc = [$cc];
        if (is_string($bcc)) $bcc = [$bcc];
        
        // Convert any attachments to base64
        $convertAttachment = function ($path) {
            $handle = fopen($path, 'r');

            // Convert attachment into binary of base64 encoded attachment content
            $data = '';
            while ($blob = fread($handle, 1000)) $data .= $blob;
            $data = chunk_split(base64_encode($data));

            // Convert to Microsoft data structure
            $attachment = [
                "@odata.type" => "microsoft.graph.fileAttachment",
                'contentBytes' => $data,
                'name' => pathinfo($path, PATHINFO_BASENAME)
            ];

            fclose($handle);
            return $attachment;
        };
        $attachments = array_map($convertAttachment, $attachments);
        
        // Convert any recipients to Microsoft recipients
        $convertRecipient = function ($recipient) {
            return is_string($recipient)
                ?  [
                    'emailAddress' => [
                        'address' => $recipient
                    ]
                ]
                : [
                    'emailAddress' => $recipient
                ];
        };
        $to = array_map($convertRecipient, $to);
        $cc = array_map($convertRecipient, $cc);
        $bcc = array_map($convertRecipient, $bcc);

        // Send message
		$json = [
			'message' => [
                'importance' => $importance,
				'subject' => $subject,
				'body' => [
					'content' => $body,
					'contentType' => $content_type
				],
                'ccRecipients' => $cc,
				'bccRecipients' => $bcc,
				'toRecipients' => $to,
                'attachments' => $attachments,
				'isReadReceiptRequested' => $request_read_receipt
			],
			'saveToSentItems' => $save_to_sent
		];
        $this->post("users/{$from}/sendMail", compact('json'));
    }


}