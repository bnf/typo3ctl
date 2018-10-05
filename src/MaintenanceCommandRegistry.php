<?php
declare(strict_types = 1);
namespace Bnf\TYPO3Ctl;

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

use TYPO3\CMS\Core\Console\CommandNameAlreadyInUseException;
use TYPO3\CMS\Core\Console\CommandRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Registry for Symfony commands, populated from extensions
 */
class MaintenanceCommandRegistry extends CommandRegistry
{
    /**
     * @var bool
     */
    protected $maintenanceMode = true;

    /**
     * @throws CommandNameAlreadyInUseException
     */
    protected function populateCommandsFromPackages()
    {
        if (!empty($this->commands)) {
            return;
        }

        $configuration = $this->getConfiguration();
        foreach ($configuration as $packageKey => $commands) {
            foreach ($commands as $commandName => $commandConfig) {
                if (!$this->isRegisteredForCurrentMode($commandConfig)) {
                    continue;
                }
                if (array_key_exists($commandName, $this->commands)) {
                    throw new CommandNameAlreadyInUseException(
                        'Command "' . $commandName . '" registered by "' . $packageKey . '" is already in use',
                        1484486383
                    );
                }
                $this->commands[$commandName] = GeneralUtility::makeInstance($commandConfig['class'], $commandName);
                $this->commandConfigurations[$commandName] = $commandConfig;
            }
        }
    }

    /**
     * @return array
     */
    protected function getConfiguration(): array
    {
        $configuration = [];
        foreach ($this->packageManager->getActivePackages() as $package) {
            $filename =  $package->getPackagePath() . 'Configuration/Commands.php';
            if (@is_file($filename)) {
                $commands = require $filename;
                if (is_array($commands)) {
                    $configuration[$package->getPackageKey()] = $commands;
                }
            }
        }
        $configuration['bnf/typo3ctl'] = require dirname(__DIR__) . '/config/commands.php';

        return $configuration;
    }

    /**
     * @param array
     */
    protected function isRegisteredForCurrentMode(array $commandConfig): bool
    {
        // tri-state: true (bool), false (bool, default), not-exclusive (string)
        $isMaintenanceCommand = $commandConfig['maintenance'] ?? false;
        if ($isMaintenanceCommand === 'not-exclusive') {
            return true;
        }
        return $isMaintenanceCommand === $this->maintenanceMode;
    }
}
