<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy;

use ReactParallel\ObjectProxy\ProxyListInterface;

final class OverridesProvider
{
    private ProxyListInterface $proxyList;

    public function __construct(ProxyListInterface $proxyList)
    {
        $this->proxyList = $proxyList;
    }

    /**
     * @return iterable<string>
     */
    public function list(): iterable
    {
        $list = [];

        foreach ($this->proxyList->interfaces() as $interface) {
            $list[$interface] = $interface;
        }

        foreach ($this->proxyList->noPromiseKnownInterfaces() as $noPromiseInterface => $interface) {
            $list[$noPromiseInterface] = $interface;
        }

        yield from $list;
    }
}
