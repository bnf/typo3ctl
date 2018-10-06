<?php
declare(strict_types = 1);
namespace Bnf\TYPO3Ctl\Command;

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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Upgrade wizard command for running wizards
 */
class UpgradeWizardRunCommand extends \TYPO3\CMS\Install\Command\UpgradeWizardRunCommand
{
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        if (!@file_exists($configurationManager->getLocalConfigurationFileLocation())) {
            $io = new SymfonyStyle($input, $output);
            $io->note('Upgrade wizards not yet available. Run the TYPO3 installation first.');
            return 0;
        }
        return parent::execute($input, $output);
    }
}
