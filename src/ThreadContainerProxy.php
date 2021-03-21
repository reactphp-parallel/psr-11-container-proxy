<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy;

use Psr\Container\ContainerInterface;
use ReactParallel\ObjectProxy\AbstractGeneratedProxy;
use ReactParallel\ObjectProxy\Proxy\DeferredCallHandler;
use ReactParallel\ObjectProxy\ProxyListInterface;

final class ThreadContainerProxy implements ContainerInterface
{
    private ProxyListInterface $proxyList;
    private ContainerInterface $local;
    private ContainerInterface $remote;

    public function __construct(ProxyListInterface $proxyList, ContainerInterface $local, ContainerInterface $remote)
    {
        $this->proxyList = $proxyList;
        $this->local     = $local;
        $this->remote    = $remote;
    }

    // phpcs:disable
    public function has($id)
    {
        return $this->proxyList->has($id) || $this->local->has($id);
    }

    // phpcs:disable
    public function get($id)
    {
        if ($this->proxyList->has($id)) {
            $proxy = $this->remote->get($id);
            if ($proxy instanceof AbstractGeneratedProxy) {
                $proxy->setDeferredCallHandler($this->local->get(DeferredCallHandler::class));
                $proxy->notifyMainThreadAboutOurExistence();
            }
            return $proxy;
        }

        return $this->local->get($id);
    }
}
