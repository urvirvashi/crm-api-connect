<?php
namespace App\Tests\HttpClient;

use App\HttpClient\CrmHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CrmHttpClientTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private CrmHttpClient $crmClient;
    private string $baseUrl = 'https://crm.example.com';
    private string $token = 'token123';

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->crmClient = new CrmHttpClient($this->httpClient, $this->baseUrl, $this->token);
    }

    public function testGetListsReturnsArray(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getContent')->willReturn(json_encode([
            'lists' => [['id' => 1, 'name' => 'London']]
        ]));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $this->baseUrl . '/api/lists', $this->anything())
            ->willReturn($mockResponse);

        $result = $this->crmClient->getLists();
        $this->assertEquals([['id' => 1, 'name' => 'London']], $result);
    }

    public function testCreateSubscriberReturnsArray(): void
    {
        $subscriberData = ['emailAddress' => 'test@example.com'];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getContent')->willReturn(json_encode([
            'subscriber' => ['id' => 'abc123']
        ]));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', $this->baseUrl . '/api/subscriber', $this->callback(function ($options) use ($subscriberData) {
                return isset($options['json']) && $options['json'] === $subscriberData;
            }))
            ->willReturn($mockResponse);

        $result = $this->crmClient->createSubscriber($subscriberData);
        $this->assertSame(['id' => 'abc123'], $result);
    }

    public function testCreateEnquiryPostsCorrectly(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getContent')->willReturn(json_encode([]));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->baseUrl . '/api/subscriber/sub123/enquiry',
                $this->callback(fn($options) => isset($options['json']['message']) && $options['json']['message'] === 'Test message')
            )
            ->willReturn($mockResponse);

        $this->crmClient->createEnquiry('sub123', 'Test message');
        $this->assertTrue(true); // Passes if no exception
    }
}
