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
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\Schema\Exception\StatementException;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command for update database schem
 */
class DatabaseUpdateCommand extends Command
{
    /**
     * Defines the allowed options for this command
     */
    protected function configure()
    {
        $this->setDescription('Updates database schema.');
        $this->setHelp('This command is used to update the database schema.');
    }

    /**
     * Bootstrap running of database update
     */
    protected function bootstrap()
    {
        Bootstrap::loadTypo3LoadedExtAndExtLocalconf(false);
        Bootstrap::unsetReservedGlobalVariables();
        Bootstrap::loadBaseTca(false);
        Bootstrap::loadExtTables(false);
    }

    /**
     * Clear caches
     *
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (empty($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'] ?? [])) {
            $io->note('Skipping database migration. No database connection configured.');
            return;
        }

        try {
            $this->bootstrap();
        } catch (\Throwable $e) {
            $io->error([
                'Failed to load ext_localconf.php and ext_tables.php files: ' . $e->getMessage(),
                $e->getFile() . ' (' . $e->getLine() . ')'
            ]);
            return 1;
        }

        $statementHashesToPerform = [];

        try {
            $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
            $sqlStatements = $sqlReader->getCreateTableStatementArray($sqlReader->getTablesDefinitionString());
            $schemaMigrationService = GeneralUtility::makeInstance(SchemaMigrator::class);
            $addCreateChange = $schemaMigrationService->getUpdateSuggestions($sqlStatements);

            foreach ($addCreateChange as $connection => $changes) {
                foreach ($changes as $type => $statements) {
                    foreach ($statements as $hash => $statement) {
                        $statementHashesToPerform[] = $hash;
                    }
                }
            }

            $results = $schemaMigrationService->migrate($sqlStatements, array_flip($statementHashesToPerform));

            // Create error flash messages if any
            $failed = false;
            foreach ($results as $errorMessage) {
                $failed = true;
                $io->error('Database update failed: ' . $errorMessage);
            }
            if ($failed) {
                return 1;
            }
        } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
            // No database available, fail silenty
            $io->note('Skipping database migration. Connection can not be established.');
            return 0;
        } catch (StatementException $e) {
            $io->error('Database analysis failed: ' . $e->getMessage());
            return 1;
        } catch (\Doctrine\DBAL\DBALException $e) {
            $io->error('Database analysis failed: ' . $e->getMessage());
            return 1;
        }

        if (empty($statementHashesToPerform)) {
            $io->success('No migration required.');
        } else {
            $io->success('Database has been migrated.');
        }
    }
}
