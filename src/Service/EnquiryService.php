<?php
namespace App\Service;

use App\HttpClient\CrmApiClientInterface;
use App\Exception\ValidationException;

class EnquiryService
{
    public function __construct(private CrmApiClientInterface $client) {}

    public function send(string $subscriberId, string $message): void
    {
        if (trim($message) === '') {
            throw new ValidationException('Message cannot be empty.');
        }

        $this->client->createEnquiry($subscriberId, $message);
    }
}
