<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: logon.php,v 1.10 2001/12/20 07:02:27 mbarclay Exp $

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";

// initialize variables
unset($error);
$InstructorResource = "";
$hour = "12";
$minute = "00";
$goback = "day.php";
$GoBackParameters = "";

// get the input parameters if they have been passed in
$rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
if(isset($rdata["username"])) $username = $rdata["username"];
if(isset($rdata["password"])) $password = $rdata["password"];
if(isset($rdata["day"])) $day = $rdata["day"];
if(isset($rdata["month"])) $month = $rdata["month"];
if(isset($rdata["year"])) $year = $rdata["year"];
if(isset($rdata["goback"])) $goback = $rdata["goback"];
if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
if(isset($rdata["usessl"])) $usessl = $rdata["usessl"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];
if(isset($rdata["resource"])) $resource = $rdata["resource"];
if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
if(isset($rdata["make"])) $make = $rdata["make"];
if(isset($rdata["model"])) $model = $rdata["model"];
if(isset($rdata["hour"])) $hour = $rdata["hour"];
if(isset($rdata["id"])) $id = $rdata["id"];
if(isset($rdata["minute"])) $minute = $rdata["minute"];

if($enablessl && $_SESSION["usessl"] && $HTTPS != "on") { header("Location: https://" . getenv("SERVER_NAME") . $_SERVER["PHP_SELF"]);  }

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

if($make) $makemodel = "&make=" . str_replace(" ", "+", $make);
else if($model) $makemodel = "&model=" . str_replace(" ", "+", $model);
else if($certificate) $makemodel = "&certificate=$certificate";
else { $all=1; $makemodel = "&all=1"; }

if(empty($resources) or !isset($resources))
  $resources = get_default_resource();

if(isset($pview)) unset ($pview);

