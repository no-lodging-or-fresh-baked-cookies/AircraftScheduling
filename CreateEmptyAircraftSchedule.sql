# phpMyAdmin SQL Dump
# version 2.5.3
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Feb 21, 2005 at 01:30 PM
# Server version: 4.0.23
# PHP Version: 4.3.9
# 
# Database : `EmptySchedule`
# 

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_aircraft`
#

CREATE TABLE `AircraftScheduling_aircraft` (
  `aircraft_id` int(11) NOT NULL auto_increment,
  `n_number` varchar(10) NOT NULL default '',
  `serial_number` varchar(16) default NULL,
  `panel_picture` varchar(64) default NULL,
  `picture` varchar(64) default NULL,
  `hobbs` double default NULL,
  `tach1` double default NULL,
  `tach2` double default NULL,
  `hourly_cost` decimal(6,2) default NULL,
  `rental_fee` decimal(6,2) default NULL,
  `empty_weight` double default NULL,
  `max_gross` double default NULL,
  `year` smallint(6) default NULL,
  `code_id` int(11) default '0',
  `make_id` int(11) NOT NULL default '0',
  `model_id` int(11) NOT NULL default '0',
  `resource_id` int(11) NOT NULL default '0',
  `description` text,
  `ifr_cert` tinyint(1) default '0',
  `min_pilot_cert` int(11) default '0',
  `status` smallint(6) NOT NULL default '1',
  `Aircraft_Color` varchar(50) default NULL,
  `Hrs_Till_100_Hr` double default NULL,
  `100_Hr_Tach` float default NULL,
  `Annual_Due` datetime default NULL,
  `Fuel_Arm` float default NULL,
  `Default_Fuel_Gallons` float default NULL,
  `Full_Fuel_Gallons` float default NULL,
  `Front_Seat_Arm` float default NULL,
  `Rear_Seat_1_Arm` float default NULL,
  `Rear_Seat_2_Arm` float default NULL,
  `Front_Seat_Weight` float default NULL,
  `Rear_Seat_1_Weight` float default NULL,
  `Rear_Seat_2_Weight` float default NULL,
  `Baggage_Area_1_Arm` float default NULL,
  `Baggage_Area_2_Arm` float default NULL,
  `Baggage_Area_3_Arm` float default NULL,
  `Baggage_Area_1_Weight` float default NULL,
  `Baggage_Area_2_Weight` int(11) default NULL,
  `Baggage_Area_3_Weight` int(11) default NULL,
  `Aux_Fuel_Arm` float default NULL,
  `Aux_Fuel_Gallons` float default NULL,
  `Aircraft_Arm` float default NULL,
  `Aircraft_Weight` float default NULL,
  `Va_Max_Weight` float default NULL,
  `Current_Hobbs` float default NULL,
  `Current_User` varchar(40) default NULL,
  `CurrentKeycode` varchar(50) default NULL,
  `Aircraft_Owner_Name` varchar(50) default NULL,
  `Aircraft_Owner_Address` varchar(50) default NULL,
  `Aircraft_Owner_City` varchar(50) default NULL,
  `Aircraft_Owner_State` varchar(50) default NULL,
  `Aircraft_Owner_Zip` varchar(50) default NULL,
  `Aircraft_Owner_Contract` varchar(50) default NULL,
  `Aircraft_Owner_Phone1` varchar(50) default NULL,
  `Aircraft_Owner_Phone2` varchar(50) default NULL,
  `Aircraft_Remarks` varchar(255) default NULL,
  `Aircraft_Airspeed` int(11) default NULL,
  `Flight_ID` varchar(10) default NULL,
  `Oil_Type` varchar(50) default NULL,
  `WB_Fields` text,
  `Cleared_By` varchar(10) default NULL,
  PRIMARY KEY  (`aircraft_id`),
  UNIQUE KEY `n_number` (`n_number`)
) ENGINE=InnoDB AUTO_INCREMENT=36 ;


# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_certificates`
#

