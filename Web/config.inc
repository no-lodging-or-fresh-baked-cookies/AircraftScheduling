<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: config.inc,v 1.11 2001/12/20 07:05:57 mbarclay Exp $

###############################################################################
#   AircraftScheduling Configuration File
#   Configure this file for your site.
#   The username and password information are in separate files.
###############################################################################

###############################################################################
#   Modification History:
#        1.00   March 22, 2003 JCovington  Initial release of AircraftScheduling
#                                          from OpenFBO.
#        1.01   April 10, 2003 JCovington  Add instructor scheduling.
#                                          Expand search.php to search make,
#                                          model and resource name.
#                                          Remove estimated cost from view entries.
#                                          Correct timestamp entries to reflect
#                                          actual update and creation times.
#        1.02   April 20, 2003 JCovington  Show aircraft status (up or down), 
#                                          allow aircraft to be marked
#                                          up or down.
#        1.03   May 7, 2003    JCovington  Select make and model after creating
#                                          deleting, or updating an entry. 
#        1.04   May 30, 2003   JCovington  Correct error in scheduling on 15 mins.
#                                          Make the name a mailto entry on the view
#                                          entry screen.
#                                          Add times to right side of table and
#                                          header information to bottom of table in
#                                          day view
#        1.05   June 3, 2003   JCovington  Correct error when instructor ID is
#                                          the same as resourse ID in edit_entry_handler.
#                                          Don't allow the tail number to be changed
#                                          when modifying an aircraft.
#        1.06   August 3, 2003 JCovington  Add contact information to scheduling
#                                          conflict information.
#                                          Link instructor and aircraft that are 
#                                          scheduled together.
#                                          Correct display of aircraft images.
#                                          Allow standby requests.
#        1.07   August 24, 2003 JCovington Disable instructor scheduling when
#                                          scheduling an instructor.
#        1.08   Sept 24, 2003 JCovington   Add a notices box on login screen.
#        1.09   Oct 19, 2003  JCovington   Make the notices box bigger.
#        1.10   Nov 1, 2003   JCovington   Correct daylight savings problem in
#                                          weekly view.
#        1.11   Feb 28, 2004  JCovington   Don't display standby requests on previous
#                                          entries.
#        1.12   Mar 6, 2004   JCovington   General clean up of the user interface
#                                          to make it more consistent across
#                                          all screens.
#                                          Add journaling so that modfications
#                                          to the schedule are tracked.
#        1.13   Mar 14, 2004  JCovington   Add mailto: on copyright line.
#        1.14   Mar 14, 2004  JCovington   Add weather credit to config information.
#        1.15   Mar 15, 2004  JCovington   Correct print preview in report and
#                                          journal view.
#        1.16   Mar 29, 2004  JCovington   Use login name for logging journal
#                                          entries instead of created by name.
#                                          Adjust modification and creation times
#                                          for local timezone.
#                                          Use PHP Calendar class in place of
#                                          mincals.
#                                          Add a popup calendar for date goto buttons.
#                                          Set the maximum repeat period to a 
#                                          value from the config database.
#                                          Updating a phone number in Change User 
#                                          Info updates the phone number in existing
#                                          schedules.
#                                          Add config variable to allow viewing
#                                          the schedule without logging in. 
#                                          Display instructor phone number on
#                                          displayInstructor page.
#                                          Add a last updated time to the notices
#                                          text box on the logon screen.
#                                          Limit image sizes to avoid memory overflow.
#                                          Add a path in the config file for images.
#        1.17   Mar 31, 2004  JCovington   Add FBO name to all email messages.
#        1.18   Apr 2, 2004   JCovington   Add Executive Flight Center to config.
#        1.19   Apr 3, 2004   JCovington   Add session path to save sessions.
#                                          Write session before using header call
#                                          to redirect to another page.
#                                          Enable dynamic GD library loading.
#                                          Use PHPMailer class for sending email.
#                                          Update make and model name when an
#                                          aircraft is updated.
#        1.20   Apr 25, 2004   JCovington  Correct missing resources in new user
#                                          registration.
#                                          Disable loading of gd until errors
#                                          are corrected on ExecutiveFlightCenter
#                                          server.
#        1.21   Apr 27, 2004   JCovington  Enable loading of gd.
#        1.22   May 3, 2004    JCovington  Correct for instructor not found errors.
#                                          General error checking for missing
#                                          information.
#        1.23   Jul 21, 2004   JCovington  Change name of ExecutiveFlightCenter
#                                          mail server.
#        1.24   Aug 1, 2004    JCovington  Add flag to disable user, instructor
#                                          and aircraft management to support 
#                                          the offline program.
#                                          Change font on header line of add_instructor.
#                                          Make description text box hold more
#                                          characters in add_instructor.
#                                          Support checking out, checking in,
#                                          checked out as aircraft status types.
#                                          Force user to pick a new password if
#                                          password is blank or password expiration
#                                          date has been reached, 
#        1.25   Aug 11, 2004    JCovington Add field to disable printing of user's 
#                                          telephone number.
#                                          Correct setting of user's name and phone
#                                          number when changed in change_user_info
#                                          and user_mod.
#                                          Add journal entry when a new user registers.
#                                          Add multiple phone numbers for users.
#                                          Provide a method to update instuctor 
#                                          comments and pictures in update user
#                                          information.
#                                          Correct display of standby requests
#                                          so that any overlaps are shown.
#                                          Show hours until 100 hr, current tach
#                                          current hobbs, annual due on displayAircraft
#                                          page.
#                                          Correct Weekly view with all day scheduled
#                                          does not show last entry of last day of a 
#                                          repeating schedule.
#        1.26   Aug 11, 2004    JCovington Show correct error message when the user
#                                          can't modify the entry. 
#        1.27   Aug 19, 2004    JCovington Correct weekly view showing a half hour
#                                          more than scheduled.
#        1.28   Aug 21, 2004    JCovington Display any manager's message at user login.
#        1.29   Dec 8, 2004     JCovington Add logic to return to the correct page
#                                          after user login, weekly view, etc.
#                                          Correct aircraft and instructor drop
#                                          downs not working with Netscape.
#        1.30   Dec 23, 2004    JCovington Allow checking of currency for users
#                                          that are using the offline program.
#                                          Allow checking of bills for users
#                                          that are using the offline program.
#        1.31   Feb 21, 2005    JCovington Modify config files to store site specific
#                                          information in a separate file.
#        2.00   Jun3 3, 2005    JCovington Add aircraft checkin/checkout, aircraft
#                                          maintenance, inventory and business reports.
#                                          Correct showing of wrong months when an
#                                          invalid date is selected from goto on the
#                                          banner.
#        2.01   July 13, 2005   JCovington Add access to the member information
#                                          from the maintenance account (read only).
#                                          Add PRIVATE to aircraft filter selection.
#        2.02   July 14, 2005   JCovington Add screen to sell one item to multiple
#                                          users.
#                                          Add JavaScript code to validate dates
#                                          in AddModifyMember.php.
#                                          Add select after focus calls in JavaScript
#                                          to select a field that has an error.
#        2.03   July 16, 2005   JCovington Add check in aircraft check-in screen
#                                          so that if the aircraft is already checked
#                                          in, it won't check-in again.
#                                          Add total for dual time in DisplayCharges.
#                                          Add user's name to journal entries in
#                                          aircraft checkout and checkin.
#                                          Add user's name to journal entries in
#                                          AddModifyFlights.
#                                          Add urlencode to display procedures so 
#                                          that & won't cause a problem.
#        2.04   July 20, 2005   JCovington Correct grounding of aircraft during
#                                          checkin due to uninitialized variables.
#                                          Add journal entries when an aircraft
#                                          is grounded.
#                                          Correct incorrect display of standby
#                                          entries in view_entry.php.
#        2.05   July 21, 2005   JCovington Display member names in last, first
#                                          format. Internal names are still
#                                          first last.
#        2.06   July 26, 2005   JCovington Add option to turn off squawk entry.
#                                          Remove extra submit command from 
#                                          javascript code processing submit
#                                          buttons.
#                                          Don't attempt to send email if the
#                                          email address is invalid.
#        2.07   July 29, 2005   JCovington Adjust for timezone in GetDefaultTailNumber
#                                          for AircraftCheckout.php.
#        2.08   Aug 2, 2005     JCovington Limit the number of days that journal
#                                          entries are kept.
#                                          Allow the grounding status of squawks
#                                          to be changed.
#                                          Make the keycode upper case in the
#                                          DAR reports.
#                                          Make the Display screens remember the
#                                          sort order.
#                                          Remove the full SSN from the printed
#                                          reports.
#        2.09   Aug 9, 2005     JCovington Correct formating in help file.
#        2.10   Aug 18, 2005    JCovington Correct instrument comp check not
#                                          displaying expired.
#        2.11   Aug 24, 2005    JCovington Allow the cross-country fuel cost or the
#                                          cross-country fuel gallons to be used for
#                                          reimbursements.
#        2.12   Sep 8, 2005     JCovington Update the currency references in the
#                                          help system.
#                                          Change pre-solo written test currency
#                                          rule to expire after 100 years instead
#                                          of 2.
#        2.13   Sep 13, 2005    JCovington Make JavaScript comparisions case
#                                          insensitive in SellInventory.
#        2.14   Oct 10, 2005    JCovington Correct SQL error after adding a new
#                                          flight.
#                                          Correct grounding after adding a 
#                                          new flight.
#        2.15   Oct 11, 2005    JCovington Correct display of aircraft list
#                                          when aircraft checkin/out is disabled.
#        2.16   Oct 24, 2005    JCovington Correct rounding errors when updating
#                                          database records.
#        2.17   Oct 26, 2005    JCovington Don't let the goback parameter string
#                                          get too long.
#                                          Don't let the filter parameters make
#                                          the goback parameters grow.
#                                          Use the time zone adjustments for the
#                                          times in the check-in/out screens.
#        2.18   Dec 16, 2005    JCovington Correct quotes not allowing a squawk
#                                          to be updated.
#        2.19   Feb 19, 2006    JCovington Display all phone numbers in 
#                                          DisplayInstructorInfo.
#        2.20   Apr 19, 2006    JCovington Allow schedules to not be created
#                                          after a admin specified number of days.
#                                          Allow search to search for users.
#                                          Adjust last login display for time zone.
#                                          Change aircraft checkin and add/modify
#                                          flight screens to have the instructor as
#                                          a drop down box.
#                                          Add an instructor of record field to the
#                                          user information.
#                                          Allow squawk tracking to be enabled
#                                          on a per aircraft basis.
#                                          Allow a user to select the amount of
#                                          recent flight time displayed on the
#                                          CheckCurrency screen.
#        2.21   Apr 24, 2006    JCovington Remove checks for CurrencyRuleResult
#                                          != ClearedToFly and DoesntApply from
#                                          ParseCurrencyRules so that all expired
#                                          items will show expired.
#        2.22   Apr 25, 2006    JCovington Make sure an instructor is picked if
#                                          dual time is logged when checking in
#                                          an aircraft.
#        2.23   May 24, 2006    JCovington Add instructor of record to search
#                                          informaion.
#                                          Correct setting of resigned membership
#                                          date.
#                                          Allow names that have a quote in them
#                                          such as O'Neil.
#                                          Expand the AircraftScheduling_entry
#                                          phone_number value to hold more chars
#                                          so that more phone numbers will fit.
#                                          Show status of each aircraft type
#                                          currency on CheckCurrency page.
#                                          Allow search to search phone number
#                                          fields.
#        2.24   June 20, 2006   JCovington Correct month calculations in displaying
#                                          billing information.
#        2.25   July 9, 2006    JCovington Correct number of repeat days being limited
#                                          for privileged users.
#        2.26   July 12, 2006   JCovington Correct DeleteCurrencyField not 
#                                          correctly deleting the field
#        2.27   July 18, 2006   JCovington Change aircraft currency for students
#                                          to include initial check and written tests.
#                                          Make them information only for now. 
#        2.28   Aug 22, 2006    JCovington Add student name to charges screen if the
#                                          user is an instructor.
#                                          Add license agreement to the help system.
#                                          Split flight time and instructor time in
#                                          DAR reports.
#                                          Add cost of fuel to the aircraft monthly
#                                          billing reports.
#                                          Add birthday to export member information.
#                                          Add export capability for users.
#                                          Allow export of user, aircraft, flight,
#                                          charges, inventory and schedule information.
#        2.29   Sept 4, 2006    JCovington Require aircraft currency for students
#                                          to include initial check and written tests.
#        2.30   Sept 18, 2006   JCovington Correct display of -1 in student name in
#                                          DisplayMemberCharges.
#        2.31   Sept 24, 2006   JCovington Correct syntax error in SELECTS in 
#                                          StringFunctions.inc.
#                                          Show status of all aircraft for student
#                                          pilots in CheckCurrency.inc.
#        2.32   Oct 2, 2006     JCovington Add ALL option back to aircraft monthly
#                                          reports.
#                                          Return to proper screen after deleting
#                                          maintenance inventory item.
#        2.33   Aug 21, 2007    JCovington Allow sendmail as a site specific option for email.
#                                          Remove instructor from the schedule when
#                                          they have resigned or become inactive.
#        2.34   Aug 27, 2007    JCovington Correct showing 0.5 P&P when a 0 has been entered
#                                          in AddModifyFlights.
#        2.35   Sep 4, 2007     JCovington Allow currency prefix to be a configuration item.
#                                          Don't allow repeat entries to be deleted unless
#                                          the repeat entry id is valid.
#        2.36   Mar 26, 2008    JCovington Correct not printing DAR detail report for credit 
#                                          card entries.
#                                          Strip special characters from the login notice
#                                          before saving to the database.
#                                          Remove http: from the href entries so that relative
#                                          file pathnames are used (fixes problem in Safari).
#        2.37   Mar 27, 2008    JCovington Improve search capablility. 
#        2.38   Apr 17, 2008    JCovington Correct problems in search capablility. 
#        2.39   Aug 20, 2008    JCovington Correct length problems in inventory screens. 
#                                          Handle quotes correctly in AddModifyMember.
#        2.40   Sep 1, 2008     JCovington Handle quotes correctly in AddModifyInventory.
#        2.41   Sep 15, 2008    JCovington Handle quotes correctly in AddModifyInventory (again).
#                                          Correct invalid name in DisplayMemberCharges when adding
#                                          a charge for the current user.
#                                          Add support for picture upload to aircraft and instructors.
#                                          Don't display invalid squawks in AddModifyAircraft screen
#                                          when adding new aircraft.
#        2.42   Mar 26, 2011    JCovington Add ability to rename usernames.
#        2.42A  May 28, 2010    D Wyers     /* Updated May 2010 to support Apache 2.2.11, PHP 5.3.0 & MySQL 5.1.36
#                                           * by Dave Wyers "dave dot wyers at gmail dot com"
#                                           *
#                                           * The following global changes were made to make the application
#                                           * compatable with the current PHP and MYSQL versions
#                                           *
#                                           * 1/ HTTP_GET_VARS was replaced with the _GET
#                                           * 2/ HTTP_POST_VARS was replaced with the _POST
#                                           * 3/ split() was replaced with preg_split()
#                                           * 4/ mktime is_dls was removed (Will need to impliment time zones)
#                                           * 5/ the < ? tag was replaced with the < ?php tag
#                                           * 6/ ereg_replace replaced with preg_replace
#                                           * 7/ Commented out the weather lines in global_def.inc
#
#        2.43   Apr 11, 2011    JCovington Remove single quotes from inventory items.
#        2.44   Dec 11, 2011    JCovington Merge Dave Wyers 2.42 baseline with my 2.43 baseline.
#        2.45   Feb 20, 2017    JCovington Change Redstone site config to use a local directory
#                                          for storing session files.
#        2.46   Dec 12, 2018    JCovington Change flight plan information to support ICAO.
#                                          Add journal entry to logon.php when a user is diabled.
#                                          Change hobbs field length to allow > 10000 hours.
#        2.47   Sep 1, 2019     JCovington Correct missing hobbs and aircraft type in aircraft
#                                          checkout sheet.
#        2.48   Mar 28, 2021    JCovington Updates to support PHP version 8.
#        2.49   May 13, 2021    JCovington Correct problems with updates to PHP version 8.
#        2.50   Feb 22, 2026    SCovington Data integrity improvements: InnoDB migration,
#                                          transactions, foreign keys, mutex locking, and
#                                          race condition fixes.
#
#   Items to do:
#   1) Update other language files to match the lang.en file.
#   2) Only session authorization type has been developed and tested.
#
###############################################################################

