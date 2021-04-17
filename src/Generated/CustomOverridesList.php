<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy\Generated;

use ReactParallel\Psr11ContainerProxy\CustomOverridesListInterface;

final class CustomOverridesList implements CustomOverridesListInterface
{
    private const CUSTOM_OVERRIDES = ['%s'];

    /**
     * @return iterable<string>
     */
    public function list(): iterable
    {
        yield from self::CUSTOM_OVERRIDES;
    }
}
