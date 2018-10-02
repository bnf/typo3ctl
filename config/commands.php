<?php

return [
    'prepare:packagestates' => [
        'class' => \Bnf\TYPO3Ctl\Command\DumpPackageStatesCommand::class,
        'maintenance' => true,
        'schedulable' => false,
    ],
    'prepare:folderstructure' => [
        'class' => \Bnf\TYPO3Ctl\Command\PrepareFolderStructureCommand::class,
        'maintenance' => true,
        'schedulable' => false,
    ],
    'cache:flush' => [
        'class' => \Bnf\TYPO3Ctl\Command\CacheFlushCommand::class,
        'maintenance' => true,
        'schedulable' => false,
    ],
    'database:migrate' => [
        'class' => \Bnf\TYPO3Ctl\Command\DatabaseUpdateCommand::class,
        'maintenance' => true,
        'schedulable' => false,
    ],
];
