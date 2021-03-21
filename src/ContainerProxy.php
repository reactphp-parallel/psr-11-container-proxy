<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy;

use Psr\Container\ContainerInterface;
use ReactParallel\ObjectProxy\Proxy;
use ReactParallel\ObjectProxy\ProxyListInterface;

final class ContainerProxy
{
    private ProxyListInterface $proxyList;
    private ContainerInterface $proxy;

    public function __construct(ProxyListInterface $proxyList, ContainerInterface $container, Proxy $proxy)
    {
        $this->proxyList = $proxyList;
        /**
         * @psalm-suppress PropertyTypeCoercion
         * @phpstan-ignore-next-line
         */
        $this->proxy = $proxy->share($container, ContainerInterface::class);
    }

    public function create(ContainerInterface $container): ContainerInterface
    {
        return new ThreadContainerProxy($this->proxyList, $container, $this->proxy);
    }

    public function proxy(): ContainerInterface
    {
        return $this->proxy;
    }
}
