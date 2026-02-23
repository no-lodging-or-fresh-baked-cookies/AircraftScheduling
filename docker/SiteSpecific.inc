<?php
//-----------------------------------------------------------------------------
// SiteSpecific.inc — Docker configuration
//-----------------------------------------------------------------------------

# Database connection (matches docker-compose.yml service name and credentials)
$db_host = "db";
$db_database = "aircraftscheduling";
$db_login = "appuser";
$db_password = "apppassword";

# email configuration (disabled by default in Docker)
$email_type = "smtp";
$email_host = "localhost";
$email_username = "";
$email_password = "";
$email_return_address = "admin@localhost";

# accepts full path name in scripts
$AcceptsFullScriptPath = true;

# sessions use default /tmp path set in php.ini
$SaveSessionCookiesLocally = false;

?>
