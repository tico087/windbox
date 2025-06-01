<?php

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use WindBox\Domain\Entities\WindPacket;
use WindBox\Domain\Ports\WindPacketRepository;
use WindBox\Domain\Services\WindStorageManager;
use WindBox\Domain\Exceptions\InsufficientWindException;
use Carbon\Carbon;
use SplQueue;

class WindStorageManagerTest extends TestCase
{
    private $repositoryMock;
    private WindStorageManager $manager;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(WindPacketRepository::class);
        $this->manager = new WindStorageManager($this->repositoryMock);
    }

    public function testGetAvailableVolume()
    {
        $this->repositoryMock->method('getAvailableVolume')
                             ->with('LocationA')
                             ->willReturn(100.0);

        $volume = $this->manager->getAvailableVolume('LocationA');
        $this->assertEquals(100.0, $volume);
    }

    public function testAllocateWindSuccessfully()
    {
        $packet1 = new WindPacket(1, 'LocationA', 20, 50.0, 'A', Carbon::now());
        $packet2 = new WindPacket(2, 'LocationA', 25, 70.0, 'B', Carbon::now());

        $queue = new SplQueue();
        $queue->enqueue($packet1);
        $queue->enqueue($packet2);

        $this->repositoryMock->method('getAvailableVolume')
                             ->with('LocationA')
                             ->willReturn(120.0); // 50 + 70

        $this->repositoryMock->method('findAvailableByLocation')
                             ->with('LocationA', 100)
                             ->willReturn($queue);

        // Expect remove for packet1 and update for packet2
        $this->repositoryMock->expects($this->once())
                             ->method('remove')
                             ->with($this->callback(function($p) use ($packet1) {
                                 return $p->getId() === $packet1->getId();
                             }));

        $this->repositoryMock->expects($this->once())
                             ->method('save')
                             ->with($this->callback(function($p) use ($packet2) {
                                 return $p->getId() === $packet2->getId() && $p->getVolumeM3() === 40.0; // 70 - 30
                             }));


        $result = $this->manager->allocateWind('LocationA', 80.0); // Allocate 80 (50 from packet1, 30 from packet2)
        $this->assertTrue($result);
        $this->assertEquals(40.0, $packet2->getVolumeM3()); // Verify volume was updated on packet2
    }

    public function testAllocateWindThrowsExceptionOnInsufficientWind()
    {
        $this->repositoryMock->method('getAvailableVolume')
                             ->with('LocationA')
                             ->willReturn(50.0);

        $this->expectException(InsufficientWindException::class);
        $this->expectExceptionMessage("Not enough wind available at location: LocationA");

        $this->manager->allocateWind('LocationA', 100.0);
    }
}