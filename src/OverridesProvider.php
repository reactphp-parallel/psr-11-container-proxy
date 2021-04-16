<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy;

use ReactParallel\ObjectProxy\ProxyListInterface;

final class OverridesProvider
{
    private ProxyListInterface $proxyList;
    private CustomOverridesListInterface $customOverridesList;

    public function __construct(ProxyListInterface $proxyList, CustomOverridesListInterface $customOverridesList)
    {
        $this->proxyList           = $proxyList;
        $this->customOverridesList = $customOverridesList;
    }

    /**
     * @return iterable<string>
     */
    public function list(): iterable
    {
        $list = [];

        foreach ($this->customOverridesList->list() as $override) {
            $list[$override] = $override;
        }

        foreach ($this->proxyList->interfaces() as $interface) {
            $list[$interface] = $interface;
        }

        foreach ($this->proxyList->noPromiseKnownInterfaces() as $noPromiseInterface => $interface) {
            $list[$noPromiseInterface] = $interface;
        }

        yield from $list;
    }
}
