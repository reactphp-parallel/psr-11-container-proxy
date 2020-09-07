<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use ReactParallel\Factory as ParallelFactory;
use ReactParallel\ObjectProxy\Proxy;
use ReactParallel\Psr11ContainerProxy\ContainerProxy;
use Yuloh\Container\Container;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$loop            = Factory::create();
$parallelFactory = new ParallelFactory($loop);
$container       = new Container();
$container->set(LoggerInterface::class, static fn (): LoggerInterface => new Logger('main', [new StreamHandler(STDOUT)]));
$proxy = new ContainerProxy($container, new Proxy($parallelFactory));
$parallelFactory->call(static function (ContainerProxy $proxy, int $time): int {
    $container = new Container();
    $container->set('logger', static fn (): LoggerInterface => new Logger('child', [new StreamHandler(STDOUT)]));
    $containerProxy = $proxy->create($container);
    $containerProxy->get('logger')->critical('Time: ' . $time);
    $containerProxy->get(LoggerInterface::class)->critical('Time: ' . $time);

    return $time;
}, [$proxy, time()])->then(static function (int $time) use ($parallelFactory): void {
    echo 'Time: ', $time;
    $parallelFactory->lowLevelPool()->kill();
})->done();

$loop->run();
