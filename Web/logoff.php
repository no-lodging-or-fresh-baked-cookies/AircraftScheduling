<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: logoff.php,v 1.5 2001/12/20 07:02:27 mbarclay Exp $

include "global_def.inc";
include "config.inc";
include "$dbsys.inc";
include "AircraftScheduling_auth.inc";
include "functions.inc";

// get the input parameters if they have been passed in
$rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
if(isset($rdata["day"])) $day = $rdata["day"];
if(isset($rdata["month"])) $month = $rdata["month"];
if(isset($rdata["year"])) $year = $rdata["year"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];

#If we dont know the right date then make it up 
if (!isset($day) or !isset($month) or !isset($year))
{
	$day   = date("d");
	$month = date("m");
	$year  = date("Y");
} 
else
{
    // make sure the date values are valid
    ValidateDate($day, $month, $year);
}

if(user_logoff()) 
{
	// Unset all of the session variables.
	$_SESSION = array();

	// destroy the session
	session_destroy();

    $PHP_AUTH_USER="";
    $PHP_AUTH_PASS="";
	session_write_close();
	
	// force the default view to be for today
    $day   = date("d");
	$month = date("m");
	$year  = date("Y");
	Header("Location: index.php?day=$day&month=$month&year=$year");
}

?>
