<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Bnf\TYPO3Ctl;

use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;
use TYPO3\CMS\Core\Console\CommandApplication;
use TYPO3\CMS\Core\Context\Context;

/**
 * @internal
 */
class ServiceProvider implements ServiceProviderInterface
{
    public function getFactories(): array
    {
        return [
            CommandApplication::class => [ static::class, 'getCommandApplication' ],
            MaintenanceCommandRegistry::class => [ static::class, 'getMaintenanceCommandRegistry' ],
            Command\DumpPackageStatesCommand::class => [ static::class, 'getDumpPackageStatesCommand' ],
            Command\PrepareFolderStructureCommand::class => [ static::class, 'getPrepareFolderStructureCommand' ],
            Command\CacheFlushCommand::class => [ static::class, 'getCacheFlushCommand' ],
            Command\DatabaseUpdateCommand::class => [ static::class, 'getDatabaseUpdateCommand' ],
            Command\UpgradeWizardRunCommand::class => [ static::class, 'getUpgradeWizardRunCommand' ],
            Command\UpgradeWizardListCommand::class => [ static::class, 'getUpgradeWizardListCommand' ],
        ];
    }

    public function getExtensions(): array
    {
        return [
            MaintenanceCommandRegistry::class => [ static::class, 'configureCommands' ],
        ];
    }

    public static function getCommandApplication(ContainerInterface $container): CommandApplication
    {
        return new CommandApplication(
            $container->get('failsafe-typo3-container')->get(Context::class),
            $container->get(MaintenanceCommandRegistry::class)
        );
    }

    public static function getMaintenanceCommandRegistry(ContainerInterface $container)
    {
        return new MaintenanceCommandRegistry($container);
    }

    public static function configureCommands(ContainerInterface $container, MaintenanceCommandRegistry $commandRegistry): MaintenanceCommandRegistry
    {
        $commandRegistry->addLazyCommand('prepare:packagestates', Command\DumpPackageStatesCommand::class, false);
        $commandRegistry->addLazyCommand('prepare:folderstructure', Command\PrepareFolderStructureCommand::class, false);
        $commandRegistry->addLazyCommand('cache:flush', Command\CacheFlushCommand::class, false);
        $commandRegistry->addLazyCommand('database:migrate', Command\DatabaseUpdateCommand::class, false);
        $commandRegistry->addLazyCommand('upgrade:run', Command\UpgradeWizardRunCommand::class, false);
        $commandRegistry->addLazyCommand('upgrade:list', Command\UpgradeWizardListCommand::class, false);

        return $commandRegistry;
    }

    public static function getDumpPackageStatesCommand(ContainerInterface $container): Command\DumpPackageStatesCommand
    {
        $container = $container->get('failsafe-typo3-container');
        return new Command\DumpPackageStatesCommand('prepare:packagestates');
    }

    public static function getPrepareFolderStructureCommand(ContainerInterface $container): Command\PrepareFolderStructureCommand
    {
        $container = $container->get('failsafe-typo3-container');
        return new Command\PrepareFolderStructureCommand('prepare:folderstructure');
    }

    public static function getCacheFlushCommand(ContainerInterface $container): Command\CacheFlushCommand
    {
        $container = $container->get('failsafe-typo3-container');
        return new Command\CacheFlushCommand('cache:flush');
    }

    public static function getDatabaseUpdateCommand(ContainerInterface $container): Command\DatabaseUpdateCommand
    {
        $container = $container->get('failsafe-typo3-container');
        return new Command\DatabaseUpdateCommand('database:migrate');
    }

    public static function getUpgradeWizardRunCommand(ContainerInterface $container): Command\UpgradeWizardRunCommand
    {
        $container = $container->get('failsafe-typo3-container');
        return new Command\UpgradeWizardRunCommand(
            'upgrade:run',
            $container->get(\TYPO3\CMS\Install\Service\LateBootService::class),
            $container->get(\TYPO3\CMS\Install\Service\UpgradeWizardsService::class)
        );
    }

    public static function getUpgradeWizardListCommand(ContainerInterface $container): Command\UpgradeWizardListCommand
    {
        $container = $container->get('failsafe-typo3-container');
        return new Command\UpgradeWizardListCommand(
            'upgrade:list',
            $container->get(\TYPO3\CMS\Install\Service\LateBootService::class),
            $container->get(\TYPO3\CMS\Install\Service\UpgradeWizardsService::class)
        );
    }
}
