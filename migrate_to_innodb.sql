-- ============================================================================
-- Aircraft Scheduling — Data Integrity Migration
-- ============================================================================
-- This script must be run during a maintenance window (tables are locked
-- during ALTER TABLE). Back up the entire database first.
--
-- Deployment order:
--   1. Full database backup
--   2. Phase 1: MyISAM → InnoDB
--   3. Phase 3: Add missing primary keys
--   4. Phase 4a: Data cleanup
--   5. Phase 4b: Foreign key constraints
-- ============================================================================

-- ============================================================================
-- Phase 1: Convert all tables from MyISAM to InnoDB
-- ============================================================================
-- InnoDB supports transactions, foreign keys, and row-level locking.
-- ALTER TABLE ... ENGINE=InnoDB preserves all existing data, indexes, and
-- auto-increment values. InnoDB uses ~1.5-2x more disk than MyISAM.

ALTER TABLE `AircraftScheduling_aircraft` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_certificates` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_config` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_entry` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_equipment_codes` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_instructors` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_journal` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_make` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_model` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_notices` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_person` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_pilot_certificates` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_repeat` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_required_ratings` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_resource` ENGINE=InnoDB;
ALTER TABLE `AircraftScheduling_schedulable` ENGINE=InnoDB;
ALTER TABLE `Categories` ENGINE=InnoDB;
ALTER TABLE `Charges` ENGINE=InnoDB;
ALTER TABLE `CurrencyFields` ENGINE=InnoDB;
ALTER TABLE `CurrencyRules` ENGINE=InnoDB;
ALTER TABLE `Flight` ENGINE=InnoDB;
ALTER TABLE `Inventory` ENGINE=InnoDB;
ALTER TABLE `Safety_Meeting` ENGINE=InnoDB;
ALTER TABLE `Squawks` ENGINE=InnoDB;

-- ============================================================================
-- Phase 3: Add missing primary keys
-- ============================================================================
-- These 5 tables lack primary keys. Adding an auto-increment PK at position
-- FIRST does not break existing queries (code uses named-column SELECTs).

