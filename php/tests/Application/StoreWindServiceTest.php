<?php

namespace Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use WindBox\Application\UseCases\Commands\StoreWindCommand;
use WindBox\Application\Services\StoreWindService;
use WindBox\Domain\Ports\WindPacketRepository;
use WindBox\Domain\Entities\WindPacket;
use Carbon\Carbon;

class StoreWindServiceTest extends TestCase
{
    private $repositoryMock;
    private StoreWindService $service;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(WindPacketRepository::class);
        $this->service = new StoreWindService($this->repositoryMock);
    }

    public function testExecuteStoresWindPacket()
    {
        $command = new StoreWindCommand(
            'LocationB',
            15.0,
            200.0,
            'B',
            Carbon::now()->addMonth()
        );

        $this->repositoryMock->expects($this->once())
                             ->method('save')
                             ->with($this->isInstanceOf(WindPacket::class));

        $packet = $this->service->execute($command);

        $this->assertInstanceOf(WindPacket::class, $packet);
        $this->assertEquals('LocationB', $packet->getLocation());
        $this->assertEquals(200.0, $packet->getVolumeM3());
        $this->assertNotNull($packet->getStoredAt());
        $this->assertNotNull($packet->getExpiresAt());
    }
}