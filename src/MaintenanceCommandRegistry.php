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
        if ($this->commands) {
            return;
        }

        $configurationFiles = [];
        foreach ($this->packageManager->getActivePackages() as $package) {
            $configurationFiles[] = $package->getPackagePath() . 'Configuration/Commands.php';
        }
        $configurationFiles[] = dirname(__DIR__) . '/config/commands.php';

        // Core commands that may be executed in maintenance mode
        $whitelist = [
            'upgrade:run',
            'upgrade:list',
        ];

        foreach ($configurationFiles as $commandsOfExtension) {
            if (@is_file($commandsOfExtension)) {
                /*
                 * We use require instead of require_once here because it eases the testability as require_once returns
                 * a boolean from the second execution on. As this class is a singleton, this require is only called
                 * once per request anyway.
                 */
                $commands = require $commandsOfExtension;
                if (is_array($commands)) {
                    foreach ($commands as $commandName => $commandConfig) {
                        // tri-state: true (bool), false (bool, default), not-exclusive (string)
                        $isMaintenanceCommand = $commandConfig['maintenance'] ?? false;
                        if ($isMaintenanceCommand !== $this->maintenanceMode && $isMaintenanceCommand !== 'not-exclusive' && !in_array($commandName, $whitelist)) {
                            continue;
                        }
                        if (array_key_exists($commandName, $this->commands)) {
                            throw new CommandNameAlreadyInUseException(
                                'Command "' . $commandName . '" registered by "' . $package->getPackageKey() . '" is already in use',
                                1484486383
                            );
                        }
                        $this->commands[$commandName] = GeneralUtility::makeInstance($commandConfig['class'], $commandName);
                        $this->commandConfigurations[$commandName] = $commandConfig;
                    }
                }
            }
        }
    }
}
