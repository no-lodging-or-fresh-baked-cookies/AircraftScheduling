# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AircraftScheduling is a PHP/MySQL web application for managing flying club operations: aircraft scheduling, flight tracking, pilot currency/compliance, billing, and inventory. It originated from MRBS (Meeting Room Booking System) → OpenFBO → AircraftScheduling. Current version: 2.50.

## Development Setup

No build system, package manager, or automated tests exist. This is a direct-deployment PHP application.

**Deploy:** Copy `Web/` contents to a web server document root, create a MySQL database, load `CreateEmptyAircraftSchedule.sql`, configure `Web/SiteSpecific.inc` (credentials) and `Web/config.inc` (feature flags). Default login: admin/admin.

**Database migration (InnoDB):** Run `migrate_to_innodb.sql` against an existing database to convert MyISAM→InnoDB, add primary keys, clean orphaned data, and add foreign key constraints. Back up first.

## Architecture

### Database Layer
- **`mysql.inc`** / **`pgsql.inc`** — Abstraction over mysqli/pgsql. Key functions: `sql_query()`, `sql_command()`, `sql_row()`, `sql_begin()`/`sql_commit()`/`sql_rollback()`, `sql_mutex_lock()`/`sql_mutex_unlock()`. Database handle is global `$db_c`.
- **`DatabaseFunctions.inc`** — Record-level operations using `DatabaseFieldType` objects. `SetDatabaseRecord()` builds field descriptors; `UpdateDatabaseRecord()`, `InsertDatabaseRecord()`, `DeleteDatabaseRecord()` execute them. `MySQLExecuteCommand()` wraps `sql_command()` with retry logic. `UpdateDatabaseRecord()` returns affected row count.
- **`DatabaseConstants.inc`** — Positional field offsets (e.g., `$username_offset = 1`) for SQL result row access. Must be updated when table schemas change.
- **`AircraftScheduling_sql.inc`** — Scheduling-specific SQL: conflict detection, entry CRUD, repeating series expansion, standby promotion.

### Configuration (3-tier)
1. **`SiteSpecific.inc`** — Database credentials, email config (never committed)
2. **`config.inc`** — Feature flags (`$AllowAircraftCheckout`, `$AllowSquawkControl`, `$dbsys`), auth backend selection, admin list
3. **`AircraftScheduling_config`** table — Runtime settings (company name, schedule hours, rates) loaded by `functions.inc`

### Authentication & Authorization
- **`AircraftScheduling_auth.inc`** — Wraps pluggable backends (`auth_session.inc` is primary; LDAP, IP, external also available)
- 6 user levels: Disabled(0), Normal(1), Super(2), Office(3), Maintenance(4), Admin(5)
- `getAuthorised($user, $pass, $requiredLevel)` checks access; `getWritable()` checks entry ownership
- Session variables: `$_SESSION["authenticated"]`, `$_SESSION["ActiveUsername"]`, `$_SESSION["ActiveUserLevel"]`, `$_SESSION["LastActivityTime"]`

### Currency/Compliance System (`CurrencyFunctions.inc`, 3678 lines)
The most complex subsystem. Tracks pilot qualifications against FAA requirements:
- **Pilot types:** Student, Private Under/Over 200hrs, Instrument, CFI
- **Currency states:** ClearedToFly(1), DayOnly(2), NoInstruments(3), NotCleared(4), Override(5), InformationOnly(6)
- **CurrencyFields table** defines trackable fields; **CurrencyRules table** defines pass criteria per pilot type
- Rules stored as serialized strings in `AircraftScheduling_person.Rules_Field`, parsed by `LoadDBCurrencyFields()`/`SaveDBCurrencyFields()`
- Currency checked at checkout time and displayed via `CheckCurrency.php`

### Key Workflows
- **Schedule entry:** `edit_entry.php` → `edit_entry_handler.php` → `AircraftScheduling_sql.inc` functions
- **Aircraft checkout:** `AircraftCheckout.php` — validates pilot currency, checks clearing authority, updates aircraft status to "In Use" (status=2), prints checkout form
- **Aircraft checkin:** `AircraftCheckin.php` — records flight data (hobbs/tach/fuel/landings), creates Flight records, updates aircraft status back to "On Line" (status=1) or "Grounded" (status=0)
- **Standby:** `StandbyRequest.php` → `StandbyRequestHandler.php` — waitlist entries (entry_type=-1) auto-promoted when slot opens

### Data Integrity Patterns
- All tables use InnoDB with foreign key constraints (CASCADE or SET NULL)
- Write-path PHP files acquire named mutex locks via `sql_mutex_lock()` before modifications
- Multi-step operations wrapped in `sql_begin()`/`sql_commit()` with `sql_rollback()` on failure
- Aircraft status updates use conditional WHERE clauses (`AND status=1` for checkout, `AND status=2` for checkin) with affected-rows checking to prevent race conditions
- `AdjustInventoryItem()` uses atomic `SET Quantity = Quantity + N` instead of read-modify-write
- `repeat_id` uses NULL (not 0) for non-repeating entries to support FK constraints

## Database Schema (24 tables, all InnoDB)

**Core scheduling:** `AircraftScheduling_entry` (bookings), `AircraftScheduling_resource` (schedulable units), `AircraftScheduling_schedulable` (resource types: 1=Aircraft, 2=Instructor), `AircraftScheduling_repeat` (repeating templates)

**Aircraft:** `AircraftScheduling_aircraft` (fleet, keyed by n_number), `AircraftScheduling_make`, `AircraftScheduling_model`, `AircraftScheduling_required_ratings`

**People:** `AircraftScheduling_person` (members/pilots), `AircraftScheduling_instructors`, `AircraftScheduling_pilot_certificates`, `AircraftScheduling_certificates`

**Operations:** `Flight` (flight records), `Charges` (billing), `Inventory` (parts/fuel), `Squawks` (maintenance faults), `Categories` (accounting codes), `Safety_Meeting`

**System:** `AircraftScheduling_config`, `AircraftScheduling_journal` (audit log), `AircraftScheduling_notices`, `CurrencyFields`, `CurrencyRules`

**Aircraft status values:** 0=Grounded, 1=On Line, 2=In Use, 3=Checking Out, 4=Checking In

## File Naming Conventions

- `Display[Entity].php` — Read-only list/detail views
- `AddModify[Entity].php` — CRUD forms (these are the largest files, 100K+ bytes each)
- `Print[Report].php` — Printable reports
- `[feature].inc` — PHP include libraries
- `[action]_handler.php` — Form submission handlers

## Important Conventions

- All PHP pages include `global_def.inc`, `config.inc`, `AircraftScheduling_auth.inc`, `$dbsys.inc`, `functions.inc` in that order
- Input parameters read from merged `$_GET`/`$_POST` via `$rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST'])`
- Usernames are case-insensitive, stored uppercase via `UCase()`
- Dates passed between pages as separate `$day`, `$month`, `$year` parameters
- Aircraft identified by `$resource` (name) and `$resource_id` (numeric) throughout URL parameters
- `$makemodel` URL parameter carries aircraft filter state between pages
- `print_header()` and `trailer.inc` bracket every page's HTML output
- `CreateJournalEntry()` called after every data modification for audit trail
- SQL escaping uses `addescapes()` (wrapper around `mysqli_real_escape_string`)
