<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy;

use Psr\Container\ContainerInterface;
use ReactParallel\ObjectProxy\Proxy;

final class ContainerProxy
{
    private ContainerInterface $proxy;

    public function __construct(ContainerInterface $container, Proxy $proxy)
    {
        /**
         * @psalm-suppress PropertyTypeCoercion
         * @phpstan-ignore-next-line
         */
        $this->proxy = $proxy->share($container, ContainerInterface::class);
    }

    public function create(ContainerInterface $container): ContainerInterface
    {
        return new ThreadContainerProxy($container, $this->proxy);
    }

    public function proxy(): ContainerInterface
    {
        return $this->proxy;
    }
}
