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
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command for dumping the PackageStates.php file
 */
class DumpPackageStatesCommand extends Command
{
    /**
     * Defines the allowed options for this command
     */
    protected function configure()
    {
        $this->setDescription('Dumps PackageStates.php. All installed packages will be activated.');
        $this->setHelp('This command is only useful in composer mode. Use the extension manager in classic mode.');
    }

    /**
     * This command is only useful in composer mode.
     *
     * @inheritdoc
     */
    public function isEnabled()
    {
        return Environment::isComposerMode();
    }

    /**
     * Dump PackageStates.php
     *
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        if (!$packageManager instanceof \TYPO3\CMS\Core\Package\FailsafePackageManager) {
            throw new \TYPO3\CMS\Core\Exception(self::class . ' can only be executed in failsafe mode', 1536919047);
        }

        $configPath = Environment::getLegacyConfigPath();
        if (!is_dir($configPath)) {
            GeneralUtility::mkdir_deep($configPath);
        }
        $packages = $packageManager->getAvailablePackages();
        \Closure::bind(function () use ($packages) {
            foreach ($packages as $package) {
                $this->registerActivePackage($package);
            }
        }, $packageManager);
        $packageManager->forceSortAndSavePackageStates();
        $io->success('The file PackageStates.php has been updated.');
    }
}
