<?php

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: auth_ip.inc,v 1.1 2001/09/07 21:18:40 mbarclay Exp $

# No need to perform authentication - ip address always there
function authGet($realm) { }

# Always validates
function authValidateUser($user, $pass) { return 1; }

/* authGetUserLevel($user)
 * 
 * Determines the users access level
 * 
 * $user - The user name
 *
 * Returns:
 *   The users access level
 */
function authGetUserLevel($user, $lev1_admin)
{
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
	
	// User not logged in, user level '0'
	if(!isset($user))
		return $UserLevelDisabled;
	
	// Check if the user is can modify
	for($i = 0; isset($lev1_admin[$i]); $i++)
	{
		if(strcasecmp($user, $lev1_admin[$i]) == 0)
			return $UserLevelAdmin;
	}
	
	// Everybody else is access level '1'
	return $UserLevelNormal;
}

function getUserName()
{
	global $REMOTE_ADDR;
	return $REMOTE_ADDR;
}

function getUserPassword()
{
	return "dummy";
}

?>
