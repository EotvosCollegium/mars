<?php

namespace App\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class CaesarProxyTransport extends AbstractTransport
{
    private string $proxyUrl;
    private string $secretKey;

    public function __construct()
    {
        parent::__construct();
        $this->proxyUrl = config('mail.mail_proxy_url');
        $this->secretKey = base64_decode(config('mail.mail_proxy_secret'));
    }

    protected function doSend(SentMessage $sentMessage): void
    {
        $email = MessageConverter::toEmail($sentMessage->getOriginalMessage());

        $headers = $email->getPreparedHeaders();
        $headers->remove('To');
        $headers->remove('Subject');

        $bcc = implode("\r\n", array_map(fn (Address $addr) => 'Bcc: ' . $addr->toString(), $email->getBcc()));
        if ($bcc) {
            $bcc .= "\r\n";
        }

        $data = json_encode([
            'to' => implode(',', array_map(fn (Address $addr) => $addr->toString(), $email->getTo())),
            'subject' => $email->getSubject(),
            'message' => $email->getBody()->bodyToString(),
            'headers' => $headers->toString() . $bcc . $email->getBody()->getPreparedHeaders()->toString(),
        ]);

        $signature = hash_hmac('sha256', $data, $this->secretKey);

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n" .
                             "X-Signature: $signature\r\n",
                'method'  => 'POST',
                'content' => $data,
            ],
        ];

        $context  = stream_context_create($options);
        $success = file_get_contents($this->proxyUrl, false, $context);

        if ($success === false) {
            throw new \Exception('Failed to send email via Caesar Proxy Transport: ' . print_r(error_get_last(), true));
        }
    }

    public function __toString(): string
    {
        return 'caesar-proxy';
    }
}
