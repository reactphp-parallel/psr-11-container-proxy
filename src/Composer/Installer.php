<?php

declare(strict_types=1);

namespace ReactParallel\Psr11ContainerProxy\Composer;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Illuminate\Support\Collection;

use function array_key_exists;
use function array_unique;
use function array_values;
use function count;
use function defined;
use function dirname;
use function file_exists;
use function function_exists;
use function microtime;
use function round;
use function Safe\chmod;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\spl_autoload_register;
use function Safe\sprintf;
use function Safe\substr;
use function str_replace;
use function strlen;
use function strpos;
use function var_export;
use function WyriHaximus\getIn;

use const DIRECTORY_SEPARATOR;
use const PHP_INT_MIN;
use const WyriHaximus\Constants\Boolean\TRUE_;
use const WyriHaximus\Constants\Numeric\TWO;

final class Installer implements PluginInterface, EventSubscriberInterface
{
    /**
     * @return array<string, array<string|int>>
     */
    public static function getSubscribedEvents(): array
    {
        return [ScriptEvents::PRE_AUTOLOAD_DUMP => ['locateCustomOverrides', PHP_INT_MIN]];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        // does nothing, see getSubscribedEvents() instead.
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // does nothing, see getSubscribedEvents() instead.
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // does nothing, see getSubscribedEvents() instead.
    }

    /**
     * Called before every dump autoload, generates a fresh PHP class.
     */
    public static function locateCustomOverrides(Event $event): void
    {
        $start    = microtime(true);
        $io       = $event->getIO();
        $composer = $event->getComposer();

        $rootPath = self::locateRootPackageInstallPath($composer->getConfig(), $composer->getPackage());

        if (! function_exists('React\Promise\Resolve')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/react/promise/src/functions_include.php';
        }

        if (! function_exists('ApiClients\Tools\Rx\observableFromArray')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/api-clients/rx/src/functions_include.php';
        }

        if (! function_exists('WyriHaximus\getIn')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/wyrihaximus/string-get-in/src/functions_include.php';
        }

        if (! defined('WyriHaximus\Constants\Numeric\TWO')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/wyrihaximus/constants/src/Numeric/constants_include.php';
        }

        if (! defined('WyriHaximus\Constants\Boolean\TRUE')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/wyrihaximus/constants/src/Boolean/constants_include.php';
        }

        if (! function_exists('igorw\get_in')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/igorw/get-in/src/get_in.php';
        }

        if (! function_exists('Safe\file_get_contents')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/thecodingmachine/safe/generated/filesystem.php';
        }

        if (! function_exists('Safe\sprintf')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/thecodingmachine/safe/generated/strings.php';
        }

        if (! function_exists('Safe\spl_autoload_register')) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $composer->getConfig()->get('vendor-dir') . '/thecodingmachine/safe/generated/spl.php';
        }

        // Composer is bugged and doesn't handle root package autoloading properly yet
        if (array_key_exists('psr-4', $composer->getPackage()->getAutoload())) {
            foreach ($composer->getPackage()->getAutoload()['psr-4'] as $ns => $p) {
                $p = dirname($composer->getConfig()->get('vendor-dir')) . '/' . $p;
                spl_autoload_register(static function (string $class) use ($ns, $p): void {
                    if (strpos($class, $ns) !== 0) {
                        return;
                    }

                    $fileName = $p . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($ns))) . '.php';
                    if (! file_exists($fileName)) {
                        return;
                    }

                    include $fileName;
                });
            }
        }

        $io->write('<info>react-parallel/psr-11-container-proxy:</info> Custom overrides');

        $installPath     = self::locateRootPackageInstallPath($composer->getConfig(), $composer->getPackage()) . '/src/Generated/';
        $packages        = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $packages[]      = $composer->getPackage();
        $customOverrides = array_unique(array_values((new Collection($packages))->filter(
            static fn (PackageInterface $package): bool => (bool) count($package->getAutoload())
        )->flatMap(
            static fn (PackageInterface $package): array => getIn($package->getExtra(), 'react-parallel.psr-11-container-proxy.custom-overrides', [])
        )->all()));

        $io->write('<info>react-parallel/psr-11-container-proxy:</info> Found ' . count($customOverrides) . ' custom overrides');

        $classContents = sprintf(
            str_replace(
                "['%s']",
                '%s',
                file_get_contents(
                    $rootPath . '/etc/CustomOverridesList.php'
                )
            ),
            var_export($customOverrides, TRUE_),
        );

        file_put_contents($installPath . 'CustomOverridesList.php', $classContents);
        chmod($installPath . 'CustomOverridesList.php', 0664);

        $io->write(sprintf(
            '<info>react-parallel/psr-11-container-proxy:</info> Collected custom overrides and generated list in %s second(s)',
            round(microtime(TRUE_) - $start, TWO)
        ));
    }

    /**
     * Find the location where to put the generate PHP class in.
     */
    private static function locateRootPackageInstallPath(
        Config $composerConfig,
        RootPackageInterface $rootPackage
    ): string {
        // You're on your own
        if ($rootPackage->getName() === 'react-parallel/psr-11-container-proxy') {
            return dirname($composerConfig->get('vendor-dir'));
        }

        return $composerConfig->get('vendor-dir') . '/react-parallel/psr-11-container-proxy';
    }
}
