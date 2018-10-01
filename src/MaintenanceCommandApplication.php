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

use Symfony\Component\Console\Input\ArgvInput;
use TYPO3\CMS\Core\Console\CommandApplication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Entry point for the TYPO3 Command Line for Maintenance Commands
 * Does not run the RequestHandler as this already runs an Application inside an Application which
 * is just way too much logic around simple CLI calls
 *
 * @internal
 */
class MaintenanceCommandApplication extends CommandApplication
{
    /**
     * Run the Symfony Console application in this TYPO3 application
     *
     * @param callable $execute
     */
    public function run(callable $execute = null)
    {
        $this->initializeContext();
        $handler = GeneralUtility::makeInstance(MaintenanceCommandRequestHandler::class);
        $handler->handleRequest(new ArgvInput());

        if ($execute !== null) {
            call_user_func($execute);
        }
    }
}
