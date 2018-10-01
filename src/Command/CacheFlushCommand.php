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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command for flushing caches
 */
class CacheFlushCommand extends Command
{
    /**
     * Defines the allowed options for this command
     */
    protected function configure()
    {
        $this->setDescription('Flushes caches.');
        $this->setHelp('This command is used to flush caches.');
    }

    /**
     * Clear caches
     *
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        if ($packageManager instanceof \TYPO3\CMS\Core\Package\FailsafePackageManager) {
            GeneralUtility::makeInstance(\Bnf\TYPO3Ctl\Service\ClearCacheService::class)->clearAll();

            if (!$this->checkIfEssentialConfigurationExists()) {
                $io->success('Default file caches have been flushed.');
                // We are done here.
                return;
            }
        } else {
            // retrieve existing cachemanager instance
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $cacheManager->flushCaches();
        }
        $io->success('All caches have been flushed.');
    }

    /**
     * Check if LocalConfiguration.php and PackageStates.php exist
     *
     * @return bool TRUE when the essential configuration is available, otherwise FALSE
     */
    protected function checkIfEssentialConfigurationExists(): bool
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        return file_exists($configurationManager->getLocalConfigurationFileLocation());
    }
}