# version number
$AircraftScheduling_version = "AircraftScheduling 2.50 Feb 22, 2026";

###################
# Database settings
###################
# Which database system: "pgsql"=PostgreSQL, "mysql"=MySQL
# Note: pgsql has not been tested with AircraftScheduling. Let me know if it works.
$dbsys = "mysql";
#$dbsys = "pgsql";

# Uncomment this to NOT use PHP persistent (pooled) database connections:
# $db_nopersist = 1;
# Used for session expiration
# User gets 1 hour of idle time before session expires

########################
# Miscellaneous settings
########################

# Maximum repeating entrys (max needed +1):
$max_rep_entrys = 365 + 1;

# Default report span in days:
$default_report_days = 60;

# Results per page for searching:
$search["count"] = 20;

###############################################
# Authentication settings - read AUTHENTICATION
###############################################
# IP authentication allows any user to create bookings.
$auth["realm"]  = "test realm";
$auth["type"]   = "session";
$auth["prog"]   = "";
$auth["params"] = "";

# The various level two users (can modify other peoples settings)
# By default, only localhost is an administrator.
$auth["admin"][] = "";
$auth["admin"][] = "";
#$auth["admin"][] = "10.0.0.2";
#$auth["admin"][] = "10.0.0.3";

######################################################
# Settings used by auth_session.inc                  #
# auth[type] = "session" will not work without these #
######################################################

