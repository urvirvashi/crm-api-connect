<?php
namespace App\Tests\Service;

use App\HttpClient\CrmApiClientInterface;
use App\Service\EnquiryService;
use App\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class EnquiryServiceTest extends TestCase
{
    public function testSendThrowsExceptionOnEmptyMessage(): void
    {
        $client = $this->createMock(CrmApiClientInterface::class);
        $service = new EnquiryService($client);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Message cannot be empty.');

        $service->send('sub123', '   ');
    }

    public function testSendCallsCrmClient(): void
    {
        $client = $this->createMock(CrmApiClientInterface::class);
        $client
            ->expects($this->once())
            ->method('createEnquiry')
            ->with('sub123', 'test');

        $service = new EnquiryService($client);
        $service->send('sub123', 'test');
    }
}
