# Isolated regression testing

This workflow uses a fresh, disposable MariaDB server, not the operational XAMPP server. No operational database is copied. All records come from repository migrations and synthetic seeders. The main `.env` is not modified.

## Initialize a disposable instance on Windows

Run from the repository root. Port 33177 must be free. The data directory must be new; preserve any previous instance separately after shutting it down. Never point these commands at `C:\xampp\mysql\data`.

```powershell
New-Item -ItemType Directory -Path .remediation -Force | Out-Null
& C:\xampp\mysql\bin\mysql_install_db.exe --datadir=C:\xampp\htdocs\yza_automotive_laravel\.remediation\mysql-data --port=33177 --silent
Start-Process -FilePath C:\xampp\mysql\bin\mysqld.exe -ArgumentList '--defaults-file=C:\xampp\htdocs\yza_automotive_laravel\.remediation\mysql-data\my.ini','--bind-address=127.0.0.1','--port=33177','--console' -WindowStyle Hidden -RedirectStandardOutput .remediation/mysql-out.log -RedirectStandardError .remediation/mysql-error.log
& C:\xampp\php\php.exe scripts/prepare-isolated-tests.php
```

The preparer refuses port 3306, verifies the actual server port and data directory before DDL, and refuses to replace an existing test database. It writes an ignored PHPUnit configuration with a disposable application key, isolated database settings, array mail/cache/session, null broadcasting, sandbox payment configuration, and a separate compiled-view directory.

The fixture order matters: reserve the explicit historical vehicle IDs, create the early schema, seed the existing premium gallery definitions, apply the remaining migrations, then seed the legacy regression data, physical inventory, staff, and dependent personnel profiles. Synthetic membership activations pass through the real gateway processor with an in-process synthetic provider. Payment eligibility checks are not bypassed or backdated.

## Run the complete suites

```powershell
$env:PATH = 'C:\xampp\php;' + $env:PATH
& C:\xampp\php\php.exe vendor/phpunit/phpunit/phpunit --configuration=.remediation/phpunit.isolated.xml --log-junit=.remediation/results.xml
node --test tests/Ui/notification-panel.test.cjs tests/Ui/notification-sync.test.cjs tests/Ui/order-sales.test.cjs tests/Ui/rupiah-input.test.cjs tests/Ui/smart-spk.test.cjs
npm.cmd run build
```

Use PHPUnit directly with the isolated configuration. `artisan test` injects its own configuration argument, so supplying a second configuration produces a duplicate-option warning. Concurrency workers inherit the selected database port, including the SPK worker.

## Preserve evidence and stop the instance

Keep raw results, database files, and configuration under ignored `.remediation/`. Publish only a reviewed summary without tokens, payloads, or customer data. Generated HTML under `docs/qa` is also ignored because it can include session tokens and account-specific data.

Before shutdown, verify that `SELECT @@port, @@datadir` returns port 33177 and this repository's `.remediation/mysql-data`. Then stop only that instance:

```powershell
& C:\xampp\mysql\bin\mysqladmin.exe --protocol=tcp --host=127.0.0.1 --port=33177 --user=root shutdown
```

No cleanup command in this workflow deletes an existing database or rewrites Git history.
