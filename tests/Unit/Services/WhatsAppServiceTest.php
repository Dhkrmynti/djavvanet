<?php

namespace Tests\Unit\Services;

use App\Services\WhatsAppService;
use Tests\TestCase;
use ReflectionMethod;

class WhatsAppServiceTest extends TestCase
{
    /** @test */
    public function it_formats_phone_numbers_correctly()
    {
        $service = new WhatsAppService();

        // 08xx -> 628xx
        $this->assertEquals('6283117176099', $service->formatNumber('083117176099'));
        
        // 8xx -> 628xx
        $this->assertEquals('6283117176099', $service->formatNumber('83117176099'));
        
        // +628xx -> 628xx
        $this->assertEquals('6283117176099', $service->formatNumber('+6283117176099'));
        
        // 628xx -> 628xx
        $this->assertEquals('6283117176099', $service->formatNumber('6283117176099'));
        
        // Includes spaces or dashes
        $this->assertEquals('6283117176099', $service->formatNumber('0831-1717-6099'));
        $this->assertEquals('6283117176099', $service->formatNumber('0831 1717 6099'));
    }

    /** @test */
    public function it_sends_message_with_image_url_and_type()
    {
        $service = $this->getMockBuilder(WhatsAppService::class)
            ->onlyMethods(['callFonnte'])
            ->getMock();

        $service->expects($this->once())
            ->method('callFonnte')
            ->with('send', [
                'target' => '628123456789',
                'message' => 'Hello Image',
                'url' => 'https://example.com/image.jpg',
                'type' => 'image',
            ])
            ->willReturn(true);

        $result = $service->sendMessage('08123456789', 'Hello Image', 'https://example.com/image.jpg');
        $this->assertTrue($result);
    }

    /** @test */
    public function it_sends_message_without_type_when_no_url()
    {
        $service = $this->getMockBuilder(WhatsAppService::class)
            ->onlyMethods(['callFonnte'])
            ->getMock();

        $service->expects($this->once())
            ->method('callFonnte')
            ->with('send', [
                'target' => '628123456789',
                'message' => 'Hello Text Only',
            ])
            ->willReturn(true);

        $result = $service->sendMessage('08123456789', 'Hello Text Only');
        $this->assertTrue($result);
    }

    /** @test */
    public function it_builds_multipart_data_correctly()
    {
        $service = new WhatsAppService();
        $method = new ReflectionMethod(WhatsAppService::class, 'buildMultipartData');
        $method->setAccessible(true);

        $input = [
            'target' => '628123456789',
            'message' => 'Test',
            'url' => 'https://example.com/image.jpg',
            'type' => 'image',
        ];

        $result = $method->invoke($service, $input);

        $this->assertCount(4, $result);

        $this->assertEquals('target', $result[0]['name']);
        $this->assertEquals('628123456789', $result[0]['contents']);

        $this->assertEquals('message', $result[1]['name']);
        $this->assertEquals('Test', $result[1]['contents']);

        $this->assertEquals('url', $result[2]['name']);
        $this->assertEquals('https://example.com/image.jpg', $result[2]['contents']);

        $this->assertEquals('type', $result[3]['name']);
        $this->assertEquals('image', $result[3]['contents']);
    }

    /** @test */
    public function it_handles_url_with_query_parameters()
    {
        $service = $this->getMockBuilder(WhatsAppService::class)
            ->onlyMethods(['callFonnte'])
            ->getMock();

        $picUrl = 'https://fastly.picsum.photos/id/137/300/300.jpg?hmac=dpk2ovuA8rTuALV0NqRPIkay85Uqj2JmQ5Odw4fADVU';

        $service->expects($this->once())
            ->method('callFonnte')
            ->with('send', [
                'target' => '628123456789',
                'message' => 'Maintenance notice',
                'url' => $picUrl,
                'type' => 'image',
            ])
            ->willReturn(true);

        $result = $service->sendMessage('08123456789', 'Maintenance notice', $picUrl);
        $this->assertTrue($result);
    }
}
