<?php

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

// $Id: auth_ext.inc,v 1.1 2001/09/07 21:18:40 mbarclay Exp $

/* getAuth($realm)
 * 
 * Request that the username/password be given for the specified realm
 * 
 * $realm - Which username/password do we want.
 * 
 * Nothing
 */
function authGet($realm)
{
	Header("WWW-Authenticate: Basic realm=\"$realm\"");
	Header("HTTP/1.0 401 Unauthorized");
}

/* authValidateUser($user, $pass)
 * 
 * Checks if the specified username/password pair are valid
 * 
 * $user  - The user name
 * $pass  - The password
 * 
 * Returns:
 *   0        - The pair are invalid or do not exist
 *   non-zero - The pair are valid
 */
function authValidateUser($user, $pass)
{
        global $auth;
	
	// Check if we do not have a username/password
	if(!isset($user) || !isset($pass))
	{
		authGet($auth["realm"]);
		return 0;
	}
	
	// Generate the command line
	$cmd = $auth["prog"] . ' ' . $auth["params"];
	$cmd = preg_replace('/#USERNAME#/',escapeshellcmd($user),$cmd);
	$cmd = preg_replace('/#PASSWORD#/',escapeshellcmd($pass),$cmd);
	
	// Run the program
	exec($cmd, $output, $ret);
	
	// If it succeeded, return success
	if($ret == 0)
		return 1;
	
	// If we failed prompt for username/password
	if($user || $pass)
		authGet($auth["realm"]);
	
	// return failure
	return 0;
}

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
	
	// Check if the user can modify
	for($i = 0; $lev1_admin[$i]; $i++)
	{
		if(strcasecmp($user, $lev1_admin[$i]) == 0)
			return $UserLevelAdmin;
	}
	
	// Everybody else is access level '1'
	return $UserLevelNormal;
}

function getUserName()
{
	global $PHP_AUTH_USER;
	return $PHP_AUTH_USER;
}

function getUserPassword()
{
	global $PHP_AUTH_PW;
	return $PHP_AUTH_PW;
}

?>
