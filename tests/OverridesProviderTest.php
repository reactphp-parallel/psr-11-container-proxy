<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Psr11ContainerProxy;

use ReactParallel\ObjectProxy\ProxyList\Proxy;
use ReactParallel\ObjectProxy\ProxyListInterface;
use ReactParallel\Psr11ContainerProxy\OverridesProvider;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

use function WyriHaximus\iteratorOrArrayToArray;

final class OverridesProviderTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function list(): void
    {
        $proxyList = new class () implements ProxyListInterface {
            public function has(string $interface): bool
            {
                return false;
            }

            public function get(string $interface): Proxy
            {
                return new Proxy($interface, $interface);
            }

            /**
             * @return iterable<string, string>
             */
            public function interfaces(): iterable
            {
                yield 'pizza' => 'fungi';
                yield 'pancake' => 'pancake';
            }

            /**
             * @return array<string, string>
             */
            public function noPromiseKnownInterfaces(): array
            {
                return ['pancake' => 'cheese union'];
            }
        };

        $list = iteratorOrArrayToArray((new OverridesProvider($proxyList))->list());

        self::assertSame([
            'fungi' => 'fungi',
            'pancake' => 'cheese union',
        ], $list);
    }
}
