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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Console\CommandRequestHandler;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command Line Interface Request Handler dealing with registered commands.
 */
class MaintenanceCommandRequestHandler extends CommandRequestHandler
{
    /**
     * Constructor initializing the symfony application
     */
    public function __construct()
    {
        $this->application = new Application('TYPO3 CMS Maintenance', TYPO3_version);
    }

    /**
     * Handles any commandline request
     *
     * @param InputInterface $input
     * @return int
     */
    public function handleRequest(InputInterface $input): int
    {
        $output = new ConsoleOutput();

        // create the BE_USER object (not logged in yet)
        Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
        Bootstrap::initializeLanguageObject();
        // Make sure output is not buffered, so command-line output and interaction can take place
        ob_clean();

        $this->populateAvailableCommands();

        return $this->application->run($input, $output);
    }

    /**
     * Put all available commands inside the application
     * @throws \TYPO3\CMS\Core\Console\CommandNameAlreadyInUseException
     */
    protected function populateAvailableCommands()
    {
        $commands = GeneralUtility::makeInstance(MaintenanceCommandRegistry::class, null);

        foreach ($commands as $commandName => $command) {
            $this->application->add($command);
        }
    }
}
