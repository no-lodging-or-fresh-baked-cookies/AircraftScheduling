<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

// $Id: AircraftScheduling_auth.inc,v 1.2 2001/12/20 07:05:57 mbarclay Exp $

// include the authentification wrappers
include "auth_$auth[type].inc";

/* getAuthorised($user, $pass, $level)
 * 
 * Check to see if the user name/password is valid
 * 
 * $user  - The user name
 * $pass  - The users password
 * $level - The access level required
 * 
 * Returns:
 *   0        - The user does not have the required access
 *   non-zero - The user has the required access
 */
function getAuthorised($user, $pass, $level)
{
	global $auth;
	
	if(!PasswordHasExpired($user, $pass, $level))
		return 0;
	
	if(!authValidateUser($user, $pass))
		return 0;
	
	if (authGetUserLevel($user, $auth["admin"]) >= $level)
		return 1;
	else
		return 0;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: PasswordHasExpired($user, $pass, $level)
// 
// PURPOSE:  Check to see if the password has expired or it is blank. If
//           it has, force the display of the update user information
//           screen.
//
// PARAMETERS:
//		user  - The user name
//		pass  - The users password
//		level - The access level required
//
// RETURN:
//		non-zero if the password has not expired otherwise the
//      UserInformation.php program is called.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.12    Mar 6, 2004   JCovington  Add journaling.
// 
// -----------------------------------------------------------------------------
function PasswordHasExpired($user, $pass, $level)
{
	global $auth;
	
	// if we have a valid user, check the password expiration
	if (strlen($user) > 0)
	{
		// get the password expiration date
		$PasswordExpiration = sql_query1("
								SELECT Password_Expires_Date 
								FROM AircraftScheduling_person 
								WHERE username='$user'");
	
		// if the password expiration is not null, check the password
		if ($PasswordExpiration != -1)
		{
			// if the password is blank or has expired
		    if (strtotime("now") > strtotime($PasswordExpiration)||
		        strlen($pass) == 0)
		    {
			    header("Location: UserInformation.php?username=" . $user);
			    exit;
			}
		}
	}
	
	// password hasn't expired
	return 1;
}

/* getWritable($creator, $user)
 * 
 * Determines if a user is able to modify an entry
 *
 * $creator - The creator of the entry
 * $user    - Who wants to modify it
 *
 * Returns:
 *   0        - The user does not have the required access
 *   non-zero - The user has the required access
 */
function getWritable($creator, $user)
{
	global $auth;
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
		
	// Always allowed to modify your own stuff
	if($creator == $user)
		return 1;
	
	if(authGetUserLevel($user, $auth["admin"]) >= $UserLevelSuper)
		return 1;
	
	// Unathorised access
	return 0;
}

/* showAccessDenied()
 * 
 * Displays an appropriate message when access has been denied
 * 
 * $day  - day of the year for the header
 * $month  - month of the year for the header
 * $year  - year for the header
 * $resource  - resource for the header
 * $resource_id  - resource ID for the header
 * $makemodel  - makemodel the header
 * $LocalParameters - additional parameters for the links
 * $InstructorResource - instructor resource for the header
 * 
 * Returns: Nothing
 */
function showAccessDenied(
                            $day, 
                            $month, 
                            $year, 
                            $resource, 
                            $resource_id, 
                            $makemodel, 
                            $goback,
                            $LocalParameters,
                            $InstructorResource = " ")
{
	global $lang;
	global $_SERVER;

	// show the header
	print_header($day, $month, $year, $resource, $resource_id, $makemodel, $LocalParameters);

    // if the user is logged in, we must not have access
    if (user_logged_on())
    {
        // user is logged on, not enough access rights 
        echo "<H1>" . $lang["accessdenied"] . "</H1>";
        echo "<P>";
        echo $lang["norights"];
        echo "</P>";
        echo "<P>";

        // generate return URL
        GenerateReturnURL(
                            $goback, 
                            "?day=$day&month=$month&year=$year$makemodel" . 
                            "&resource=$resource" .
                            "&resource_id=$resource_id" .
                            "&InstructorResource=$InstructorResource"
                            );
 
        echo "</P>";
        echo "</BODY>";
        echo "</HTML>";
    }
    else
    {
        // user is not logged in, let them log in and come back
        echo "<H1>";
        echo $lang["mustlog"];
        echo "</H1>";
        echo "<P>";
        echo $lang["mustlogBeg"];
        echo "<A HREF='" . 
                "logon.php" .
                    "?day=$day&month=$month&year=$year" .
                    "&resource=$resource" .
                    "&resource_id=$resource_id" .
                    "&InstructorResource=$InstructorResource" .
                    "$makemodel" . 
                    "&goback=" . GetScriptName() .
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
                "'>" . $lang["logon"] . "</A>";
        echo $lang["mustlogEnd"];
        echo "</P>";
        echo "<P>";
        // generate return URL
        GenerateReturnURL(
                            $goback, 
                            "?day=$day&month=$month&year=$year$makemodel" . 
                            "&resource=$resource" .
                            "&resource_id=$resource_id" .
                            "&InstructorResource=$InstructorResource"
                            );
        echo "</P>";
        echo "</BODY>";
        echo "</HTML>";
     }
}

/* showLoginTimedOut()
 * 
 * Displays an appropate message when access has been denied
 *
 * $day  - day of the year for the header
 * $month  - month of the year for the header
 * $year  - year for the header
 * $resource  - resource for the header
 * $resource_id  - resource ID for the header
 * $makemodel  - makemodel the header
 * 
 * Returns: Nothing
 */
function showLoginTimedOut($day, $month, $year, $resource, $resource_id, $makemodel)
{
	global $lang;
	global $goback;
	global $GoBackParameters;
	global $_SERVER;
	global $_SERVER;
	
	print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
?>
  <H1><?echo $lang["loggintimeout"]?></H1>
  <P>
            <?php
            echo "<A HREF='logon.php" .
                    "?goback=" . GetScriptName() .
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
            echo "'>";
            echo $lang["returnlogin"]; 
            echo "</A>";
            ?>
  </P>
 </BODY>
</HTML>
<?php
}
?>