<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy;

use Psr\Container\ContainerInterface;
use ReactParallel\ObjectProxy\Generated\ProxyList;

use function array_key_exists;

final class ThreadContainerProxy extends ProxyList implements ContainerInterface
{
    private ContainerInterface $local;
    private ContainerInterface $remote;

    public function __construct(ContainerInterface $local, ContainerInterface $remote)
    {
        $this->local  = $local;
        $this->remote = $remote;
    }

    // phpcs:disable
    public function has($id)
    {
        return array_key_exists($id, self::KNOWN_INTERFACE) || $this->local->has($id);
    }

    // phpcs:disable
    public function get($id)
    {
        if (array_key_exists($id, self::KNOWN_INTERFACE)) {
            return $this->remote->get($id);
        }

        return $this->local->get($id);
    }
}
