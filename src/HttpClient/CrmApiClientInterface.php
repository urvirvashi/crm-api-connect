<?php
namespace App\HttpClient;

interface CrmApiClientInterface
{
    public function getLists(): array;
    public function createSubscriber(array $data): array;
    public function createEnquiry(string $subscriberId, string $message): void;
}
