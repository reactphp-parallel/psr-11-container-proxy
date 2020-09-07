<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Psr11ContainerProxy;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ReactParallel\Psr11ContainerProxy\ThreadContainerProxy;
use stdClass;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

final class ThreadContainerProxyTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function logger(): void
    {
        $std    = new stdClass();
        $logger = new Logger('monolog');
        $local  = $this->prophesize(ContainerInterface::class);
        $local->has('fake')->shouldBeCalled()->willReturn(true);
        $local->get('fake')->shouldBeCalled()->willReturn($std);
        $local->has(LoggerInterface::class)->shouldNotBeCalled();
        $local->get(LoggerInterface::class)->shouldNotBeCalled();
        $remote = $this->prophesize(ContainerInterface::class);
        $remote->has('fake')->shouldNotBeCalled();
        $remote->get('fake')->shouldNotBeCalled();
        $remote->get(LoggerInterface::class)->shouldBeCalled()->willReturn($logger);

        $proxy = new ThreadContainerProxy($local->reveal(), $remote->reveal());

        self::assertTrue($proxy->has('fake'));
        self::assertTrue($proxy->has(LoggerInterface::class));
        self::assertSame($std, $proxy->get('fake'));
        self::assertSame($logger, $proxy->get(LoggerInterface::class));
    }
}
