<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy;

interface CustomOverridesListInterface
{
    /**
     * @return iterable<string>
     */
    public function list(): iterable;
}
