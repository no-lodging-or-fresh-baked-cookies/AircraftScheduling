<?php
//-----------------------------------------------------------------------------
// 
// SiteSpecific.ini
// 
// PURPOSE: AircraftScheduling configuration file.
// 
// PARAMETERS:
//      none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//      Configure this file for your site. This file localizes all passwords
//      into one file.
// -----------------------------------------------------------------------------

# Hostname of database server. For pgsql, can use "" instead of localhost
# to use Unix Domain Sockets instead of TCP/IP.
$db_host = "localhost";

# Database name:
$db_database = "DBName";

# Database login user name:
$db_login = "DBLogin";

# Database login password:
$db_password = "DBLoginPassword";

# email configuration
$email_type = "smtp";       // smtp or sendmail
$email_host = "localhost";          // only used for SMTP
$email_username = "somewhere.com";  // only used for SMTP
$email_password = "emailpass";      // only used for SMTP
$email_return_address = "admin@somewhere.com";

# accepts full path name in scripts
$AcceptsFullScriptPath = true;

# only needed on servers that don't have session cookies in a writable
# directory	
$SaveSessionCookiesLocally = false;

?>