$db_session_interval["pgsql"] = "now() + '@ 3600 second'";
$db_session_interval["mysql"] = "now() + 'INTERVAL 3600 SECOND'";
$enablessl = false;

# 'auth_ldap' configuration settings
# Where is the LDAP server
#$ldap_host = "localhost";
# LDAP base distinguish names
#$ldap_base_dn[] = "ou=organizationalunit1,o=organization,c=MY";
#$ldap_base_dn[] = "ou=organizationalunit2,o=organization,c=MY";
#$ldap_base_dn[] = "ou=organizationalunit3,o=organization,c=MY";

##########
# Language
##########

# Change the en below to the code for your language - if 
# there is a language file for it.
# Note: Language files other than lang.en will require updating for
# AircraftScheduling.
include 'lang.en';

# We also want to do locales for Dates/Times etc.
# Use the setlocale() function for this.

# Define the default locale here. For a list of supported
# locales on your system do "locale -a"
setlocale(LC_ALL,'C');

# We attempt to make up a sensible locale from the HTTP_ACCEPT_LANGUAGE
# environment variable. If this doesn't work for you, comment it out
# and assign locale directly.
# If HTTP_ACCEPT_LANGUAGE is a comma-separated list, take the first one.
# $locale = reg_replace(",.*", "", getenv('HTTP_ACCEPT_LANGUAGE'));
$locale = preg_replace("/,.*/", "", getenv('HTTP_ACCEPT_LANGUAGE'));

