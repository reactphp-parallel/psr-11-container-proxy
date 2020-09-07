<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Psr11ContainerProxy;

use React\EventLoop\Factory as EventLoopFactory;
use ReactParallel\Factory;
use ReactParallel\ObjectProxy\Proxy;
use ReactParallel\Psr11ContainerProxy\ContainerProxy;
use ReactParallel\Psr11ContainerProxy\ThreadContainerProxy;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use Yuloh\Container\Container;

final class ContainerProxyTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function create(): void
    {
        $loop           = EventLoopFactory::create();
        $factory        = new Factory($loop);
        $proxy          = new Proxy($factory);
        $containerProxy = new ContainerProxy(new Container(), $proxy);

        self::assertInstanceOf(ThreadContainerProxy::class, $containerProxy->create(new Container()));
    }
}
