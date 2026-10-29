# Fund Tracker

A PHP and MySQL app for tracking deposits, expenses, balances, and cash flow,
with authenticated access, charts, and Excel/PDF transaction exports.

## Local setup

1. Install PHP 8+ with PDO MySQL and MySQL/MariaDB (XAMPP works).
2. Place the project in `htdocs/fund_tracker` to match the app's URL paths.
3. Create a `fund_tracker` database and import `database/schema.sql`.
4. Copy `database/local.example.php` to `database/local.php` and set your database credentials there (local default: port 3307). The local file is ignored by Git. Environment variables `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, and `DB_DATABASE` override these settings.
5. Run `php database/seed_users.php` and optionally `php database/seed_demo.php`.
6. Start Apache/MySQL and open `http://localhost/fund_tracker/login.php`.

The predefined accounts below are for local development. Change their passwords
before deploying to a shared or public server. Database records are not stored
in this repository.

## Features and checks

UI styling is shared in `assets/css/app.css`, with Segoe UI typography, indigo branding,
teal deposits, and coral expenses.

To add sample transactions locally, run `php database/seed_demo.php`.
It adds 17 entries prefixed `[DEMO]`, dated across the past 29 calendar days,
and skips sample names that already exist. Samples are included in report totals
and can be removed individually from the Transactions page.


Authentication setup: run `php database/seed_users.php` once to create the users
 table and predefined accounts `gabo` and `niko` (password: `admin-pass`).
The setup preserves existing account passwords. Open `/fund_tracker/login.php`.
Both accounts use the same shared transaction ledger. Passwords are hashed;
protected routes require a session, and write forms require a CSRF token.

Transaction tables use DataTables search, sorting, pagination, and Excel/PDF
buttons. Exports include all filtered rows across pages and omit the action column.
DataTables, JSZip, and pdfmake load from their existing public CDNs.

Checks: `php tests/FundSummaryTest.php` and `python tests/auth_http_test.py`
(the latter requires the local app at `http://localhost/fund_tracker/`).
