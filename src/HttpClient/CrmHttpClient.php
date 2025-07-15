<?php
namespace App\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use App\Exception\CrmApiException;

class CrmHttpClient implements CrmApiClientInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private string $baseUrl,
        private string $apiToken
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Makes a request to the CRM API and returns the decoded JSON response.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path API endpoint path
     * @param array $options Additional options for the request
     * @return array Decoded JSON response
     * @throws CrmApiException If the request fails or the response is invalid
     */
    private function request(string $method, string $path, array $options = []): array
    {
        $url = $this->baseUrl . $path;

        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
            ]
        );

        try {
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                $content = $response->getContent(false);
                throw new CrmApiException(sprintf(
                    'CRM API request failed with status code: %d. Response: %s',
                    $statusCode,
                    $content
                ));
            }

            $content = $response->getContent();
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($decoded)) {
                throw new CrmApiException('Unexpected CRM API response format.');
            }

            return $decoded;
        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            throw new CrmApiException('Network error during CRM API request: ' . $e->getMessage(), 0, $e);
        } catch (\JsonException $e) {
            throw new CrmApiException('Failed to decode CRM response as JSON: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getLists(): array
    {
        return $this->request('GET', '/api/lists')['lists'] ?? [];
    }

    public function createSubscriber(array $data): array
    {
        return $this->request('POST', '/api/subscriber', ['json' => $data])['subscriber'] ?? [];
    }

    public function createEnquiry(string $subscriberId, string $message): void
    {
        $this->request('POST', "/api/subscriber/{$subscriberId}/enquiry", ['json' => ['message' => $message]]);
    }
}
