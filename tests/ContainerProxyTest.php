<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Psr11ContainerProxy;

use React\EventLoop\Factory as EventLoopFactory;
use ReactParallel\Factory;
use ReactParallel\ObjectProxy\Configuration;
use ReactParallel\ObjectProxy\Generated\Proxies\Psr\Container\ContainerInterface;
use ReactParallel\ObjectProxy\Generated\ProxyList;
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
        $configuration  = new Configuration($factory);
        $proxy          = new Proxy($configuration);
        $containerProxy = new ContainerProxy(new ProxyList(), new Container(), $proxy);

        self::assertInstanceOf(ThreadContainerProxy::class, $containerProxy->create(new Container()));
    }

    /**
     * @test
     */
    public function proxy(): void
    {
        $loop           = EventLoopFactory::create();
        $factory        = new Factory($loop);
        $configuration  = new Configuration($factory);
        $proxy          = new Proxy($configuration);
        $containerProxy = new ContainerProxy(new ProxyList(), new Container(), $proxy);

        self::assertInstanceOf(ContainerInterface::class, $containerProxy->proxy());
    }
}
