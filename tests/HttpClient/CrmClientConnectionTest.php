<?php
namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\HttpClient\CrmHttpClient;
use App\HttpClient\CrmApiClientInterface;

class CrmClienConnectionTest extends WebTestCase
{
    public function testBaseUrlIsFromEnv(): void
    {
        dump($_ENV['APP_ENV']);

        $client = static::createClient();
        $container = $client->getContainer();

        $crmClient = $container->get(\App\HttpClient\CrmApiClientInterface::class);

        $reflection = new \ReflectionClass($crmClient);
        $property = $reflection->getProperty('baseUrl');
        $property->setAccessible(true);

        $baseUrl = $property->getValue($crmClient);

        $this->assertSame('https://devtest-crm-api.test', $baseUrl);
    }

    public function testApiTokenIsFromEnv(): void
    {
        self::bootKernel();

        /** @var CrmHttpClient $client */
        $client = self::getContainer()->get(CrmHttpClient::class);

        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('apiToken');
        $property->setAccessible(true);

        $apiToken = $property->getValue($client);
        $this->assertSame('test123', $apiToken); 
    }
}