ALTER TABLE `Flight` ADD COLUMN `flight_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `Charges` ADD COLUMN `charge_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `Inventory` ADD COLUMN `inventory_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `Squawks` ADD COLUMN `squawk_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `AircraftScheduling_notices` ADD COLUMN `notice_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

-- ============================================================================
-- Phase 4a: Data cleanup — fix values that would violate FK constraints
-- ============================================================================

-- repeat_id = 0 means "non-repeating" but 0 is not a valid FK target.
-- Change to NULL (which FK constraints allow).
UPDATE `AircraftScheduling_entry` SET `repeat_id` = NULL WHERE `repeat_id` = 0;

-- Allow repeat_id to be NULL (it is currently NOT NULL default 0)
ALTER TABLE `AircraftScheduling_entry` MODIFY `repeat_id` INT DEFAULT NULL;

-- resource_id = 0 in aircraft means "not schedulable". Change to NULL.
UPDATE `AircraftScheduling_aircraft` SET `resource_id` = NULL WHERE `resource_id` = 0;

-- Allow resource_id to be NULL in aircraft table
ALTER TABLE `AircraftScheduling_aircraft` MODIFY `resource_id` INT DEFAULT NULL;

-- Delete orphaned entries pointing to non-existent resources
DELETE FROM `AircraftScheduling_entry`
WHERE `resource_id` NOT IN (SELECT `resource_id` FROM `AircraftScheduling_resource`);

-- Delete orphaned entries pointing to non-existent repeat records
DELETE FROM `AircraftScheduling_entry`
WHERE `repeat_id` IS NOT NULL
  AND `repeat_id` NOT IN (SELECT `repeat_id` FROM `AircraftScheduling_repeat`);

-- Delete orphaned instructor records pointing to non-existent persons
DELETE FROM `AircraftScheduling_instructors`
WHERE `person_id` NOT IN (SELECT `person_id` FROM `AircraftScheduling_person`);

-- Delete orphaned pilot_certificates pointing to non-existent persons
DELETE FROM `AircraftScheduling_pilot_certificates`
WHERE `pilot_id` NOT IN (SELECT `person_id` FROM `AircraftScheduling_person`);

-- Delete orphaned pilot_certificates pointing to non-existent certificates
DELETE FROM `AircraftScheduling_pilot_certificates`
WHERE `certificate_id` NOT IN (SELECT `certificate_id` FROM `AircraftScheduling_certificates`);

-- Delete orphaned resource records pointing to non-existent schedulables
DELETE FROM `AircraftScheduling_resource`
WHERE `schedulable_id` NOT IN (SELECT `schedulable_id` FROM `AircraftScheduling_schedulable`);

-- ============================================================================
-- Phase 4b: Add foreign key constraints
-- ============================================================================

-- entry → resource (CASCADE: deleting a resource removes its schedule entries)
ALTER TABLE `AircraftScheduling_entry`
  ADD CONSTRAINT `fk_entry_resource`
  FOREIGN KEY (`resource_id`) REFERENCES `AircraftScheduling_resource` (`resource_id`)
  ON DELETE CASCADE;

-- entry → repeat (SET NULL: deleting a repeat record marks entries as non-repeating)
ALTER TABLE `AircraftScheduling_entry`
  ADD CONSTRAINT `fk_entry_repeat`
  FOREIGN KEY (`repeat_id`) REFERENCES `AircraftScheduling_repeat` (`repeat_id`)
  ON DELETE SET NULL;

-- aircraft → make (SET NULL: deleting a make clears the reference)
ALTER TABLE `AircraftScheduling_aircraft`
  ADD CONSTRAINT `fk_aircraft_make`
  FOREIGN KEY (`make_id`) REFERENCES `AircraftScheduling_make` (`make_id`)
  ON DELETE SET NULL;

-- aircraft.make_id must be nullable for SET NULL to work
ALTER TABLE `AircraftScheduling_aircraft` MODIFY `make_id` INT DEFAULT NULL;

-- aircraft → model (SET NULL)
ALTER TABLE `AircraftScheduling_aircraft`
  ADD CONSTRAINT `fk_aircraft_model`
  FOREIGN KEY (`model_id`) REFERENCES `AircraftScheduling_model` (`model_id`)
  ON DELETE SET NULL;

-- aircraft.model_id must be nullable for SET NULL to work
ALTER TABLE `AircraftScheduling_aircraft` MODIFY `model_id` INT DEFAULT NULL;

-- aircraft → resource (SET NULL: disabling scheduling clears the reference)
ALTER TABLE `AircraftScheduling_aircraft`
  ADD CONSTRAINT `fk_aircraft_resource`
  FOREIGN KEY (`resource_id`) REFERENCES `AircraftScheduling_resource` (`resource_id`)
  ON DELETE SET NULL;

-- instructors → person (CASCADE: deleting a person removes instructor record)
ALTER TABLE `AircraftScheduling_instructors`
  ADD CONSTRAINT `fk_instructors_person`
  FOREIGN KEY (`person_id`) REFERENCES `AircraftScheduling_person` (`person_id`)
  ON DELETE CASCADE;

-- pilot_certificates → person (CASCADE)
ALTER TABLE `AircraftScheduling_pilot_certificates`
  ADD CONSTRAINT `fk_pilotcert_person`
  FOREIGN KEY (`pilot_id`) REFERENCES `AircraftScheduling_person` (`person_id`)
  ON DELETE CASCADE;

-- pilot_certificates → certificates (CASCADE)
ALTER TABLE `AircraftScheduling_pilot_certificates`
  ADD CONSTRAINT `fk_pilotcert_certificate`
  FOREIGN KEY (`certificate_id`) REFERENCES `AircraftScheduling_certificates` (`certificate_id`)
  ON DELETE CASCADE;

-- resource → schedulable (CASCADE: deleting a schedulable type removes resources)
ALTER TABLE `AircraftScheduling_resource`
  ADD CONSTRAINT `fk_resource_schedulable`
  FOREIGN KEY (`schedulable_id`) REFERENCES `AircraftScheduling_schedulable` (`schedulable_id`)
  ON DELETE CASCADE;
