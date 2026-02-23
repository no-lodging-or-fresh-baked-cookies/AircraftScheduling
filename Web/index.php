<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: index.php,v 1.2 2001/12/20 07:02:27 mbarclay Exp $

#Index is just a stub to redirect to the appropriate day view

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";

// initialize variables
$InstructorResource = "";
$hour = "12";
$minute = "00";

// get the input parameters if they have been passed in
$rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
if(isset($rdata["day"])) $day = $rdata["day"];
if(isset($rdata["month"])) $month = $rdata["month"];
if(isset($rdata["year"])) $year = $rdata["year"];
if(isset($rdata["make"])) $make = $rdata["make"];
if(isset($rdata["model"])) $model = $rdata["model"];
if(isset($rdata["resource"])) $resource = $rdata["resource"];
if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];
if(isset($rdata["goback"])) $goback = $rdata["goback"];
if(isset($rdata["all"])) $all = $rdata["all"];
if(isset($rdata["hour"])) $hour = $rdata["hour"];
if(isset($rdata["minute"])) $minute = $rdata["minute"];

#If we dont know the right date then make it up 
if(!isset($day) or !isset($month) or !isset($year))
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

if (empty($resource))
	$resource = get_default_resource();

if (empty($InstructorResource))
	$InstructorResource = "None";

if($make) $makemodel = "&make=" . str_replace(" ", "+", $make);
else if($model) $makemodel = "&model=" . str_replace(" ", "+", $model);
else if($certificate) $makemodel = "&certificate=$certificate";
else { $all=1; $makemodel = "&all=1"; }
		
// if the login has timed out
if (user_logged_on() && 
		LoginHasTimedOut() && 
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelNormal)
{
	// login has timed out, logout the user
	user_logoff();
	
	// show the problem
	showLoginTimedOut($day, $month, $year, $resource, $resource_id, $makemodel);
	exit();
}

if(getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal) || !$User_Must_Login)
{
	// we are signed in or login to view is disabled, give them the default screen
	session_write_close();
	if(isset($goback))
	    header("Location: " . $goback . 
	                "?day=$day&month=$month&year=$year" .
	                "&resource=$resource" .
	                "&resource_id=$resource_id" .
	                "&InstructorResource=$InstructorResource" .
	                "&hour=$hour" .
	                "&minute=$minute" .
	                "$makemodel");
	else
    	header("Location: day.php" .
	                "?day=$day&month=$month&year=$year" .
	                "&resource=$resource" .
	                "&resource_id=$resource_id" .
	                "&InstructorResource=$InstructorResource" .
	                "&hour=$hour" .
	                "&minute=$minute" .
	                "$makemodel");
}
else
{
	// we aren't signed in yet, give them the logon screen
	session_write_close();
	header("Location: logon.php");
}

