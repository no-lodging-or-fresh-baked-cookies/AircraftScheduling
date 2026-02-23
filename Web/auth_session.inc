<?php

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: auth_session.inc,v 1.8 2001/12/20 07:05:57 mbarclay Exp $

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
    global $auth, $db_session_interval, $dbsys;
	
	// Check if we do not have a username/password
	if(empty($user) || empty($pass))
	{
		authGet($auth["realm"]);
		return 0;
	}
	$sql = "SELECT 
	            person_id, 
	            user_level, 
	            first_name, 
	            last_name, 
	            phone_number, 
	            counter,
	            Allow_Phone_Number_Display,
	            Home_Phone  
	        FROM AircraftScheduling_person 
	        WHERE username = '$user' AND password = '$pass'";
	$res = sql_query($sql); 
	if (!$res)
	{
		// Failed to log on
	    return false;    
	}
	else
	{ 
		// Sucessful log on
	    $row = sql_row($res,0);
	    
	    // if user is null or user level is 0, account is not active
	    if($row == 0 || $row[0] == '' || $row[1] == 0)
			return false;

    	// user is logged in, save the session information
		$_SESSION["authenticated"] = true;
		$_SESSION["ActiveUsername"] = $user;
		$_SESSION["ActivePassword"] = $pass;
		$_SESSION["ActiveUserLevel"] = $row[1];
		$_SESSION["ActiveFirstName"] = $row[2];
		$_SESSION["ActiveLastName"]  = $row[3];
        // if the user is allowing phone number display, save the phone
        // numbers
    	if ($row[6] == 1)
    	{           
    		$_SESSION["ActivePhoneNumber"]  = $row[4];           
    		$_SESSION["ActivePhoneNumber2"]  = $row[7];           
    	}
    	else
    	{
    	    // user doesn't want phone numbers displayed
    		$_SESSION["ActivePhoneNumber"]  = "UNLISTED";
    		$_SESSION["ActivePhoneNumber2"]  = "";           
    	}
		$_SESSION["counter"] = $row[5];
		$_SESSION["LastActivityTime"] = strtotime("now");
		
		return true;
	} 
}

/* user_logged_on()
 * 
 * Checks if the a user is logged in
 * 
 * Returns:
 *   false - User is not logged in
 *   true - User is logged in
 */
function user_logged_on() 
{
    return $_SESSION["authenticated"] ? $_SESSION["authenticated"] : false;
}

/* user_logoff()
 * 
 * Clear the variables that show a user is logged in
 * 
 * Returns:
 *   true - User is logged off
 */
function user_logoff() 
{
	// clear the user information from the session
	$_SESSION["authenticated"] = false;
	$_SESSION["ActiveUsername"] = "";
	$_SESSION["ActivePassword"] = "";
	$_SESSION["ActiveUserLevel"] = "";
	$_SESSION["ActiveFirstName"] = "";
	$_SESSION["ActiveLastName"]  = "";
	$_SESSION["ActivePhoneNumber"]  = "";
    return true;
}

/* LoginHasTimedOut()
 * 
 * Check to see if the user login has timed out
 * 
 * Returns:
 *   false - Login has timed out
 *   true - Login has timed out
 */
function LoginHasTimedOut() 
{
	global $Activity_Timeout;
	
	// if we have been inactive too long
	if (abs(strtotime("now") - $_SESSION["LastActivityTime"]) < $Activity_Timeout)
		// login has not timed out
		return false;
	else
		// login has timed out
    	return true;
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
	
	// User not logged in, user level UserLevelDisabled
    return $_SESSION["ActiveUserLevel"] ? $_SESSION["ActiveUserLevel"] : $UserLevelDisabled;
}

function getUserName()
{
    return $_SESSION["ActiveUsername"];
}

function buildName($first, $last)
{
    global $BuildNameFormat;
    
	if($first != "")
		if($last != "")
		{
		    eval("\$ReturnStr = \"$BuildNameFormat\";");
			return $ReturnStr;
		}
		else
			return "$first";
	else
		if($last != "")
			return "$last";
		else
			return "";
}

function getName()
{
	$first = $_SESSION["ActiveFirstName"];
	$last  = $_SESSION["ActiveLastName"];
	return buildName($first, $last);
}

function getPhoneNumber()
{
     return $_SESSION["ActivePhoneNumber"];
}

function getPhoneNumber2()
{
     return $_SESSION["ActivePhoneNumber2"];
}

function getUserPassword()
{	
    return $_SESSION["ActivePassword"];
}
?>
