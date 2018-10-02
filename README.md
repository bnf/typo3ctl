# TYPO3 Maintenance Command Line Binary (experimental)

`typo3ctl` is a lightweight replacement for `helhum/typo3-console` for maintenance related tasks.

Do **not** use this package in production yet. It is supposed to be merged into `typo3/cms-cli` in near future (and later to `typo3/cms-core`).

## Installation

```sh
composer require bnf/typo3ctl
```

## Usage

Use `bin/typo3ctl` for maintenance tasks like:

```sh
vendor/bin/typo3ctl prepare:packagestates
vendor/bin/typo3ctl prepare:folderstructure
vendor/bin/typo3ctl database:migrate
vendor/bin/typo3ctl cache:flush
vendor/bin/typo3ctl upgrade:run
```

Use the core command line tool `bin/typo3` for execution of business logic:

```sh
vendor/bin/typo3 import:foobar
vendor/bin/typo3 scheduler:run
```

## Further Notes

If you want to start the migration from `helhum/typo3-console` to the core cli command
to execute business logic, then please be aware that `helhum/typo3-console` prevents the
installation of `vendor/bin/typo3` when installed in parallel. You can either execute the binary
from the sources in the web directory:
```sh
public/typo3/sysext/core/bin/typo3
```

Or alternatively install `bnf/combat-helhum-console-autocracy` which forces installation
of `vendor/bin/typo3`:
```sh
composer require bnf/combat-helhum-console-autocracy
```
