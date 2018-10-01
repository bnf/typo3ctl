<?php

return [
    'prepare:packagestates' => [
        'class' => \Bnf\TYPO3Ctl\Command\DumpPackageStatesCommand::class,
        'maintenance' => true,
        'schedulable' => false,
    ],
    'cache:flush' => [
        'class' => \Bnf\TYPO3Ctl\Command\CacheFlushCommand::class,
        'maintenance' => true,
        'schedulable' => false,
    ],
];