// did the user enter a valid username?
if(isset($username) and !empty($username))
{
	if(!isset($_SESSION["try" . $username])) 
    {
		$_SESSION["try" . $username] = 0;
    }
    else
	   	$_SESSION["try" . $username]++;
    $error = "Sign in Failed! ";
    if($_SESSION["try" . $username] > $Signin_Attempts)
    {
    	// too many sign on attempts, disable the account
		sql_command("UPDATE AircraftScheduling_person SET user_level = $UserLevelDisabled WHERE username = '$username'");
		$_SESSION["try" . $username] = 0;
	
		// log the information in the journal
		$Description = 
					"Disabling user $username due to too many failed login attempts";
		CreateJournalEntry(strtotime("now"), $username, $Description);
		
	}

    if(isset($usessl)) 
    {
		$_SESSION["usessl"] = true;
		if(isset($_SESSION["goback"]))
		    $_SESSION["goback"] = preg_replace("/http/", "/https/", $_SESSION["goback"]);
    }
    
    if(authValidateUser($username, $password)) 
    {
		// login successful, update login information in database
		$error = "";
		$_SESSION["counter"]++;
		$sql = "UPDATE AircraftScheduling_person 
				SET counter = " . $_SESSION["counter"] . 
					" WHERE username = '$username'";
		if(1 != sql_command($sql)) 
		{
			echo "Unable to update user counter in database.";
		}
		
		// update the activity counter
		$_SESSION["LastActivityTime"] = strtotime("now");	
                    		
		// if we have a manager's message, display it		                        
        $ManagerMessage = sql_query1(
                    "SELECT Manager_Message  
                     FROM AircraftScheduling_person 
                     WHERE username = '$username'
                     ");
                     
        if ($ManagerMessage != -1 && strlen($ManagerMessage) > 0)
        {
            // we have a managers message, show it to them
			session_write_close();
        	$Header = "Location: DisplayManagerMsg.php?" . 
        	                "ManagerMessage=$ManagerMessage" . 
        	                "&day=$day&month=$month&year=$year";       	                
        	if (isset($resource) && !empty($resource)) $Header .= "&resource=$resource";
        	if (isset($resource_id) && !empty($resource_id)) $Header .= "&resource_id=$resource_id";
        	if (isset($InstructorResource) && !empty($InstructorResource)) $Header .= "&InstructorResource=$InstructorResource";
        	if (isset($hour) && !empty($hour)) $Header .= "&hour=$hour";
        	if (isset($minute) && !empty($minute)) $Header .= "&minute=$minute";
        	if (isset($id) && !empty($id)) $Header .= "&id=$id";
        	if (isset($makemodel) && !empty($makemodel)) $Header .= "$makemodel";
        	if (!empty($goback)) $Header .= "&goback=$goback";
        	if (!empty($GoBackParameters)) $Header .= "&GoBackParameters=$GoBackParameters";
            header($Header);
            exit();
        }
        else
        {
            // no manager's message, send them to the correct page
  			session_write_close();
    		if(!empty($goback))
    		{
        	    $Header = "Location: $goback" . CleanGoBackParameters($GoBackParameters);
                header($Header);
	            exit();
        	}
    		else
    		{
            	$Header = "Location: day.php?" .
        	        "day=$day&month=$month&year=$year";
        	    if (isset($resource) && !empty($resource)) $Header .= "&resource=$resource";
        	    if (isset($resource_id) && !empty($resource_id)) $Header .= "&resource_id=$resource_id";
         	    if (isset($InstructorResource) && !empty($InstructorResource)) $Header .= "&InstructorResource=$InstructorResource";
        	    if (isset($hour) && !empty($hour)) $Header .= "&hour=$hour";
        	    if (isset($minute) && !empty($minute)) $Header .= "&minute=$minute";
        	    if (isset($id) && !empty($id)) $Header .= "&id=$id";
        	    if (isset($makemodel) && !empty($makemodel)) $Header .= "$makemodel";
        	    if (!empty($goback)) $Header .= "&goback=$goback"; 
        	    if (!empty($GoBackParameters)) $Header .= "&GoBackParameters=$GoBackParameters";
                header($Header);
	            exit();
        	}
    	}
	}
    else 
    {
		$disabled = 
		    sql_query1("SELECT user_level FROM AircraftScheduling_person WHERE username = '$username'");
		if($disabled == 0)
			$error .= "Account is disabled please contact customer support";
		else    
			$error .= "Invalid Username or Password";
   }
}

# print the page header
print_header($day, $month, $year, $resources, $resource_id, $makemodel, "");

if(isset($error))
{
	echo "<H1>$error</H1>";
} 
?>


<TABLE WIDTH="100%" CELLSPACING="2" CELLPADDING="0">
<TR>
  <TD VALIGN="TOP">
  <BR>

  <TABLE WIDTH="100%">
      <TR>
      <TD>
        <DIV STYLE="border-width:1px; border-style:solid; border-color:#000000;">
          <TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="0" BGCOLOR="#FFFFFF">
            <TR>
              <TD CLASS="CellHeader"><?php echo $lang["logon"]; ?></TD>
            </TR>
            <TR>
              <TD CLASS="CellContent">
                <FORM NAME="logon" ACTION="<?php echo getenv("SCRIPT_NAME") ?>" METHOD="POST">
                <INPUT NAME="day" TYPE="HIDDEN" VALUE="<?php echo $day ?>">
                <INPUT NAME="month" TYPE="HIDDEN" VALUE="<?php echo $month ?>">
                <INPUT NAME="year" TYPE="HIDDEN" VALUE="<?php echo $year ?>">
                <?php
                    if(isset($resource) && !empty($resource)) echo "<INPUT NAME='resource' TYPE='HIDDEN' VALUE='$resource'>";
                    if(isset($resource_id) && !empty($resource_id)) echo "<INPUT NAME='resource_id' TYPE='HIDDEN' VALUE='$resource_id'>";
                    if(isset($make) && !empty($make)) echo "<INPUT NAME='make' TYPE='HIDDEN' VALUE='$make'>";
                    if(isset($model) && !empty($model)) echo "<INPUT NAME='model' TYPE='HIDDEN' VALUE='$model'>";
                    if(isset($InstructorResource) && !empty($InstructorResource)) echo "<INPUT NAME='InstructorResource' TYPE='HIDDEN' VALUE='$InstructorResource'>";
                    if(isset($hour) && !empty($hour)) echo "<INPUT NAME='hour' TYPE='HIDDEN' VALUE='$hour'>"; 
                    if(isset($minute) && !empty($minute)) echo "<INPUT NAME='minute' TYPE='HIDDEN' VALUE='$minute'>"; 
                    if(isset($id) && !empty($id)) echo "<INPUT NAME='id' TYPE='HIDDEN' VALUE='$id'>"; 
                    if(!empty($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>"; 
                    if(!empty($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>"; 
                ?>

                <TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0">
                  <TR>
                    <TD CLASS="CellDescription">Username:</TD>
                    <TD CLASS="CellInput"><INPUT TYPE="TEXT" NAME="username" SIZE="16" MAXLENGTH="20"></TD>
                  </TR>
                  <TR>
                    <TD CLASS="CellDescription">Password:</TD>
                    <TD CLASS="CellInput"><INPUT TYPE="PASSWORD" NAME="password" SIZE="16" MAXLENGTH="24"></TD>
                  </TR>
                  <TR>
                    <?php if($enablessl && !isset($HTTPS)) { ?>
                        <TD COLSPAN='2' ALIGN='CENTER'>
                        <H2><INPUT NAME="usessl" TYPE="checkbox"> Use SSL (Select this option to protect your username and password)</H2><BR>
                        </TD>
                    
                        <SCRIPT LANGUAGE="JavaScript">
                        function do_submit() {
                          if(document.forms["logon"].usessl.checked == true)
                              document.forms["logon"].action = <?php echo "\"https://" . getenv("SERVER_NAME") . getenv("SCRIPT_NAME") . "\";" ?>
                        }
                        </SCRIPT>
                        
                        <SCRIPT LANGUAGE="JavaScript">
                               document.writeln ( '<INPUT type="submit" value="<?php echo $lang["logon"] ?>" ONCLICK="do_submit()">' );
                        </SCRIPT>
                        <NOSCRIPT>
                           <INPUT TYPE="submit" VALUE="<?php echo $lang["logon"]?>">
                        </NOSCRIPT>
                    
                    <?php 
                        } 
                        else 
                        {
                            // not using SSL
                            echo "<TD COLSPAN='2' ALIGN='CENTER'>";
                            echo "<INPUT TYPE='submit' VALUE='$lang[logon]'>";
                            echo "</TD>";  
                        } 
                    ?>
                  </TR>
                  <TR>
                    <TD COLSPAN='2'>
                        <?php 
                        echo "<A href='email_pass.php?$_SERVER[QUERY_STRING]" . 
                            "'>" . $lang["forgot_password"] . "</a>";  
                        ?>
                   </TD>
                  </TR>
                </TABLE>
                </FORM>
              </TD>
            </TR>
          </TABLE>
        </DIV>

      </TD>
    </TR>

    <!-- Weather map and links -->
    <TR>
      <TD>
        <DIV STYLE="border-width:1px; border-style:solid; border-color:#000000;">

    
          <!-- Weather link -->
          <TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="0">
            <TR>
              <TD CLASS="CellHeader">Weather</TD>
            </TR>
            <TR>
              <TD CLASS="CellContent" BGCOLOR="#FFFFFF">
                <FONT CLASS="StdText">
                  <CENTER>Click for detailed weather images<BR>
                  <A TARGET="_NEW" HREF="<?php echo $Logon_Weather_Link; ?>">
                    <IMG WIDTH="217" HEIGHT="190" ALT="weather map" SRC="<?php echo $Logon_Weather_Map_Link; ?>" BORDER="1">
                  </A>
                  <?php echo $Logon_Weather_Credit; ?>
                  </CENTER>
                </FONT>
              </TD>
            </TR>
          </TABLE>

        </DIV>

      </TD>
    </TR>
  </TABLE>
  </TD>

  <!-- Notices -->
  <TD CLASS="CellMessage" VALIGN="TOP" WIDTH="100%">
  <BR>
        <?php
        // display the notice field if one exists
        $sql = "SELECT Notices, " . sql_syntax_timestamp_to_unix("timestamp") . 
                " FROM AircraftScheduling_notices";
        $sql_result = sql_query($sql);			
        // check the results and save if an error occurs
        if ($sql_result) 
        {
        	// valid result, display the notices
        	$row = sql_row($sql_result, 0);
        
        	// if there is a notice, display the notice box and message
        	if (strlen($row[0]) > 0)
        	{
        		echo "<center><table border=1>";
        		echo "<tr>";
        		echo "<TD CLASS='CellHeader'>" . $lang["notices"] . 
        		        "<BR><FONT SIZE=2>Last Updated " . 
        		            strftime('%X - %d %B %Y', $row[1] - TimeZoneAdjustment()) . 
        		        "</FONT></TD>";
        		echo "</tr>";
        		echo "<tr>";
        		echo "<TD CLASS=TL>
        		        <TEXTAREA NAME='Notice' ROWS=22 COLS=68 WRAP='virtual' READONLY>" . 
        		        htmlentities ( $row[0] ) . "</TEXTAREA></TD></TR>";
        		echo "</tr>";
        		echo "</table>";
        	}
        	else
        	{
        		echo "<center><table border=1>";
        		echo "<tr>";
        		echo "<TD CLASS='CellHeader'>" . $lang["notices"] . 
        		        "<BR><FONT SIZE=2>Last Updated " . 
        		            strftime('%X - %d %B %Y', $row[1] - TimeZoneAdjustment()) . 
        		        "</FONT></TD>";
        		echo "</tr>";
        		echo "<tr>";
        		echo "<TD CLASS=TL>
        		        <TEXTAREA NAME='Notice' ROWS=22 COLS=68 WRAP='virtual' READONLY>" . 
        		        htmlentities ( " " ) . "</TEXTAREA></TD></TR>";
        		echo "</tr>";
        		echo "</table>";
        	}
        }
        else
        {
        	// invalid result, display the error
        	echo "<H2>Unable to retreive data from the notices table!</H2>\n" . sql_error(); 
        }
        ?>
  </TD>

</TR>
</TABLE>

<?php
include "trailer.inc"; 
?>