# These two lines import a language based on what the client
# is using. Comment them out to disable this.
$lang_file = "lang.". strtolower(substr($locale,0,2));
if (file_exists($lang_file)) {include $lang_file;}

#
# Convert locale=xx to xx_XX; this is not correct for some locales???
if (strlen($locale) == 2) {
   $locale = strtolower($locale)."_".strtoupper($locale);
} else {
   $locale = strtolower(substr($locale,0,2))."_".strtoupper(substr($locale,3,2));
}
setlocale(LC_ALL,$locale);

# set the currency prefix for the country
# note that the currency prefix can only be one character long
$CurrencyPrefix = "$";

#############
# Entry Types
#############
# This array maps entry type codes (letters A through J) into descriptions.
# Each type has a color (see TD.x classes in the style sheet AircraftScheduling.css).
#    A=Pink  B=Blue-green  C=Peach  D=Yellow      E=Light blue
#    F=Tan   G=Red         H=Aqua   I=Light green J=Gray
# The value for each type is a short (one word is best) description of the
# type. The values must be escaped for HTML output ("R&amp;D").
# Please leave I and E alone for compatibility.
# If a type's entry is unset or empty, that type is not defined; it will not
# be shown in the day view color-key, and not offered in the type selector
# for new or edited entries.

# $typel["A"] = "A";
# $typel["B"] = "B";
# $typel["C"] = "C";
# $typel["D"] = "D";
# $typel["E"] = $lang["external"];
# $typel["F"] = "F";
# $typel["G"] = "G";
# $typel["H"] = "H";
# $typel["I"] = $lang["internal"];
# $typel["J"] = "J";