CREATE TABLE `AircraftScheduling_certificates` (
  `certificate_id` int(11) NOT NULL auto_increment,
  `certificate` varchar(64) default NULL,
  PRIMARY KEY  (`certificate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 ;

#
# Dumping data for table `AircraftScheduling_certificates`
#

INSERT INTO `AircraftScheduling_certificates` VALUES (1, 'Student Pilot');
INSERT INTO `AircraftScheduling_certificates` VALUES (2, 'Recreational Pilot');
INSERT INTO `AircraftScheduling_certificates` VALUES (3, 'Private Pilot');
INSERT INTO `AircraftScheduling_certificates` VALUES (4, 'Commercial Pilot');
INSERT INTO `AircraftScheduling_certificates` VALUES (5, 'Airline Transport Pilot');
INSERT INTO `AircraftScheduling_certificates` VALUES (6, 'Certified Flight Instructor');
INSERT INTO `AircraftScheduling_certificates` VALUES (7, 'Certified Flight Instructor Instrument');
INSERT INTO `AircraftScheduling_certificates` VALUES (8, 'Certified Flight Instructor Multi-Engine');
INSERT INTO `AircraftScheduling_certificates` VALUES (9, 'Certified Flight Instructor Multi-Engine Instrument');
INSERT INTO `AircraftScheduling_certificates` VALUES (10, 'Ground Instructor');

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_config`
#

CREATE TABLE `AircraftScheduling_config` (
  `name` varchar(32) NOT NULL default '',
  `value` varchar(128) default NULL,
  `description` varchar(128) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB;

#
# Dumping data for table `AircraftScheduling_config`
#
INSERT INTO `AircraftScheduling_config` (`name`, `value`, `description`) VALUES ('AircraftScheduling_admin', 'Admin Admin', 'Admin''s name'),
('AircraftScheduling_admin_email', 'Admin.Email@FlyingClub.com', 'Admin''s Email'),
('AircraftScheduling_company', 'New Flying Club', 'Name of the FBO'),
('AircraftScheduling_phone', 'FBO Phone Number', 'FBO''s phone number'),
('changes_prohibited_interval', '0 day', 'Schedule changes not allowed within this amount of time'),
('resolution', '1800', 'The interval on the schedule in seconds. i.e. 1800/60==30 minutes.'),
('morningstarts', '6', 'The first hour on the schedule.  Must be an integer.'),
('eveningends', '20', 'The last hour on the schedule.  Must be an integer.'),
('eveningends_minutes', '30', 'Minutes past the last hour on the schedule.'),
('weekstarts', '0', 'First day of the week; 0==Sunday, 6==Saturday'),
('dateformat', '0', 'Date Format: 0 to show dates as "Jul 10", 1 for "10 Jul"'),
('Signin_Attempts', '10', 'Number of sign in attempts before disabling account'),
('Activity_Timeout', '300', 'Number of seconds of inactivity before signing off the user'),
('Default_Instructor_Rate', '22.00', 'Default hourly rate for instructors'),
('Logon_Weather_Map_Link', 'http://www.wx.com/partnership/miniradar.cfm?zip=35802', 'Link to display weather map on logon page'),
('Logon_Weather_Link', 'http://www.wx.com/myweather.cfm?ZIP=35802', 'Link to page when clicking on logon page weather map'),
('Logon_Weather_Credit', 'Powered by Meteorlogix', 'Credit for logon weather information.'),
('Timezone_Server', '-5', 'Server timezone hours from GMT'),
('Timezone_Local', '-6', 'Local timezone hours from GMT'),
('Number_Repeat_Days', '30', 'Maximum days allowed in a repeating schedule'),
('User_Must_Login', '1', 'Set to 1 to force user to login to view schedule'),
('MaxJournalDays', '120 days', 'Amount of time to retain journal entries'),
('Number_of_Check_in_Copies', '2', 'Number of copies of checkin sheet to print'),
('Number_of_Check_out_Copies', '1', 'Number of checkout copies to print'),
('Number_of_Fault_Record_Copies', '0', 'Number of fault records to print'),
('Lock_Combination', '1234', 'Lock combination'),
('Checkout_Waiver_Text', 'I HAVE READ THE TRANSITORY SECTION OF THE PIF PRIOR TO THIS FLIGHT & THE PERMANENT SECTION OF THE PIF WITHIN THE PAST YEAR & I AM NOT GROUNDED. I AM CURRENT AS REQUIRED FOR THIS AIRCRAFT & ENROUTE CONDITIONS. I HAVE CHECKED WEATHER & NOTAM INFORMATION FOR MY ROUTE OF FLIGHT. I HAVE READ & UNDERSTAND ALL PROVISIONS IN THE FARS, ARS & SOP. THE AIRCRAFT IS WITHIN WEIGHT AND BALANCE LIMITS AS SHOWN ABOVE. WAIVER(S) IS (ARE) ON FILE FOR PILOT/CFI. PASSENGER WAIVERS ARE SIGNED AND ATTACHED TO THIS FORM.  FALSE REPESENTATION OF PILOT BY SIGNING  MAKES THE PILOT SUBJECT TO FINE, SUSPENSION AND/ OR TERMINATION AS APPROPRIATE', 'Waiver text to print on the checkout form'),
('Signature_Block_Text', 'FBO Manager\r\nMANAGER, \r\nNew Flying Club', 'Signature block text for reports'),
('CombinationLock_Display_Seconds', '20', 'Number of seconds to display lock combination'),
('Ground_Instruction_Amount', '22', 'Amount to charge for ground instruction'),
('Private_Instruction_Amount', '22', 'Amount to charge for private instruction'),
('Instrument_Instruction_Amount', '22', 'Amount to charge for instrument instruction'),
('Commercial_Instruction_Amount', '22', 'Amount to charge for commercial instruction'),
('CFI_Instruction_Amount', '22', 'Amount to charge for CFI instruction'),
('CFII_Instruction_Amount', '22', 'Amount to charge for CFII instruction'),
('Inventory_Up_Charge_Percent', '34', 'Percent to add to wholesale price for retail price'),
('Fuel_Reimbursement', '3.40', 'Amount to reimburse for cross-country fuel (0 to use actual cost)'),
('Fuel_Charge', '3.36', 'Amount to charge for fuel'),
('Fuel_Cost', '3.26', 'Cost of the fuel'),
('Oil_Charge', '3.21', 'Amount to charge for oil'),
('FBOLocation', 'FBOLocation', 'Location of the FBO'),
('MaxScheduleDaysAllowed', '30', 'Maximum number of days in the future that a schedule may be created (0 for unlimited)');

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_entry`
#

CREATE TABLE `AircraftScheduling_entry` (
  `entry_id` int(11) NOT NULL auto_increment,
  `start_time` int(11) NOT NULL default '0',
  `end_time` int(11) NOT NULL default '0',
  `entry_type` int(11) NOT NULL default '0',
  `repeat_id` int(11) default NULL,
  `resource_id` int(11) default NULL,
  `timestamp` timestamp NOT NULL,
  `create_time` int(11) default NULL,
  `create_by` varchar(25) NOT NULL default '',
  `person_id` int(11) default NULL,
  `name` varchar(80) NOT NULL default '',
  `phone_number` varchar(25) default NULL,
  `type` char(1) NOT NULL default 'E',
  `description` text,
  PRIMARY KEY  (`entry_id`),
  KEY `idxOpenfboStartTime` (`start_time`),
  KEY `idxOpenfboEndTime` (`end_time`)
) ENGINE=InnoDB AUTO_INCREMENT=33577 ;

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_equipment_codes`
#

CREATE TABLE `AircraftScheduling_equipment_codes` (
  `code_id` int(11) NOT NULL auto_increment,
  `code` char(1) default NULL,
  `description` varchar(128) default NULL,
  PRIMARY KEY  (`code_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 ;

#
# Dumping data for table `AircraftScheduling_equipment_codes`
#

INSERT INTO `AircraftScheduling_equipment_codes` VALUES (1, 'X', 'No Transponder');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (2, 'G', 'GPS (approach), transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (3, 'I', 'RNAV/LORAN/INS, transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (4, 'A', 'DME, transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (5, 'P', 'TACAN, transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (6, 'E', 'FMS (dual), transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (7, 'F', 'FMS (single), transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (8, 'R', 'RNP, transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (9, 'W', 'RNP/RVSM, transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (10, 'U', 'transponder, mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (11, 'C', 'RNAV/LORAN/INS, transponder, no mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (12, 'B', 'DME, transponder, no mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (13, 'N', 'TACAN, transponder, no mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (14, 'T', 'transponder, no mode C');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (15, 'Y', 'RNAV/LORAN/INS, no transponder');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (16, 'D', 'DME, no transponder');
INSERT INTO `AircraftScheduling_equipment_codes` VALUES (17, 'M', 'TACAN, no transponder');

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_instructors`
#

CREATE TABLE `AircraftScheduling_instructors` (
  `instructor_id` int(11) NOT NULL auto_increment,
  `hourly_cost` decimal(6,2) default NULL,
  `lesson_fee` decimal(6,2) default NULL,
  `description` text,
  `picture` varchar(64) default NULL,
  `resource_id` int(11) NOT NULL default '0',
  `person_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`instructor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 ;


# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_journal`
#

CREATE TABLE `AircraftScheduling_journal` (
  `journal_id` int(11) NOT NULL auto_increment,
  `timestamp` int(14) NOT NULL default '0',
  `username` varchar(20) NOT NULL default '''''',
  `description` text NOT NULL,
  PRIMARY KEY  (`journal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30161 ;

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_make`
#

CREATE TABLE `AircraftScheduling_make` (
  `make_id` int(11) NOT NULL auto_increment,
  `make` varchar(32) default NULL,
  `hourly_cost` decimal(6,2) default NULL,
  `rental_fee` decimal(6,2) default NULL,
  PRIMARY KEY  (`make_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 ;

#
# Dumping data for table `AircraftScheduling_make`
#

INSERT INTO `AircraftScheduling_make` VALUES (1, 'Cessna', '0.00', '0.00');
INSERT INTO `AircraftScheduling_make` VALUES (2, 'Piper', '0.00', '0.00');
INSERT INTO `AircraftScheduling_make` VALUES (3, 'Simulator', '0.00', '0.00');
INSERT INTO `AircraftScheduling_make` VALUES (4, 'Tampico', '0.00', '0.00');
INSERT INTO `AircraftScheduling_make` VALUES (6, 'Beech', '0.00', '0.00');
INSERT INTO `AircraftScheduling_make` VALUES (7, 'Grumman', '0.00', '0.00');
INSERT INTO `AircraftScheduling_make` VALUES (8, 'Mooney', NULL, NULL);

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_model`
#

CREATE TABLE `AircraftScheduling_model` (
  `model_id` int(11) NOT NULL auto_increment,
  `model` varchar(32) default NULL,
  `hourly_cost` decimal(6,2) default NULL,
  `rental_fee` decimal(6,2) default NULL,
  PRIMARY KEY  (`model_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 ;

#
# Dumping data for table `AircraftScheduling_model`
#

INSERT INTO `AircraftScheduling_model` VALUES (1, 'C152', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (2, 'C172', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (3, 'C182', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (4, 'C182R', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (5, 'P28R', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (7, 'P28A', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (8, 'TB-9', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (9, 'PCATD', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (11, 'Duchess', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (12, 'AA1', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (13, 'BE19', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (14, 'BE23', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (15, 'PA24', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (16, 'SIM', '0.00', '0.00');
INSERT INTO `AircraftScheduling_model` VALUES (18, 'M20J', '0.00', '0.00');

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_notices`
#

CREATE TABLE `AircraftScheduling_notices` (
  `notice_id` int(11) NOT NULL auto_increment,
  `Notices` text NOT NULL,
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`notice_id`)
) ENGINE=InnoDB;

#
# Dumping data for table `AircraftScheduling_notices`
#
INSERT INTO `AircraftScheduling_notices` (`Notices`, `timestamp`) VALUES (' ', 20040330185710);

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_person`
#

CREATE TABLE `AircraftScheduling_person` (
  `person_id` int(11) NOT NULL auto_increment,
  `first_name` varchar(16) default '',
  `middle_name` varchar(16) default '',
  `last_name` varchar(24) default '',
  `title` varchar(24) default '',
  `email` varchar(64) default '',
  `username` varchar(20) NOT NULL default '',
  `password` varchar(32) NOT NULL default 'hackme',
  `user_level` bigint(20) NOT NULL default '0',
  `counter` int(11) default '0',
  `last_login` timestamp NOT NULL,
  `address1` varchar(128) default NULL,
  `address2` varchar(128) default NULL,
  `city` varchar(64) default NULL,
  `state` char(2) default NULL,
  `zip` bigint(20) default NULL,
  `phone_number` varchar(25) default NULL,
  `SSN` varchar(15) default NULL,
  `Organization` varchar(30) default NULL,
  `Work_Ext` varchar(10) default NULL,
  `Home_Phone` varchar(20) default NULL,
  `Dues_Amount` decimal(20,4) default NULL,
  `Ground_Instruction_Amount` decimal(20,4) default NULL,
  `Private_Instruction_Amount` decimal(20,4) default NULL,
  `Instrument_Instruction_Amount` decimal(20,4) default NULL,
  `Commercial_Instruction_Amount` decimal(20,4) default NULL,
  `CFI_Instruction_Amount` decimal(20,4) default NULL,
  `CFII_Instruction_Amount` decimal(20,4) default NULL,
  `Contract_Number` varchar(50) default NULL,
  `Notify_First_Name` varchar(20) default NULL,
  `Notify_Middle_Initial` char(2) default NULL,
  `Notify_Last_Name` varchar(20) default NULL,
  `Notify_Relation` varchar(20) default NULL,
  `Notify_Address` varchar(50) default NULL,
  `Notify_City` varchar(20) default NULL,
  `Notify_State` char(2) default NULL,
  `Notify_Zip` varchar(5) default NULL,
  `Notify_Phone1` varchar(20) default NULL,
  `Notify_Phone2` varchar(20) default NULL,
  `Contract_Expiration_Date` varchar(50) default NULL,
  `Rules_Field` text,
  `Member_Notes` text,
  `Credit_Card_Number` varchar(50) default NULL,
  `Credit_Card_Expiration` datetime default NULL,
  `Manager_Message` text,
  `Membership_Date` datetime default NULL,
  `Resign_Date` datetime default NULL,
  `Clearing_Authority` tinyint(4) default NULL,
  `Password_Expires_Date` datetime default NULL,
  `Allow_Phone_Number_Display` tinyint(4) default '1',
  `OldUsername` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`person_id`),
  UNIQUE KEY `username` (`username`),
  KEY `idxOpenfboUserName` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1420 ;

#
# Dumping data for table `AircraftScheduling_person`
#

INSERT INTO `AircraftScheduling_person` VALUES (1, 'admin', '', 'admin', '', 'email@somewhere.net', 'admin', 'admin', 5, 480, 20050214232733, '', '', '', '', 0, '256-555-1212', '0', '', NULL, '', '0.0000', '0.0000', '0.0000', '0.0000', '0.0000', '0.0000', '0.0000', '', '', '', '', '', '', '', '', '', '', '', '', 'Birth_Date,01-Jan-1900;Manager_Grounding,FALSE;Pilot_Certificate,1;Pre_Solo_Written_Exam,01-Jan-1900;Safety_Meeting,01-Jan-1900;Solo_Endorsement,01-Jan-1900;Student_Pilot_Certificate,01-Jan-1900;Waiver,01-Jan-1900;Rating,Student;Member_Status,Active;Medical_Class,1;Medical_Date,01-Jan-1900;C182R_Written_Test,01/01/1900;C182R_Flight_Test,01/01/1900;C182R_Initial_Checkout,01/01/1900', '', '0', '2004-08-01 00:00:00', '', '0000-00-00 00:00:00', '1900-01-01 00:00:00', 0, NULL, 1, 0);

# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_pilot_certificates`
#

CREATE TABLE `AircraftScheduling_pilot_certificates` (
  `pilot_certificate_id` int(11) NOT NULL auto_increment,
  `pilot_id` int(11) default NULL,
  `certificate_id` int(11) default NULL,
  PRIMARY KEY  (`pilot_certificate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;

#
# Dumping data for table `AircraftScheduling_pilot_certificates`
#


# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_repeat`
#

CREATE TABLE `AircraftScheduling_repeat` (
  `repeat_id` int(11) NOT NULL auto_increment,
  `start_time` int(11) NOT NULL default '0',
  `end_time` int(11) NOT NULL default '0',
  `rep_type` int(11) NOT NULL default '0',
  `end_date` int(11) NOT NULL default '0',
  `rep_opt` varchar(32) NOT NULL default '',
  `resource_id` int(11) NOT NULL default '1',
  `timestamp` timestamp NOT NULL,
  `create_by` varchar(25) NOT NULL default '',
  `person_id` int(11) default NULL,
  `name` varchar(80) NOT NULL default '',
  `phone_number` varchar(25) default NULL,
  `type` char(1) NOT NULL default 'E',
  `description` text,
  PRIMARY KEY  (`repeat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=410 ;


# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_required_ratings`
#

CREATE TABLE `AircraftScheduling_required_ratings` (
  `required_rating_id` int(11) NOT NULL auto_increment,
  `aircraft_id` int(11) default NULL,
  `rating_id` int(11) default NULL,
  PRIMARY KEY  (`required_rating_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;

#
# Dumping data for table `AircraftScheduling_required_ratings`
#


# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_resource`
#

CREATE TABLE `AircraftScheduling_resource` (
  `resource_id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL default '0',
  `schedulable_id` int(11) default NULL,
  `resource_name` varchar(40) NOT NULL default '''''',
  `resource_make` varchar(32) NOT NULL default '''''',
  `resource_model` varchar(32) NOT NULL default '''''',
  PRIMARY KEY  (`resource_id`),
  KEY `idxResourceName` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 ;


# --------------------------------------------------------

#
# Table structure for table `AircraftScheduling_schedulable`
#

CREATE TABLE `AircraftScheduling_schedulable` (
  `schedulable_id` int(11) NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `display_page` varchar(64) default NULL,
  `schedulable` tinyint(1) default '1',
  PRIMARY KEY  (`schedulable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 ;

#
# Dumping data for table `AircraftScheduling_schedulable`
#

INSERT INTO `AircraftScheduling_schedulable` VALUES (1, 'Aircraft', 'DisplayAircraftInfo.php', 1);
INSERT INTO `AircraftScheduling_schedulable` VALUES (2, 'Instructor', 'DisplayInstructorInfo.php', 1);

# --------------------------------------------------------

#
# Table structure for table `Categories`
#

CREATE TABLE `Categories` (
  `Name` char(50) default NULL,
  `GLAC` char(50) default NULL,
  `Can_Be_Changed` tinyint(4) default NULL,
  `Record_ID` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`Record_ID`),
  KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 ;

#
# Dumping data for table `Categories`
#

INSERT INTO `Categories` VALUES ('MERCHANDISE SALES', 'C 301-63', 1, 1);
INSERT INTO `Categories` VALUES ('INSURANCE PREMIUM', 'C 140-00', 1, 2);
INSERT INTO `Categories` VALUES ('MAINTENANCE', 'C 501-63', 1, 3);
INSERT INTO `Categories` VALUES ('SCHOOL KITS', 'C 301-63', 1, 4);
INSERT INTO `Categories` VALUES ('FUEL/OIL PUR. RSAFA', 'C 301-63', 1, 5);
INSERT INTO `Categories` VALUES ('FINE ASSESSMENT', 'C 504', 1, 6);
INSERT INTO `Categories` VALUES ('CHECK OUT FEES', 'C 501-63', 1, 7);
INSERT INTO `Categories` VALUES ('FLIGHT TIME', 'C 504-63', 0, 8);
INSERT INTO `Categories` VALUES ('CASH OVERAGE', 'C 511-63', 1, 9);
INSERT INTO `Categories` VALUES ('FAA TEST FEES', 'C 501-63', 1, 10);
INSERT INTO `Categories` VALUES ('MISC . ACCT/REC. PAID', 'C 140-00', 1, 11);
INSERT INTO `Categories` VALUES ('DUES & INTIATION FEES', 'C 509', 1, 12);
INSERT INTO `Categories` VALUES ('FUEL REIMBURSEMENT', 'D 664-63', 0, 13);
INSERT INTO `Categories` VALUES ('MISC. ACCT/REC.', 'D 140-00', 1, 14);
INSERT INTO `Categories` VALUES ('AMERICAN EXPRESS', 'D 139-00', 1, 15);
INSERT INTO `Categories` VALUES ('MASTER CARD/VISA', 'D 110-00', 0, 16);
INSERT INTO `Categories` VALUES ('CASH SHORTAGE', 'D 739-G1', 1, 17);
INSERT INTO `Categories` VALUES ('GROUND/INSTURMENT SCHOOL FEES', 'C 501-63', 1, 18);
INSERT INTO `Categories` VALUES ('EQUIPMENT MAINTENCE', 'C 658', 1, 19);
INSERT INTO `Categories` VALUES ('MAINTENCE SUPPLIES', 'C-160', 1, 20);
INSERT INTO `Categories` VALUES ('FLY IN', '501-63', 1, 21);
INSERT INTO `Categories` VALUES ('TIEDOWN FEE', 'C504-00', 1, 22);
INSERT INTO `Categories` VALUES ('RECREATION INCOME', 'C 501-63', 1, 23);

# --------------------------------------------------------

#
# Table structure for table `Charges`
#

CREATE TABLE `Charges` (
  `charge_id` int(11) NOT NULL auto_increment,
  `KeyCode` char(50) default NULL,
  `Date` datetime default NULL,
  `Part_Number` char(50) default NULL,
  `Part_Description` char(50) default NULL,
  `Quantity` float default NULL,
  `Price` float default NULL,
  `Total_Price` float default NULL,
  `Category` char(50) default NULL,
  `Unit_Price` decimal(20,4) default NULL,
  PRIMARY KEY  (`charge_id`),
  KEY `KeyCode` (`KeyCode`)
) ENGINE=InnoDB;


# --------------------------------------------------------

#
# Table structure for table `CurrencyFields`
#

CREATE TABLE `CurrencyFields` (
  `Currency_Field_Name` char(50) default NULL,
  `Currency_Field_Type` char(50) default NULL,
  `Student` tinyint(4) default NULL,
  `Private_Under_200` tinyint(4) default NULL,
  `Private_Over_200` tinyint(4) default NULL,
  `Instrument` tinyint(4) default NULL,
  `CFI` tinyint(4) default NULL,
  KEY `Currency_Field_Name` (`Currency_Field_Name`)
) ENGINE=InnoDB;

#
# Dumping data for table `CurrencyFields`
#

INSERT INTO `CurrencyFields` VALUES ('Waiver', 'Date', 1, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Manager_Grounding', 'Boolean', 1, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Safety_Meeting', 'Date', 1, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Student_Pilot_Certificate', 'Date', 1, 0, 0, 0, 0);
INSERT INTO `CurrencyFields` VALUES ('Pre_Solo_Written_Exam', 'Date', 1, 0, 0, 0, 0);
INSERT INTO `CurrencyFields` VALUES ('Solo_Endorsement', 'Date', 1, 0, 0, 0, 0);
INSERT INTO `CurrencyFields` VALUES ('FAR_Local_Procedures_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('CFI_Written_Test', 'Date', 0, 0, 0, 0, 1);
INSERT INTO `CurrencyFields` VALUES ('Instrument_Written_Test', 'Date', 0, 0, 0, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Instrument_Comp_Check', 'Date', 0, 0, 0, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Annual_Flight_Review', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Medical_Date', 'Date', 1, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Birth_Date', 'Date', 1, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Pilot_Certificate', 'Integer', 1, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Medical_Class', 'Integer', 1, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Rating', 'String', 1, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Day_VFR_Prof_Check', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Night_VFR_Prof_Check', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Biannual_Flight_Review', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('Temp_Certificate_Date', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"AA1"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C152"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C152"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C172"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C172"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C152"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C172"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C182"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"P28A"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"P28A"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C182R"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"P28R"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C182"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C182"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C182R"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"P28A"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"C182R"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"P28R"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"BE23"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"BE23"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"BE23"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"P28R"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"BE19"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"M20J"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"M20J"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"M20J"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"BE19"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"BE19"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"AA1"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"AA1"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"PA24"_Written_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"PA24"_Flight_Test', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('"PA24"_Initial_Checkout', 'Date', 0, 1, 1, 1, 1);
INSERT INTO `CurrencyFields` VALUES ('TSA_Citizenship_Proof', 'Date', 1, 1, 1, 1, 1);

# --------------------------------------------------------

#
# Table structure for table `CurrencyRules`
#

CREATE TABLE `CurrencyRules` (
  `ID` int(11) default NULL,
  `Item` text,
  `Pass_Criteria` text,
  `Expires_Month_End` tinyint(4) default NULL,
  `Student` varchar(50) default NULL,
  `Private_Under_200` varchar(50) default NULL,
  `Private_Over_200` varchar(50) default NULL,
  `Instrument` varchar(50) default NULL,
  `CFI` varchar(50) default NULL,
  KEY `ID` (`ID`)
) ENGINE=InnoDB;

#
# Dumping data for table `CurrencyRules`
#

INSERT INTO `CurrencyRules` VALUES (34, 'BE23 Flight Test', '"BE23"_Flight_Test + 1Y >= Now | "C152"_Flight_Test + 1Y >= Now | "C172"_Flight_Test + 1Y >= Now | "P28A"_Flight_Test + 1Y >= Now | "C182"_Flight_Test + 1Y >= Now | "P28R"_Flight_Test + 1Y >= Now | "C182R"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (34, 'TSA Citizenship Proof', 'TSA_Citizenship_Proof + 100Y>= Now', 0, 'Information', 'Information', 'Information', 'Information', 'Information');
INSERT INTO `CurrencyRules` VALUES (34, 'BE23 Initial Checkout', '"BE23"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (34, 'BE23 Written Test', '"BE23"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (33, 'C152 Initial Checkout', '"C152"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (33, 'C152 Flight Test', '"C152"_Flight_Test + 1Y >= Now | "C172"_Flight_Test + 1Y >= Now | "P28A"_Flight_Test + 1Y >= Now | "C182"_Flight_Test + 1Y >= Now | "P28R"_Flight_Test + 1Y >= Now | "C182R"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (33, 'C152 Written Test', '"C152"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (32, 'C172 Initial Checkout', '"C172"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (32, 'C172 Flight Test', '"C172"_Flight_Test + 1Y >= Now | "P28A"_Flight_Test + 1Y >= Now | "C182"_Flight_Test + 1Y >= Now | "P28R"_Flight_Test + 1Y >= Now | "C182R"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (32, 'C172 Written Test', '"C172"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (31, 'P28A Flight Test', '"P28A"_Flight_Test + 1Y >= Now | "C182"_Flight_Test + 1Y >= Now | "P28R"_Flight_Test + 1Y >= Now | "C182R"_Flight_Test + 1Y >= Now | "C172"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (31, 'P28A Written Test', '"P28A"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (31, 'P28A Initial Checkout', '"P28A"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (28, 'C182R Flight Test', '"C182R"_Flight_Test + 1Y >= Now | "P28R"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (29, 'P28R Written Test', '"P28R"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (29, 'P28R Initial Checkout', '"P28R"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (29, 'P28R Flight Test', '"P28R"_Flight_Test + 1Y >= Now | "C182R"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (1, 'Waiver', 'Waiver + 1y >= Now', 0, 'Required to solo', 'Required to solo', 'Required to solo', 'Required to solo', 'Required to solo');
INSERT INTO `CurrencyRules` VALUES (2, 'Manager Grounding', 'Manager_Grounding = FALSE', 0, 'Required to fly', 'Required to fly', 'Required to fly', 'Required to fly', 'Required to fly');
INSERT INTO `CurrencyRules` VALUES (3, 'Safety Meeting', 'Safety_Meeting + Safety_Meeting_Days >= Now', 0, 'Required to solo', 'Required to solo', 'Required to solo', 'Required to solo', 'Required to solo');
INSERT INTO `CurrencyRules` VALUES (4, 'Student Pilot Certificate', 'Student_Pilot_Certificate + 2Y >= Now', 1, 'Required to solo', 'No', 'No', 'No', 'No');
INSERT INTO `CurrencyRules` VALUES (5, 'Pre-Solo Written Exam', 'Pre_Solo_Written_Exam + 2Y >= Now', 1, 'Required to solo', 'No', 'No', 'No', 'No');
INSERT INTO `CurrencyRules` VALUES (6, 'Solo Endorsement', 'Solo_Endorsement + 90d >= Now', 0, 'Required to solo', 'No', 'No', 'No', 'No');
INSERT INTO `CurrencyRules` VALUES (8, 'FAR/Local Procedures Test', 'FAR_Local_Procedures_Test + 1Y >= Now', 1, 'No', 'Required to solo', 'Required to solo', 'Required to solo', 'Required to solo');
INSERT INTO `CurrencyRules` VALUES (9, 'Instrument Written Test', 'Instrument_Written_Test + 1Y >= Now', 1, 'No', 'No', 'No', 'Required to fly instrument', 'Required to fly instrument');
INSERT INTO `CurrencyRules` VALUES (10, 'CFI Written Test', 'CFI_Written_Test + 1Y >= Now', 1, 'No', 'No', 'No', 'No', 'Required to solo');
INSERT INTO `CurrencyRules` VALUES (11, '1 hr/3 lnds 60 days - day Cur', '(Within 60d (Flight_Hours >= 1 & Landings >= 3)) | (Day_VFR_Prof_Check + 60d >= Now) | (Night_VFR_Prof_Check + 60d >= Now) | (Annual_Flight_Review + 60d >= Now)', 0, 'No', 'Required to solo', 'No', 'No', 'No');
INSERT INTO `CurrencyRules` VALUES (12, '1 hr/3 lnds 60 days - night Cur', '(Within 60d (Night_Flight_Hours >= 1 & Night_Landings >= 3)) | (Night_VFR_Prof_Check + 60d >= Now)', 0, 'No', 'Required to solo', 'No', 'No', 'No');
INSERT INTO `CurrencyRules` VALUES (13, '1 hr/3 lnds 90 days - day Cur', '(Within 90d (Flight_Hours >= 1 & Landings >= 3)) | (Day_VFR_Prof_Check + 90d >= Now) | (Night_VFR_Prof_Check + 90d >= Now) | (Annual_Flight_Review + 90d >= Now)', 0, 'No', 'No', 'Required to solo', 'Required to solo', 'Required to solo');
INSERT INTO `CurrencyRules` VALUES (14, '1 hr/3 lnds 90 days - night Cur', '(Within 90d (Night_Flight_Hours >= 1 & Night_Landings >= 3)) | (Night_VFR_Prof_Check + 90d >= Now)', 0, 'No', 'No', 'Required to solo', 'Required to solo', 'Required to solo');
INSERT INTO `CurrencyRules` VALUES (15, 'Instrument Comp Check', 'Instrument_Comp_Check + 6m >= Now', 1, 'No', 'No', 'No', 'Information', 'Information');
INSERT INTO `CurrencyRules` VALUES (16, 'Instrument Currency', 'Within 6m (Holding_Procedures > 0 & Navigation_Intercepts > 0 & Instrument_Approaches >= 6 | Instrument_Comp_Check + 6m >= Now)', 0, 'No', 'No', 'No', 'Required to fly instrument', 'Required to fly instrument');
INSERT INTO `CurrencyRules` VALUES (17, 'Annual Flight Review', 'Annual_Flight_Review + 1y >= Now', 1, 'No', 'Required to Solo', 'Required to solo', 'Required to solo', 'Required to solo');
INSERT INTO `CurrencyRules` VALUES (18, 'Medical CL II', '(Medical_Class <> 0 -> Medical_Date + 1y >= Now) | (Medical_Class = 0 -> Medical_Date > Now) | (Medical_Class <> 0 & Medical_Date - Birth_Date <= (40 * 365.25) -> Medical_Date + 3y >= Now) | (Medical_Class <> 0 & Medical_Date - Birth_Date > (40 * 365.25) -> Medical_Date + 2y >= Now)', 1, 'No', 'No', 'No', 'No', 'Required to solo');
INSERT INTO `CurrencyRules` VALUES (19, 'Medical CL III', '(Medical_Class = 0 -> Medical_Date > Now) | (Medical_Class <> 0 & Medical_Date - Birth_Date <= (40 * 365.25) -> Medical_Date + 3y >= Now) | (Medical_Class <> 0 & Medical_Date - Birth_Date > (40 * 365.25) -> Medical_Date + 2y >= Now)', 1, 'Required to solo', 'Required to solo', 'Required to solo', 'Required to solo', 'No');
INSERT INTO `CurrencyRules` VALUES (7, 'Dual Time Within 30 Days', 'Within 30d (Dual_Time > 0)', 0, 'Required to solo', 'no', 'no', 'no', 'no');
INSERT INTO `CurrencyRules` VALUES (30, 'C182 Flight Test', '"C182"_Flight_Test + 1Y >= Now | "P28R"_Flight_Test + 1Y >= Now | "C182R"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (30, 'C182 Initial Checkout', '"C182"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (30, 'C182 Written Test', '"C182"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (28, 'C182R Initial Checkout', '"C182R"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (28, 'C182R Written Test', '"C182R"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (27, 'M20J Flight Test', '"M20J"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (27, 'M20J Written Test', '"M20J"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (27, 'M20J Initial Checkout', '"M20J"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (26, 'BE19 Initial Checkout', '"BE19"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (26, 'BE19 Flight Test', '"BE19"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (26, 'BE19 Written Test', '"BE19"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (25, 'AA1 Flight Test', '"AA1"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (25, 'AA1 Initial Checkout', '"AA1"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (25, 'AA1 Written Test', '"AA1"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (24, 'PA24 Flight Test', '"PA24"_Flight_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (24, 'PA24 Written Test', '"PA24"_Written_Test + 1Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');
INSERT INTO `CurrencyRules` VALUES (24, 'PA24 Initial Checkout', '"PA24"_Initial_Checkout + 100Y >= Now', 1, 'No', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO', 'REQUIRED TO SOLO');

# --------------------------------------------------------

#
# Table structure for table `Flight`
#

CREATE TABLE `Flight` (
  `flight_id` int(11) NOT NULL auto_increment,
  `Keycode` varchar(10) default NULL,
  `Date` datetime default NULL,
  `Aircraft` varchar(10) default NULL,
  `model_id` int(11) default NULL,
  `Begin_Hobbs` float default NULL,
  `End_Hobbs` float default NULL,
  `Hobbs_Elapsed` float default NULL,
  `Aircraft_Rate` float default NULL,
  `Aircraft_Cost` float default NULL,
  `Begin_Tach` float default NULL,
  `End_Tach` float default NULL,
  `Day_Time` float default NULL,
  `Night_Time` float default NULL,
  `Instruction_Type` varchar(20) default NULL,
  `Dual_Time` float default NULL,
  `Dual_PP_Time` float default NULL,
  `Instruction_Rate` float default NULL,
  `Instructor_Charge` float default NULL,
  `Instructor_Keycode` varchar(10) default NULL,
  `Student_Keycode` varchar(10) default NULL,
  `Day_Landings` int(11) default NULL,
  `Night_Landings` int(11) default NULL,
  `Navigation_Intercepts` int(11) default NULL,
  `Holding_Procedures` int(11) default NULL,
  `Instrument_Approach` int(11) default NULL,
  `Fuel_Cost` float default NULL,
  `Local_Fuel` float default NULL,
  `Local_Fuel_Cost` float default NULL,
  `Cross_Country_Fuel` float default NULL,
  `Cross_Country_Fuel_Credit` float default NULL,
  `Oil` float default NULL,
  `Oil_Rate` float default NULL,
  `Oil_Cost` float default NULL,
  `Owner_Rate` float default NULL,
  `Owner_Reimbursement` float default NULL,
  `Cleared_By` varchar(10) default NULL,
  PRIMARY KEY  (`flight_id`),
  KEY `Aircraft` (`Aircraft`),
  KEY `Cleared_By` (`Cleared_By`),
  KEY `Date` (`Date`),
  KEY `Instructor_Keycode` (`Instructor_Keycode`),
  KEY `Keycode` (`Keycode`),
  KEY `Student_Keycode` (`Student_Keycode`),
  KEY `Type` (`model_id`)
) ENGINE=InnoDB;


# --------------------------------------------------------

#
# Table structure for table `Inventory`
#

CREATE TABLE `Inventory` (
  `inventory_id` int(11) NOT NULL auto_increment,
  `Date` datetime default NULL,
  `Part_Number` char(50) default NULL,
  `Description` char(50) default NULL,
  `Unit_Price` decimal(20,4) default NULL,
  `Retail_Price` decimal(20,4) default NULL,
  `Quantity_In_Stock` int(11) default NULL,
  `Reorder_Quantity` int(11) default NULL,
  `Position` char(50) default NULL,
  `Category` char(50) default NULL,
  `Inventory_Type` char(25) default NULL,
  PRIMARY KEY  (`inventory_id`),
  KEY `Part_Number` (`Part_Number`)
) ENGINE=InnoDB;


# --------------------------------------------------------

#
# Table structure for table `Safety_Meeting`
#

CREATE TABLE `Safety_Meeting` (
  `Next_Safety_Meeting` datetime default NULL,
  `Last_Safety_Meeting` datetime default NULL,
  `Safety_Meeting_Expiration_Days` char(50) default NULL,
  `Record_ID` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`Record_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 ;

#
# Dumping data for table `Safety_Meeting`
#

INSERT INTO `Safety_Meeting` VALUES ('2005-03-16 00:00:00', '2004-12-16 00:00:00', '90d', 2);

# --------------------------------------------------------

#
# Table structure for table `Squawks`
#

CREATE TABLE `Squawks` (
  `squawk_id` int(11) NOT NULL auto_increment,
  `Aircraft` varchar(10) default NULL,
  `KeyCode` varchar(50) default NULL,
  `Date` datetime default NULL,
  `Repair_Date` varchar(12) default NULL,
  `Grounding` tinyint(4) default NULL,
  `Description` text,
  `Repair_Description` text,
  `Initial_Tach` int(11) default NULL,
  `Repair_Tach` int(11) default NULL,
  `Mechanic` varchar(50) default NULL,
  PRIMARY KEY  (`squawk_id`),
  KEY `Aircraft` (`Aircraft`),
  KEY `KeyCode` (`KeyCode`)
) ENGINE=InnoDB;

