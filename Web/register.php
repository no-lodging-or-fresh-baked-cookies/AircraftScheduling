<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: register.php,v 1.6 2001/12/20 07:02:27 mbarclay Exp $

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";

// initialize data
$InstructorResource = "";

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
if(isset($rdata["email"])) $email = $rdata["email"];
if(isset($rdata["username"])) $username= $rdata["username"];
if(isset($rdata["password"])) $password = $rdata["password"];
if(isset($rdata["password2"])) $password2 = $rdata["password2"];
if(isset($rdata["title"])) $title = $rdata["title"];
if(isset($rdata["first_name"])) $first_name = $rdata["first_name"];
if(isset($rdata["middle_name"])) $middle_name = $rdata["middle_name"];
if(isset($rdata["last_name"])) $last_name = $rdata["last_name"];
if(isset($rdata["address1"])) $address1 = $rdata["address1"];
if(isset($rdata["address2"])) $address2 = $rdata["address2"];
if(isset($rdata["city"])) $city = $rdata["city"];
if(isset($rdata["state"])) $state = $rdata["state"];
if(isset($rdata["zip"])) $zip = $rdata["zip"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];
if(isset($rdata["Allow_Phone_Number_Display"])) $Allow_Phone_Number_Display = $rdata["Allow_Phone_Number_Display"];

global $day, $month, $year;

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

if($AllowAircraftCheckout)
{
    // don't allow registrations since the office staff handles adding users
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    echo "<H1>Registration is not allowed in the online scheduler. See one of the
        office staff to register.</H1>";
    echo "<P>";

    // generate return URL
    GenerateReturnURL(
                        "index.php", 
                        "?day=$day&month=$month&year=$year$makemodel" . 
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource"
                        );
    echo "</P>";
    echo "</BODY>";
    echo "</HTML>";
    exit();
}

if(isset($pview)) unset ($pview);

if (empty($resource))
	$resource = get_default_resource();

if($make) $makemodel = "&make=$make";
else if($model) $makemodel = "&model=$model";
else if(empty($certificate)) { $all=1; $makemodel = "&all=1"; }

if(!isset($first_name) or !isset($last_name) or !isset($email) or !isset($username) or !isset($password) or
     $username == "" or $password == "") 
{ 
	print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
	show_registration_form();
}
else 
{
    // clear the password2 entry since it was only used to validate the password
	unset($_POST["password2"]);
	reset($_POST);
	
	// reset the instructor information since a new user can't be an instructor
	unset($_POST["InstructorResourceID"]);
	reset($_POST);

    // build the database fields and values
	$c=0;
	foreach ($_POST as $name => $value)
	{
		if(!empty($name) and !empty($value))
		{
			$fields[$c] = $name;
			$values[$c] = $value;
		}
		$c++;
	}
		
    // set the allow phone number display to a valid value
    // the code above will set the value if it is a 1 but won't
    // if it is a null. If it is a null, we will set it to a 0.
    if ($Allow_Phone_Number_Display != 1)
    {
        $Allow_Phone_Number_Display = 0;
	    $c++;
	    $fields[$c] = "Allow_Phone_Number_Display";
	    $values[$c] = $Allow_Phone_Number_Display;
	}

	// add commas between the field values
	$fields = join(", ", $fields);
	$values = join("', '", $values);
	
	$sql = "INSERT INTO AircraftScheduling_person ($fields, user_level) VALUES ('$values', 0)";
	
	$test = sql_query1("SELECT username FROM AircraftScheduling_person WHERE username = '$username'");
	if($test != -1) 
	{
		print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
		echo "<H2>Error!  Username already taken!  Please try again:</H2>";
		show_registration_form();
		include "trailer.inc";
		exit();
	}
	
	if(sql_command($sql) != 1) 
	{
		print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
		echo "<H2>Error!  Unable to add you to the database!  Please contact the systems administrator</H2>".sql_error();
		show_registration_form();
		include "trailer.inc";
		exit();
	}
	
	// Success!
	AircraftSchedulingMail($AircraftScheduling_admin_email, 
	      "$username added to $AircraftScheduling_company database", 
	      "Please review and enable the following user: $username\n" .
	      "Name: $title $first_name $middle_name $last_name\n" .
	      "Email: $email\n" . 
	      "FBO: $AircraftScheduling_company\n");
	
	// log the information in the journal
	$Description = 
				"New user $title $first_name $middle_name $last_name has requested access";
	CreateJournalEntry(strtotime("now"), "none", $Description);
	
	// show the results of the registration
	print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
	show_registration_results();
	exit;
}

function show_registration_form() 
{
	global $lang, $first_name, $last_name, $middle_name, $title, $email, $username, $enablessl, $SCRIPT_NAME, $HTTPS;
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
	
	// display the form
	echo "<FORM NAME=\"register\" ACTION=\"" . getenv("SCRIPT_NAME") . "\" METHOD=\"POST\">";
	echo "<CENTER><H4>Enter the following information</H4></CENTER>";
	displayUserInformation(
	                        true, 
	                        "register", 
	                        $UserLevelDisabled, 
	                        "",                         // username                     
	                        "",                         // first_name                   
	                        "",                         // last_name                    
	                        "",                         // email                        
	                        "",                         // person_id                    
	                        $UserLevelDisabled,         // user_level                   
	                        "",                         // counter                      
	                        "",                         // $middle_name                 
	                        "",                         // $password                    
	                        "",                         // title                        
	                        "",                         // address1                     
	                        "",                         // address2                     
	                        "",                         // city                         
	                        "",                         // state                        
	                        "",                         // zip                          
	                        "",                         // phone_number                 
	                        1,                          // Allow_Phone_Number_Display   
	                        "",                         // Home_Phone                   
                            -1,                         // Instructor Resource ID
                            "",                         // Instructor description
                            "");                        // Instructor's picture
	echo "</center>";
	
	// display the SSL checkbox if enabled
	if($enablessl && !isset($HTTPS)) 
	{
		echo "<H2><INPUT NAME=\"usessl\" TYPE=\"checkbox\"> Use SSL (Select this option to protect your personal information)</H2>";
	}
	?>
	
	<SCRIPT LANGUAGE="JavaScript">
		document.writeln ( '<INPUT type="button" value="Submit" ONCLICK="do_submit()">' );
	</SCRIPT>
	<NOSCRIPT>
		<INPUT TYPE="submit" VALUE="Submit">
	</NOSCRIPT>
	
	</FORM>
	
	<?php
	
}

?>

<?php
function show_registration_results() 
{
	global $day, $month, $year;
?>
<FORM NAME="register" ACTION=<?php 
			echo "\"index.php?year=$year&month=$month&day=$day";
			if (isset($resource)) echo "&resource=$resource";
			if (isset($InstructorResource)) echo "&InstructorResource=$InstructorResource\"" 
			?> METHOD="POST">
<center>

<td width="70%">
  <table border=0>
    <tr>
      <td><H1>Your account has been saved but is inactive. Email has been sent to the system administrator
with your new account information. You will be notified when the account is activated.</H1></td>
    </tr>
  </table>

  <INPUT TYPE="submit" VALUE="OK">
</center>
</FORM>

<?php

}

include "trailer.inc"

?>
