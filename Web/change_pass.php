<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: change_pass.php,v 1.7 2001/12/20 07:02:27 mbarclay Exp $

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";

// initialize variables
$InstructorResource = "";
$goback = "";
$GoBackParameters = "";

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
if(isset($rdata["enablessl"])) $enablessl = $rdata["enablessl"];
if(isset($rdata["username"])) $username = $rdata["username"];
if(isset($rdata["oldpass"])) $oldpass = $rdata["oldpass"];
if(isset($rdata["newpass"])) $newpass = $rdata["newpass"];
if(isset($rdata["newpass2"])) $newpass2 = $rdata["newpass2"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];
if(isset($rdata["goback"])) $goback = $rdata["goback"];
if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];

if($enablessl && $_SESSION["usessl"] && $HTTPS != "on") { header("Location: https://" . getenv('SERVER_NAME') . $_SERVER["PHP_SELF"]);  }

if (empty($resource))
	$resource = get_default_resource();

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

if($make) $makemodel = "&make=$make";
else if($model) $makemodel = "&model=$model";
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

if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
{
	print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
	echo "<H2>You are not authorized to change your password</H2>";
	echo "<H2>Please contact the <A href='mailto:$AircraftScheduling_admin_email'>Administrator</A> to get User privledges</H2>";
	exit();
}

print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");

$username = isset($username) ? $username : getUserName();

if($username != "" and isset($oldpass) and isset($newpass) and $newpass == $newpass2) 
{
	$sql = "UPDATE AircraftScheduling_person SET password = '$newpass' WHERE username = '$username' AND password = '$oldpass'";
	
	if(1 != sql_command($sql))
	{
        if (isset($goback)) 
    		echo "<FORM NAME='PasswordChanged' ACTION='$goback' METHOD='POST'>";
        else 
    		echo "<FORM NAME='PasswordChanged' ACTION='index.php' METHOD='POST'>";
		echo "<center>";
		echo "<H1>Unable to change password! " . sql_error() . "</H1>";
    
        BuildHiddenInputs("year=$year&month=$month&day=$day"
                          . "&resource=$resource"
                          . "&resource_id=$resource_id"
                          . "&InstructorResource=$InstructorResource");

		echo "<INPUT TYPE='submit' VALUE='OK'>";
		echo "</center>";
		echo "</FORM>";
	}
	else 
	{
		// log the change in to the journal
		$Description = 
					"Password changed";
		CreateJournalEntry(strtotime("now"), getUserName(), $Description);

		$_SESSION["ActivePassword"] = $newpass;
        if (isset($goback)) 
    		echo "<FORM NAME='PasswordChanged' ACTION='$goback' METHOD='POST'>";
        else 
    		echo "<FORM NAME='PasswordChanged' ACTION='index.php' METHOD='POST'>";
		echo "<center>";
		echo "<H1>Password changed!</H1>";
    
        BuildHiddenInputs("year=$year&month=$month&day=$day"
                          . "&resource=$resource"
                          . "&resource_id=$resource_id"
                          . "&InstructorResource=$InstructorResource");

		echo "<INPUT TYPE='submit' VALUE='OK'>";
		echo "</center>";
		echo "</FORM>";
	}
}
else 
{
    echo "<FORM name='change_pass' ACTION='" . getenv("SCRIPT_NAME") . "' METHOD='POST'>";
    if(!user_logged_on()) {
		echo "<H3>Enter your username:</H3>";
		echo "<INPUT name='username' length=10 maxlength=24><br>";
    }
    echo "<H3>Enter your current password:</H3>";
    echo "<INPUT name='oldpass' type=password><br>";

    echo "<H3>Enter a new password:</H3>";
    echo "<INPUT name='newpass' type=password><br>";

    echo "<H3>Enter it again:</H3>";
    echo "<INPUT name='newpass2' type=password><br>";
    
    BuildHiddenInputs("year=$year&month=$month&day=$day"
              . "&resource=$resource"
              . "&goback=$goback"
              . "&resource_id=$resource_id"
              . "&InstructorResource=$InstructorResource");

?>
<SCRIPT LANGUAGE="JavaScript">
function validate_and_submit ()
{
  np = document.forms["change_pass"].newpass.value;
  np2 = document.forms["change_pass"].newpass2.value;
  oldp = document.forms["change_pass"].oldpass.value;

  <?php if(!user_logged_on()) { ?>
      user = document.forms["change_pass"].username.value;
      
      if(user == "") {
	  alert("Please enter a username");
	  return false;
      }
  <?php } ?>

  if(np != np2)  {
    alert("Your new password doesn't match");
    return false;
  }
  
  if(oldp == np) {
      alert("That's not a new password!");
      return false;
  }

  document.forms["change_pass"].submit();
  
  return true;
}
</SCRIPT>

<SCRIPT LANGUAGE="JavaScript">
       document.writeln ( '<INPUT type="button" value="<?php echo $lang["change"] ?>" ONCLICK="validate_and_submit()">' );
</SCRIPT>
<NOSCRIPT>
   <INPUT TYPE="submit" VALUE="<?php echo $lang["change"]?>">
</NOSCRIPT>

<?php
 }
include "trailer.inc";
?>