#############
# Error Reporting
#############
# Set the error reporting level
# for production use: error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
# for testing use: error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

##########################################
# image root path
##########################################
$ImageRootPath = "../img/";

##########################################
# PHP System Configuration - do not change
##########################################
# Disable magic quoting on database returns:
# set_magic_quotes_runtime(0);

##########################################
# Set to true to enable aircraft checkout
# and maintenance functions. Billing
# functions are enabled as well as
# pilot currency processing.
##########################################
$AllowAircraftCheckout = true;

##########################################
# Set to true to enable aircraft squawk
# control functions. If this is enabled,
# users may enter squawks when checking
# in or out aircraft (if $AllowAircraftCheckout
# is enabled) or when viewing aircraft
# information. If AllowSquawkControl is 
# true, the entering of squawks can be
# disabled on a per aircraft basis.
##########################################
$AllowSquawkControl = true;

##########################################
# Set to true to force the logoff of the
# user after checkin or checkout of an
# aircraft. Note that $AllowAircraftCheckout
# must be true for this to apply.
##########################################
$LogoffAfterCheckInOut = true;

# enable the GD extension library
if(!extension_loaded('gd'))
{ 
    $prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
    !dl($prefix . 'gd2.' . PHP_SHLIB_SUFFIX)
        and die($prefix . 'gd2.' . PHP_SHLIB_SUFFIX . " lib not loaded<BR>"); 
}

?>
