<?php
//-----------------------------------------------------------------------------
// 
// functions
// 
// PURPOSE: Provides general functions for aircraft scheduling.
// 
// PARAMETERS:
//      none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// -----------------------------------------------------------------------------

require("class.phpmailer.php");
require_once("StringFunctions.inc");

//-----------------------------------------------------------------------------
// 
// FUNCTION: 
// 
// PURPOSE: Set variables from the database.
// 
// PARAMETERS:
//		none
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//      This is not actually a procedure. It is called when the file is
//      included.
// 
// HISTORY:   Date:      Author:     Comment:
// 
// -----------------------------------------------------------------------------
$res = sql_query("SELECT name, value FROM AircraftScheduling_config");
if($res) 
{
	for($c=0;($row = sql_row($res, $c)); $c++) 
	{
		$config_name = $row[0];
		$$config_name = stripslashes("$row[1]");
	}
} 
else
{
	echo "<H2>Unable to retreive data from the config table!</H2>\n" . sql_error(); 
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: UserHasAircraftCheckedout($UserName)
// 
// PURPOSE: Check to see if the given user has an aircraft checked out, return
//          true if so.
// 
// PARAMETERS:
//      UserName - username of the user to check
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.32   Mar 8, 2005     JCovington Move offline functions into the online program.
// 
// -----------------------------------------------------------------------------
function UserHasAircraftCheckedout($UserName)
{                    		
	// see if this user already has an aircraft checked out		                        
    $AircraftTailNumber = sql_query1(
                "SELECT n_number  
                 FROM AircraftScheduling_aircraft 
                 WHERE CurrentKeycode = '$UserName'
                 ");
                 
    if ($AircraftTailNumber != -1 && strlen($AircraftTailNumber) > 0)
    {
        // user has an aircraft checked out, return true
        return true;
    }
    else
    {
        // user does not have an aircraft checked out, return false
        return false;
    }
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: BuildHiddenInputs($Parameters)
// 
// PURPOSE: Generate hidden inputs from the given parameters.
// 
// PARAMETERS:
//      Parameters - parameters to be split into hidden inputs. The format of
//                  the parameter is: &parameter=value.
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function BuildHiddenInputs($Parameters)
{	
    // use a local copy of the parameters so we won't alter the original
    $locParameters = $Parameters;
    
    // if the first character is a ? or & remove it
    if (substr($locParameters, 0, 1) == "?" || substr($locParameters, 0, 1) == "&")
        $locParameters = substr($locParameters, 1, strlen($locParameters) - 1);
        
	// split the parameter string into substrings so we can include it in the inputs
	$ParameterArray = explode("&", $locParameters);

	// process the parameters
	foreach ($ParameterArray as $Item)
	{
		$ItemArray = preg_split("/=/", $Item, 2);
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
    	    echo "<INPUT TYPE=HIDDEN NAME=$ItemArray[0] VALUE=$ItemArray[1]>";
		}
	}
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: GetScriptName()
// 
// PURPOSE: Return the name of the current executing script.
// 
// PARAMETERS:
//      none
//
// RETURN:
//		The name of the script
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function GetScriptName()
{	
	global $AcceptsFullScriptPath;
	
    $ScriptName = $_SERVER["PHP_SELF"];
    
    // depending on the capabilities of the server we are running on, we will 
    // return the appropriate script name
    if ($AcceptsFullScriptPath) 
    {
        // no changes needed
        $ScriptName = $ScriptName;	
    }
    else
    {	
        // this server does not accept the full path name in the script name
        // so we will isolate just the script name
        $ScriptArray = explode("/", $ScriptName);
        
        // the script name is the last entry in the array
        $ScriptNameSubscript = count($ScriptArray) - 1;
        if ($ScriptNameSubscript < 0) $ScriptNameSubscript = 0;
        $ScriptName = $ScriptArray[$ScriptNameSubscript];
    }

    // return the script name
	return $ScriptName;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: BuildGoBackParameters($Parameters)
// 
// PURPOSE: Format the parmeters passed in into a string that can be parsed into
//          parameters for returning from a script.
// 
// PARAMETERS:
//      Parameters - parameters to make up the goback parameters.
//
// RETURN:
//		The completed string.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function BuildGoBackParameters($Parameters)
{	
	$locGoBackParameters = str_replace("?", "_Q_", $Parameters);
	$locGoBackParameters = str_replace("&", "_A_", $locGoBackParameters);
	return $locGoBackParameters;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: StripGoBackParameters($Parameters)
// 
// PURPOSE: Remove special characters that we added to the goback parameters
//          so that the browser will now understand them.
// 
// PARAMETERS:
//      Parameters - goback parameters to clean up.
//
// RETURN:
//		The completed string.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function StripGoBackParameters($Parameters)
{
    $locGoBackParameters = str_replace("_Q_", "?", $Parameters);
    $locGoBackParameters = str_replace("_A_", "&", $locGoBackParameters);

	return $locGoBackParameters;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: CleanGoBackParameters($Parameters)
// 
// PURPOSE: Remove special characters that we added to the goback parameters
//          so that the browser will now understand them. Only remove the 
//          special characters from the first GoBackParameter field found.
// 
// PARAMETERS:
//      Parameters - goback parameters to clean up.
//
// RETURN:
//		The completed string.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function CleanGoBackParameters($Parameters)
{
    // GoBackParameters	string seperator
    $GoBackSeparator = "GoBackParameters";
    
    // since the goback and GoBackParameters may be nested, we only want to 
    // remove the special characters from everything up to the first occurance,
    // otherwise, the nested GoBackParameters will be removed.
	$ParameterArray = explode($GoBackSeparator, $Parameters);

	// process the parameters
	$LoopCounter = 0;
	foreach ($ParameterArray as $Item)
	{
	    // explode removes the separator so we need to add it back for
	    // all entries except the last
	    if ($LoopCounter < count($ParameterArray) - 1) 
	    {
	        $Item .= $GoBackSeparator;
	    }
	    
	    // if this is the first string (everything up to the first GoBackParameters),
	    // remove the special characters
	    if ($LoopCounter == 0)
	    {
            $locGoBackParameters = StripGoBackParameters($Item);
	    }
	    else
	    {
	        // not the first time, add the other GoBackParamters back to the string
	        $locGoBackParameters .= $Item;
	    }
	    $LoopCounter++;
	}

	return $locGoBackParameters;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: GenerateReturnURL($Page, $Parameters)
// 
// PURPOSE: Generate a return URL.
// 
// PARAMETERS:
//      Page - page name to return to
//      Parameters - parameters for the URL.
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function GenerateReturnURL($Page, $Parameters)
{	
	global $lang;

    // generate return URL if we have a valid page
    if (!empty($Page))
    {
        echo "<a href='$Page$Parameters'>" . $lang["returnprev"];
        echo "</a>";
    }
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: ValidateDate($Day, $Month, $Year)
// 
// PURPOSE: Make sure that the given date is a valid date. If the number of days
//          is too large for the given month, it will be reset to a valid number.
// 
// PARAMETERS:
//      Day - day of the month
//      Month - month of the year
//      Year - year
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function ValidateDate($Day, $Month, $Year)
{	
    // make sure the date values are valid
    if (empty($day))
    	$day   = date("d");
    if (empty($month))
    	$month = date("m");
    if (empty($year))
	    $year  = date("Y");

    // Make the date valid if day is more then number of days in month
	while (!checkdate($month, $day, $year))
		$day--;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: PrintAuthorizationHeader(
//                        $day, $month, $year, 
//                        $resource,  
//                        $resource_id,  
//                        $makemodel,  
//                        $ExtraParameters)
// 
// PURPOSE: Print the header at the top of the page for the authorization session 
//          type. Different links will be enabled depending on the user's status.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      resource - selected resource to pass to selected URLs
//      resource_id - selected resource ID to pass to selected URLs
//      makemodel - selected make and model to pass to selected URLs
//      ExtraParameters - extra parameters for URL links
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.32   Mar 8, 2005     JCovington Move offline functions into the online program.
// 
// -----------------------------------------------------------------------------
function PrintAuthorizationHeader(
                        $day, $month, $year, 
                        $resource, 
                        $resource_id, 
                        $makemodel, 
                        $ExtraParameters)
{
    global $lang, $AircraftScheduling_company, $search_str, $ses, $auth, $pview;
    global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    global $UserLevelMaintenance, $UserLevelAdmin;
    global $User_Must_Login;
    global $AllowAircraftCheckout;
    global $_SERVER;
	global $params;
		
	// authorization type is session
	if(user_logged_on()) 
	{ 
		// user is logged in, display the menu items
		
		// date selector
		?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ROWSPAN=2>
			<FORM NAME=BANNER ACTION=<?php echo "\"index.php\"";?> METHOD=GET>
				<FONT SIZE=2>
					<?php
					genDateSelector("", "BANNER", $day, $month, $year);
            	    BuildHiddenInputs($params);
					?>
					
					<INPUT TYPE=SUBMIT VALUE="<?php echo $lang["goto"] ?>">
				</FONT>
			</FORM>
		</TD>
		
		<?php
		// help column
        ?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="
			    <?php
			    echo "help.php" .
                        "?day=$day&month=$month&year=$year" .
			            "$params" .
                        "&goback=" . GetScriptName() .  
			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
			    ?>
			    ">
			    <?php echo $lang["help"] ?></A>
		</TD>
		
		<?php
		// logoff column
        ?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="
				<?php 
				echo "logoff.php?day=$day&month=$month&year=$year$params";
				?>
				">
				<?php echo $lang["logoff"] ?></A>
		</TD>

		<?php
		// change password column
        ?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="
				<?php 
				echo "change_pass.php?day=$day&month=$month&year=$year$params" .
                        "&goback=" . GetScriptName() .  
			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
				?>
				">
				<?php echo "$lang[change]<BR>$lang[password]" ?></A>
		</TD>
		<?php 

		// put up the menu selection based on the user's level
		if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice) 
		{ 
			// user modification menu item if the user is the office worker
		    ?>
			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
				<A HREF="admin.php?day=<?php echo $day ?>&month=<?php echo $month ?>&year=<?php echo "$year$params" ?>"><?php echo $lang["office"] ?></A>
			</TD>
		    <?php
		}
		else if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
		{ 
			// aircraft modification menu item if the user is the maintenance worker
		?>
			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
				<A HREF="admin.php?day=<?php echo $day ?>&month=<?php echo $month ?>&year=<?php echo "$year$params" ?>"><?php echo $lang["maintenance"] ?></A>
			</TD>
		<?php
		}
		else if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin) 
		{ 
			// admin menu item if the user is the administrator
		?>
			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
				<A HREF="admin.php?day=<?php echo $day ?>&month=<?php echo $month ?>&year=<?php echo "$year$params" ?>"><?php echo $lang["admin"] ?></A>
			</TD>
		<?php
		}
		else
		{
			// not an administrator, let them update their user information
			?>
			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
				<A HREF="
					<?php 
					echo "change_user_info.php?day=$day&month=$month&year=$year$params" .
                        "&goback=" . GetScriptName() .  
			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
					?>
					">
					<?php echo "$lang[change]<BR>$lang[userinfo]" ?></A>
			</TD>
			<?php
		}
		
		// report and search items
		?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="
				<?php 
				echo "report.php?day=$day&month=$month&year=$year$params";
				?>
				">
				<?php echo "$lang[report]" ?></A>
		</TD>

		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
		<FORM METHOD=GET ACTION="search.php">
		<FONT SIZE=2>Search: </FONT>
		<INPUT TYPE=TEXT   NAME="search_str" VALUE="<?php echo $search_str ?>" SIZE=10>
		<?php

		// if the aircraft checkout functions are enable, let them check their currency
		if ($AllowAircraftCheckout)
		{
		?>
		    </TR>
    		<?php
    		// check currency column
            ?>
		    <TR>
			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
				<A HREF="
				    <?php
				    echo "CheckCurrency.php" .
                            "?day=$day&month=$month&year=$year" .
				            "$params" .
                            "&goback=" . GetScriptName() .  
				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
				    ?>
				    ">
				    <?php echo $lang["CheckCurrency"] ?></A>
			</TD>

    		<?php
    		// display member charges column
            ?>
			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
				<A HREF="
				    <?php
				    echo "DisplayMemberCharges.php" .
                            "?day=$day&month=$month&year=$year" .
				            "$params" .
                            "&goback=" . GetScriptName() .  
				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
				    ?>
				    ">
				    <?php echo $lang["CheckCharges"] ?></A>
			</TD>

    		<?php
    		// check flight column
    		if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelSuper ||
    		    authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
        		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice ||
        		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
    		{
    		    // allow flight modifications for this user
    		    ?> 
    			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
    				<A HREF="
    				    <?php
    				    echo "DisplayFlights.php" .
                                "?day=$day&month=$month&year=$year" .
    				            "$params" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
    				    ?>
    				    ">
    				    <?php echo $lang["ModifyFlights"] ?></A>
    			</TD>
    			<?php
    		}
    		else
    		{ 
    		    // don't allow flight modifications for this user
    		    ?>
    			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
    			</TD>
    			<?php
    		}
    	}
            
        // export user data column
		echo "<TD CLASS='banner' BGCOLOR='#C0E0FF' ALIGN=CENTER>";
		echo "<A HREF='";
	    echo "ExportUserData.php" .
                "?day=$day&month=$month&year=$year" .
	            "$params" .
                "&goback=" . GetScriptName() .  
	            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
		echo "'>";
		echo $lang["ExportUserData"];
		echo "</A></TD>";

		// if the aircraft checkout functions are enable, let them check their currency
		if ($AllowAircraftCheckout)
		{
    		// aircraft checkout or checkin column
    		if (UserHasAircraftCheckedout(getUserName()))
    		{
    		    // user does have an aircraft checked out, let them checkin
                ?>
    			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER COLSPAN=2>
    				<A HREF="
    				    <?php
    				    echo "AircraftCheckin.php" .
                                "?day=$day&month=$month&year=$year" .
    				            "$params" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
    				    ?>
    				    ">
    				    <?php echo $lang["AircraftCheckin"] ?></A>
    			</TD>
    		<?php	
    		}
    		else
    		{
    		    // user does not have an aircraft checked out, let them checkout
                ?>
    			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER COLSPAN=2>
    				<A HREF="
    				    <?php
    				    echo "AircraftCheckout.php" .
                                "?day=$day&month=$month&year=$year" .
    				            "$params" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
    				    ?>
    				    ">
    				    <?php echo $lang["AircraftCheckout"] ?></A>
    			</TD>
    		<?php	
    		}
	    }
	}
	else
	{
		// user is not logged in, display the menu items
		
		// if the user isn't required to login to view, show the navigation
		// bar
		if (!$User_Must_Login)
		{
		?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF">
			<FORM NAME=BANNER ACTION=<?php echo "\"index.php\"";?> METHOD=GET>
				<FONT SIZE=2>
					<?php
					genDateSelector("", "BANNER", $day, $month, $year);
            	    BuildHiddenInputs($params);
					?>
					
					<INPUT TYPE=SUBMIT VALUE="<?php echo $lang["goto"] ?>">
				</FONT>
			</FORM>
		</TD>
		<?php
	    }
	    ?>

		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="
			    <?php
			    echo "help.php" .
			            "?day=$day&month=$month&year=$year" .
			            "$params" .
                        "&goback=" . GetScriptName() .  
			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
			    ?>
			    ">
			    <?php echo $lang["help"] ?></A>
		</TD>
		
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="
				<?php 
				    echo "logon.php" .
                            "?day=$day&month=$month&year=$year" .
				            "$params" .
                            "&goback=" . GetScriptName() .  
				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
				?>
				">
				<?php echo $lang["logon"] ?></A>
		</TD>


		<?php 
		// if the aircraft checkout functions are enabled, don't allow new users to register
        if(!$AllowAircraftCheckout)
        {
        ?>
			<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
				<A HREF="register.php"><?php echo $lang["register"] ?></A>
			</TD>
		<?php 
		}
	}			
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: PrintNonAuthorizationHeader(
//                        $day, $month, $year, 
//                        $resource,  
//                        $resource_id,  
//                        $makemodel,  
//                        $ExtraParameters)
// 
// PURPOSE: Print the header at the top of the page for non-authorization sessions.
//          Different links will be enabled depending on the user's status.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      resource - selected resource to pass to selected URLs
//      resource_id - selected resource ID to pass to selected URLs
//      makemodel - selected make and model to pass to selected URLs
//      ExtraParameters - extra parameters for URL links
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//      **** This procedure has not been tested since I only use authorization. ****
// 
// HISTORY:   Date:      Author:     Comment:
// 1.32   Mar 8, 2005     JCovington Move offline functions into the online program.
// 
// -----------------------------------------------------------------------------
function PrintNonAuthorizationHeader(
                        $day, $month, $year, 
                        $resource, 
                        $resource_id, 
                        $makemodel, 
                        $ExtraParameters)
{
	global $lang, $AircraftScheduling_company, $search_str, $ses, $auth, $pview;
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
    global $User_Must_Login;
    global $AllowAircraftCheckout;
	global $_SERVER;
	
	// authorization type is not session
	?>
	<TD CLASS="banner" BGCOLOR="#C0E0FF" ROWSPAN=2>
		<FORM NAME=BANNER ACTION=<?php echo "\"index.php\"";?> METHOD=GET>
			<FONT SIZE=2>
				<?php
				genDateSelector("", "BANNER", $day, $month, $year);
          	    BuildHiddenInputs($params);
				?>
				
				<INPUT TYPE=SUBMIT VALUE="<?php echo $lang["goto"] ?>">
			</FONT>
		</FORM>
	</TD>

	<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
		<A HREF="
		    <?php
		    echo "help.php" .
                    "?day=$day&month=$month&year=$year" .
		            "$params" .
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
		    ?>
		    ">
		    <?php echo $lang["help"] ?></A>
	</TD>

	<?php
	// if the aircraft checkout functions are enabled, let them check their currency
	if ($AllowAircraftCheckout)
	{
	?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="
			    <?php
			    echo "CheckCurrency.php" .
                        "?day=$day&month=$month&year=$year" .
			            "$params" .
                        "&goback=" . GetScriptName() .  
			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
			    ?>
			    ">
			    <?php echo $lang["CheckCurrency"] ?></A>
		</TD>
	<?php
    }
	?>

	<?php
	// if the aircraft checkout functions are enabled, let them check charges
	if ($AllowAircraftCheckout)
	{
	?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="
			    <?php
			    echo "DisplayMemberCharges.php" .
                        "?day=$day&month=$month&year=$year" .
			            "$params" .
                        "&goback=" . GetScriptName() .  
			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
			    ?>
			    ">
			    <?php echo $lang["CheckCharges"] ?></A>
		</TD>
	<?php
    }
	?>

	<?php 
	
	// put up the menu selection based on the user's level
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice) 
	{ 
		// user modification menu item if the user is the office worker
	?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="admin.php?day=<?php echo $day ?>&month=<?php echo $month ?>&year=<?php echo "$year$params" ?>"><?php echo $lang["office"] ?></A>
		</TD>
	<?php
	}
	else if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
	{ 
		// aircraft modification menu item if the user is the maintenance worker
	?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="admin.php?day=<?php echo $day ?>&month=<?php echo $month ?>&year=<?php echo "$year$params" ?>"><?php echo $lang["maintenance"] ?></A>
		</TD>
	<?php
	}
	else if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin) 
	{ 
		// admin menu item if the user is the administrator
	?>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
			<A HREF="admin.php?day=<?php echo $day ?>&month=<?php echo $month ?>&year=<?php echo "$year$params" ?>"><?php echo $lang["admin"] ?></A>
		</TD>
	<?php
	}
	
	?>
	<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
		<A HREF="report.php"><?php echo $lang["report"] ?></A>
	</TD>

	<TD CLASS="banner" BGCOLOR="#C0E0FF" ALIGN=CENTER>
	<FORM METHOD=GET ACTION="search.php">
	<FONT SIZE=2>Search: </FONT>
	<INPUT TYPE=TEXT   NAME="search_str" VALUE="<?php echo $search_str ?>" SIZE=10>
	<?php
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: print_header(
//                        $day, $month, $year, 
//                        $resource,  
//                        $resource_id,  
//                        $makemodel,  
//                        $ExtraParameters,
//                        $OnLoadProcedure = "")
// 
// PURPOSE: Print the header at the top of the page. Different links will be 
//          enabled depending on the user's status.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      resource - selected resource to pass to selected URLs
//      resource_id - selected resource ID to pass to selected URLs
//      makemodel - selected make and model to pass to selected URLs
//      ExtraParameters - extra parameters for URL links
//      OnLoadProcedure - optional. javascript to call after procedure is loaded
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function print_header(
                        $day, $month, $year, 
                        $resource, 
                        $resource_id, 
                        $makemodel, 
                        $ExtraParameters,
                        $OnLoadProcedure = "")
{
	global $lang, $AircraftScheduling_company, $search_str, $ses, $auth, $pview;
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
    global $User_Must_Login;
    global $AllowAircraftCheckout;
	global $_SERVER;
	global $CurrencyPrefix;
	global $params;
	
	# If we dont know the right date then make it up 
	if(!$day)
		$day   = date("d");
	if(!$month)
		$month = date("m");
	if(!$year)
		$year  = date("Y");
	if (empty($search_str))
		$search_str = "";
	if(empty($resource))
		$params = "";
	else
		$params = "&resource=$resource";
	if(!empty($resource_id))
		$params .= "&resource_id=$resource_id";
	if(!empty($InstructorResource))
		$params .= "&InstructorResource=$InstructorResource";
	if(!empty($ExtraParameters))
		$params .= $ExtraParameters;
	if(!empty($makemodel))
		$params .= $makemodel;
	?>
	
	<HTML>
	<HEAD>
	<TITLE>
	
	<?php 
	echo $lang["AircraftScheduling"] 
	?>
	
	</TITLE>
	
	<?php
    // save the input variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var CurrencyPrefix = '$CurrencyPrefix';";
    echo "</SCRIPT>";
	include "style.inc";
	include "JAVALib.inc"
	?>

	</HEAD>
	<?php 
	// if the onload procedure is specified, set the body to load it
	if (strlen($OnLoadProcedure) > 0)
	{
	    // onload procedure specified
	    echo "<BODY " . 
	            "onload='$OnLoadProcedure()' " . 
	            "onUnload='closePopWin()' " . 
	            "BGCOLOR='#ffffed' " . 
	            "TEXT=black " . 
	            "LINK='#5B69A6' " . 
	            "VLINK='#5B69A6' " . 
	            "ALINK=red>";
	}
	else
	{
	    // onload procedure not specified
	    echo "<BODY " . 
	            "onUnload='closePopWin()' " . 
	            "BGCOLOR='#ffffed' " . 
	            "TEXT=black " . 
	            "LINK='#5B69A6' " . 
	            "VLINK='#5B69A6' " . 
	            "ALINK=red>";
	}
	// if print view mode is selected, don't display the header
	if ( $pview != 1 ) 
	{
	?>
		<TABLE WIDTH="100%">
		<TR>
		<TD BGCOLOR="#5B69A6">
		<TABLE WIDTH="100%" BORDER=0>
		<TR>
		<TD CLASS="banner" BGCOLOR="#C0E0FF" ROWSPAN=2>
			<FONT SIZE=4><B><?php echo $AircraftScheduling_company ?></B><BR>
				<A HREF="index.php"><?php echo $lang["AircraftScheduling"] ?></A>
				
				<?php 
				if($auth["type"] == "session") 
				{ 
					if(user_logged_on()) 
					{
						// if we are logged on, put up the welcome information
						echo "<BR><B>" . $lang["welcome"] . getName() . "</B>"; 
					}
					else
					{
						// not logged in, tell the user
						echo "<BR><B>" . $lang["NotLogon"] . "</B>"; 
					}
				}
				?>		
			</FONT>
		</TD>
				
		<?php
		if($auth["type"] == "session") 
		{
			// authorization type is session
			PrintAuthorizationHeader(
                        $day, $month, $year, 
                        $resource, 
                        $resource_id, 
                        $makemodel, 
                        $ExtraParameters);
		}
		else
		{
			// authorization type is not session
			PrintNonAuthorizationHeader(
                        $day, $month, $year, 
                        $resource, 
                        $resource_id, 
                        $makemodel, 
                        $ExtraParameters);
        }
		?>
				
		<INPUT TYPE=HIDDEN NAME=day        VALUE="<?php echo $day        ?>"        >
		<INPUT TYPE=HIDDEN NAME=month      VALUE="<?php echo $month      ?>"        >
		<INPUT TYPE=HIDDEN NAME=year       VALUE="<?php echo $year       ?>"        >
		<?php
		if (!empty($resource))
			echo "<INPUT TYPE=HIDDEN NAME=area VALUE=$resource>"
		?>
		
		</FORM>
		</TD>
		</TR>
		</TABLE>
		</TD>
		</TR>
		</TABLE>
	<?php
	}
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: getAircraftMakeList($makemodel, $InstructorResource, $year, $month, $day)
// 
// PURPOSE: Get the list of aircraft makes from the database.
// 
// PARAMETERS:
//      makemodel - selected make and model to pass to selected URLs
//      InstructorResource - selected instructor resource
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function getAircraftMakeList($makemodel, $InstructorResource, $year, $month, $day)
{
	$ret = "";
	$name = key($makemodel);  // make|model
	$value = $makemodel[$name];  // C172|PA28-141
	$name = strtolower($name);
	$sql = "
			SELECT '$name' AS type, a.$name, count(b.aircraft_id) 
			FROM AircraftScheduling_$name a, AircraftScheduling_aircraft b, AircraftScheduling_resource c 
			WHERE b.resource_id=c.resource_id AND b.$name"."_id=a.$name"."_id 
			GROUP BY a.$name";
	$res = sql_query($sql);
	
	//  List makes in the database
	if($res) 
	{
		for ($c = 0; ($row = sql_row($res, $c)); $c++) 
		{
			if (empty($InstructorResource))
			{
				$ret .=  
						$_SERVER["PHP_SELF"] . 
						"?year=$year&month=$month&day=$day&resource=Aircraft&$name=" . 
						str_replace(" ", "+",$row[1]) . 
						", ";
			}
			else
			{
				$ret .=  
						$_SERVER["PHP_SELF"] . 
						"?year=$year&month=$month&day=$day&resource=Aircraft&InstructorResource=$InstructorResource&$name=" . 
						str_replace(" ", "+",$row[1]) . 
						", ";
			}
			$ret .= htmlspecialchars($row[1]) . " ($row[2])";
			$ret .= "<_>";
		}
	}
	return $ret;
}
	
//-----------------------------------------------------------------------------
// 
// FUNCTION: displayResourceMenuDay(
//                                $year, $month, $day, 
//                                $resource, 
//                                $InstructorResource, 
//                                $params, 
//                                $makemodel, 
//                                $Counts)
// 
// PURPOSE: Display the resource menus for the day screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      resource - selected resource to display menus for
//      InstructorResource - selected instructor resource to display menus for
//      makemodel - selected make and model
//      Counts - number of each resource found
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function displayResourceMenuDay(
                                $year, $month, $day, 
                                $resource, 
                                $InstructorResource, 
                                $params, 
                                $makemodel, 
                                $Counts)
{
	global $lang;
	
	// see if everything is selected
	if ($resource == "Aircraft" && 
		$InstructorResource == "Instructor" && 
		empty($params["model"]) && 
		empty($params["make"]))
		$EveryThing = 1;
	else
		$EveryThing = 0;
	
	// setup a table to show aircraft and instructor filters
	echo "<table border=0>";
	echo "<tr>";
	echo "<td>";

    // display the aircraft filter
	echo "Show Aircraft:";
	echo "</td>";
	
	echo "<td>";
	//  get the makes and models from the database
	$MakeArray = 
		preg_split("/<_>/", getAircraftMakeList(array("Make" => $params["make"]), $InstructorResource, $year, $month, $day));
	$ModelArray = 
		preg_split("/<_>/", getAircraftMakeList(array("Model" => $params["model"]), $InstructorResource, $year, $month, $day));

	// put up a drop down list to select the aircraft to display
	echo "<SELECT NAME='ResourceSelect' onChange=SelectAircraft()>";
	
	// option for all aircraft
	echo "<OPTION" . ($params["all"] == 1 && $resource == "Aircraft" && !$EveryThing ? " SELECTED" : "") . ">All Aircraft (" . $Counts["aircraft_count"] . ")";
	
	// process the makes
	foreach ($MakeArray as $Item)
	{
		$ItemArray = preg_split("/, /", $Item);		// 0 - link 1 - make name
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
			// remove the counts from the end of the string
			$MakeName = preg_split("/ /", $ItemArray[1]);
			echo "<OPTION" . 
					($resource == "Aircraft" && $params["make"] == $MakeName[0] ? " SELECTED" : "") . 
					">$ItemArray[1]";
		}
	}
	
	// process the models
	foreach ($ModelArray as $Item)
	{
		$ItemArray = preg_split("/, /", $Item);		// 0 - link 1 - model name
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
			// remove the counts from the end of the string
			$ModelName = preg_split("/ /", $ItemArray[1]);
			echo "<OPTION" . 
					($resource == "Aircraft" && $params["model"] == $ModelName[0] ? " SELECTED" : "") . 
					">$ItemArray[1]";
		}
	}
	
	// option for none
	echo "<OPTION" . ($resource == "None" ? " SELECTED" : "") . ">None";
	echo "</SELECT>";
	echo "</td>";
	
	// setup script to select link when the aircraft selection changes
	echo "<SCRIPT LANGUAGE=\"JavaScript\">";
	echo "function SelectAircraft()";
	echo "{";
	echo "	var LinkEntries = new Array;";

	// build the link entry array
	// All Aircraft
	$LinkEntriesCount = 0;
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["'DOCUMENT_ROOT'"] . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=Aircraft&InstructorResource=$InstructorResource\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// process the makes
	foreach ($MakeArray as $Item)
	{
		$ItemArray = preg_split("/, /", $Item);		// 0 - link 1 - make name
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
			echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $ItemArray[0] . "\";";
			$LinkEntriesCount = $LinkEntriesCount + 1;
		}
	}

	// process the models
	foreach ($ModelArray as $Item)
	{
		$ItemArray = preg_split("/, /", $Item);		// 0 - link 1 - model name
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
			echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $ItemArray[0] . "\";";
			$LinkEntriesCount = $LinkEntriesCount + 1;
		}
	}

	// None
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=None&InstructorResource=$InstructorResource$makemodel\";";
		
	echo "	window.location.href = LinkEntries[document.forms['main'].ResourceSelect.selectedIndex];";
	echo "}";
	echo "</SCRIPT>";
	
    // put instructor information in the next row of the table
	echo "<tr>";
	echo "<td>";

	// display the instructors filter
	echo "Show Instructors:";
	echo "</td>";

	echo "<td>";

	// put up a drop down list to select the instructors to display
	echo "<SELECT NAME='InstructSelect' onChange=SelectInstructor()>";
	
	// option for all instructors
	echo "<OPTION" . ($InstructorResource == "Instructor" && !$EveryThing ? " SELECTED" : "") . ">All Instructors (" . $Counts["instructor_count"] . ")";
	
	// show a link for A - G instructors
	echo "<OPTION" . ($InstructorResource == "InstructorAG" ? " SELECTED" : "") . ">Instructors A - G (" . $Counts["instructorAG_count"] . ")";
	
	// show a link for H - M instructors
	echo "<OPTION" . ($InstructorResource == "InstructorHM" ? " SELECTED" : "") . ">Instructors H - M (" . $Counts["instructorHM_count"] . ")";
	
	// show a link for N - S instructors
	echo "<OPTION" . ($InstructorResource == "InstructorNS" ? " SELECTED" : "") . ">Instructors N - S (" . $Counts["instructorNS_count"] . ")";
	
	// show a link for T - Z instructors
	echo "<OPTION" . ($InstructorResource == "InstructorTZ" ? " SELECTED" : "") . ">Instructors T - Z (" . $Counts["instructorTZ_count"] . ")";
	
	// option for none
	echo "<OPTION" . ($InstructorResource == "None" ? " SELECTED" : "") . ">None";
	echo "</SELECT>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";

	// setup script to select link when the aircraft selection changes
	echo "<SCRIPT LANGUAGE=\"JavaScript\">";
	echo "function SelectInstructor()";
	echo "{";
	echo "	var LinkEntries = new Array;";

	// build the link entry array
	// All Instructors
	$LinkEntriesCount = 0;
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=$resource&InstructorResource=Instructor$makemodel\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// A - G Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=$resource&InstructorResource=InstructorAG$makemodel\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// H - M Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=$resource&InstructorResource=InstructorHM$makemodel\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// N - S Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=$resource&InstructorResource=InstructorNS$makemodel\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// T - Z Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=$resource&InstructorResource=InstructorTZ$makemodel\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// None
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=$resource&InstructorResource=None$makemodel\";";
		
	echo "	window.location.href = LinkEntries[document.forms['main'].InstructSelect.selectedIndex];";
	echo "}";
	echo "</SCRIPT>";
	
	// space before next items
	echo "<br>";
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: displayResourceMenuWeekMonth(
//                                $year, $month, $day, 
//                                $resource, 
//                                $InstructorResource, 
//                                $params, 
//                                $makemodel, 
//                                $Counts)
// 
// PURPOSE: Display the resource menus for the week and month screens.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      resource - selected resource to display menus for
//      InstructorResource - selected instructor resource to display menus for
//      makemodel - selected make and model
//      Counts - number of each resource found
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function displayResourceMenuWeekMonth(
                                    $year, $month, $day, 
                                    $resource, 
                                    $InstructorResource, 
                                    $params, 
                                    $makemodel, 
                                    $Counts)
{
	global $lang;

	// process the filter entries
	echo "Select:";
	
	//  get the makes and models from the database
	$MakeArray = 
		preg_split("/<_>/", getAircraftMakeList(array("Make" => $params["make"]), $InstructorResource, $year, $month, $day));
	$ModelArray = 
		preg_split("/<_>/", getAircraftMakeList(array("Model" => $params["model"]), $InstructorResource, $year, $month, $day));

	// put up a drop down list to select the aircraft to display
	echo "<SELECT NAME='ResourceSelect' onChange=SelectFilter()>";
	
	// option for all aircraft
	echo "<OPTION" . ($params["all"] == 1 && $resource == "Aircraft" ? " SELECTED" : "") . ">All Aircraft (" . $Counts["aircraft_count"] . ")";
	
	// process the makes
	foreach ($MakeArray as $Item)
	{
		$ItemArray = preg_split("/, /", $Item);		// 0 - link 1 - make name
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
			// remove the counts from the end of the string
			$MakeName = preg_split("/ /", $ItemArray[1]);
			echo "<OPTION" . 
					($params["make"] == $MakeName[0] ? " SELECTED" : "") . 
					">$ItemArray[1]";
		}
	}
	
	// process the models
	foreach ($ModelArray as $Item)
	{
		$ItemArray = preg_split("/, /", $Item);		// 0 - link 1 - model name
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
			// remove the counts from the end of the string
			$ModelName = preg_split("/ /", $ItemArray[1]);
			echo "<OPTION" . 
					($params["model"] == $ModelName[0] ? " SELECTED" : "") . 
					">$ItemArray[1]";
		}
	}
	
	// option for all instructors
	echo "<OPTION" . ($resource == "Instructor" ? " SELECTED" : "") . ">All Instructors (" . $Counts["instructor_count"] . ")";
	
	// show a link for A - G instructors
	echo "<OPTION" . ($resource == "InstructorAG" ? " SELECTED" : "") . ">Instructors A - G (" . $Counts["instructorAG_count"] . ")";
	
	// show a link for H - M instructors
	echo "<OPTION" . ($resource == "InstructorHM" ? " SELECTED" : "") . ">Instructors H - M (" . $Counts["instructorHM_count"] . ")";
	
	// show a link for N - S instructors
	echo "<OPTION" . ($resource == "InstructorNS" ? " SELECTED" : "") . ">Instructors N - S (" . $Counts["instructorNS_count"] . ")";
	
	// show a link for T - Z instructors
	echo "<OPTION" . ($resource == "InstructorTZ" ? " SELECTED" : "") . ">Instructors T - Z (" . $Counts["instructorTZ_count"] . ")";
	
	// show a link for everything
	echo "<OPTION" . ($resource == "Everything" ? " SELECTED" : "") . ">Everything (" . $Counts["everything_count"] . ")";
	echo "</SELECT>";
	
	// setup script to select link when the aircraft selection changes
	echo "<SCRIPT LANGUAGE=\"JavaScript\">";
	echo "function SelectFilter()";
	echo "{";
	echo "	var LinkEntries = new Array;";

	// build the link entry array
	// All Aircraft
	$LinkEntriesCount = 0;
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=Aircraft&all=1\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// process the makes
	foreach ($MakeArray as $Item)
	{
		$ItemArray = preg_split("/, /", $Item);		// 0 - link 1 - make name
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
			echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $ItemArray[0] . "\";";
			$LinkEntriesCount = $LinkEntriesCount + 1;
		}
	}

	// process the models
	foreach ($ModelArray as $Item)
	{
		$ItemArray = preg_split("/, /", $Item);		// 0 - link 1 - model name
		if (strlen($ItemArray[0]) > 0 && strlen($ItemArray[1]) > 0)
		{
			echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $ItemArray[0] . "\";";
			$LinkEntriesCount = $LinkEntriesCount + 1;
		}
	}

	// add the instructors to the link entry array
	// All Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=Instructor&all=1\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// A - G Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=InstructorAG&all=1\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// H - M Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=InstructorHM&all=1\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// N - S Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=InstructorNS&all=1\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// T - Z Instructors
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=InstructorTZ&all=1\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;

	// Everything
	echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $_SERVER["PHP_SELF"] . 
			"?year=$year&month=$month&day=$day&resource=Everything\";";
	$LinkEntriesCount = $LinkEntriesCount + 1;
		
	echo "	window.location.href = LinkEntries[document.forms['main'].ResourceSelect.selectedIndex];";
	echo "}";
	echo "</SCRIPT>";
}


//-----------------------------------------------------------------------------
// 
// FUNCTION: displayResourceMenu(
//                                $ViewType, 
//                                $year, $month, $day, 
//                                $resource, 
//                                $InstructorResource, 
//                                $params, 
//                                $makemodel)
// 
// PURPOSE: Display the resource menus.
// 
// PARAMETERS:
//      ViewType - screen type (day, week or month) for the resource menu
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      resource - selected resource to display menus for
//      InstructorResource - selected instructor resource to display menus for
//      makemodel - selected make and model
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function displayResourceMenu(
                            $ViewType, 
                            $year, $month, $day, 
                            $resource, 
                            $InstructorResource, 
                            $params, 
                            $makemodel)
{
	global $lang;
	global $AircraftScheduleType, $InstructorScheduleType;
	
	// get the counts of the resources
	$Counts["aircraft_count"] = sql_query1("
								SELECT count(*) 
								FROM AircraftScheduling_aircraft 
								LEFT JOIN AircraftScheduling_resource USING (resource_id)
								WHERE schedulable_id=$AircraftScheduleType");
	$Counts["instructorAG_count"] = sql_query1("
								SELECT count(*) 
								FROM AircraftScheduling_resource R, AircraftScheduling_instructors I
								LEFT JOIN AircraftScheduling_person P ON I.person_id=P.person_id
								WHERE schedulable_id=$InstructorScheduleType AND
									R.item_id=I.instructor_id AND
									ucase(left(last_name, 1)) >= 'A' AND 
									ucase(left(last_name, 1)) <= 'G'
									");
	$Counts["instructorHM_count"] = sql_query1("
								SELECT count(*) 
								FROM AircraftScheduling_resource R, AircraftScheduling_instructors I
								LEFT JOIN AircraftScheduling_person P ON I.person_id=P.person_id
								WHERE schedulable_id=$InstructorScheduleType AND
									R.item_id=I.instructor_id AND
									ucase(left(last_name, 1)) >= 'H' AND
									ucase(left(last_name, 1)) <= 'M'
									");
	$Counts["instructorNS_count"] = sql_query1("
                                SELECT count(*) 
								FROM AircraftScheduling_resource R, AircraftScheduling_instructors I
								LEFT JOIN AircraftScheduling_person P ON I.person_id=P.person_id
								WHERE schedulable_id=$InstructorScheduleType AND
									R.item_id=I.instructor_id AND
									ucase(left(last_name, 1)) >= 'N' AND
									ucase(left(last_name, 1)) <= 'S'
									");
	$Counts["instructorTZ_count"] = sql_query1("
								SELECT count(*) 
								FROM AircraftScheduling_resource R, AircraftScheduling_instructors I
								LEFT JOIN AircraftScheduling_person P ON I.person_id=P.person_id
								WHERE schedulable_id=$InstructorScheduleType AND
									R.item_id=I.instructor_id AND
									ucase(left(last_name, 1)) >= 'T' AND
									ucase(left(last_name, 1)) <= 'Z'
									");
	$Counts["instructor_count"] = $Counts["instructorAG_count"] + $Counts["instructorHM_count"] + $Counts["instructorNS_count"] + $Counts["instructorTZ_count"];
	$Counts["everything_count"] = $Counts["aircraft_count"] + $Counts["instructor_count"];
	
	// get the items to display
	$sql = "select schedulable_id, name, display_page from AircraftScheduling_schedulable WHERE schedulable=1 order by name";
	$res = sql_query($sql);
	
	// display the correct resource menu for the type of display (day, week or month)
	if ($ViewType == "DAY")
		displayResourceMenuDay(
								$year, 
								$month, 
								$day, 
								$resource, 
								$InstructorResource, 
								$params, 
								$makemodel,
								$Counts);
	else
		displayResourceMenuWeekMonth(
								$year, 
								$month, 
								$day, 
								$resource, 
								$InstructorResource, 
								$params, 
								$makemodel,
								$Counts);
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: displayUserInformation(
// 								$NewUser,
// 								$ScriptName,
// 								$ScriptUserLevel,
// 								$EditUserName,
// 								$EditFirstName,
// 								$EditLastName,
// 								$EditEmail,
// 								$EditPersonID,
// 								$EditUserLevel,
// 								$EditCounter,
// 								$EditMiddleName,
// 								$EditPassword,
// 								$EditTitle,
// 								$EditAddress1,
// 								$EditAddress2,
// 								$EditCity,
// 								$EditState,
// 								$EditZip,
// 								$EditPhoneNumber,
// 								$AllowPhoneNumberDisplay,
// 								$EditHomePhone,
// 								$InstructorResourceID,
// 								$InstructorsDescription,
// 								$InstructorPicture
// 								)
// 
// PURPOSE: Displays the user information for edit.
// 
// PARAMETERS:
//      makemodel - selected make and model to pass to selected URLs
//      NewUser - set true if this form is for a new user, false for existing user
//      ScriptName - name of the script of the caller
//      ScriptUserLevel - the user level of the calling script
//      EditUserName - user name for the user to edit
//      EditFirstName - first name for the user to edit
//      EditLastName - last name for the user to edit
//      EditEmail - email for the user to edit
//      EditPersonID - database person id for the user to edit
//      EditUserLevel - user level for the user
//      EditCounter - login counter for the user to edit
//      EditMiddleName - middle name for the user to edit
//      EditPassword - password for the user to edit
//      EditTitle - title for the user to edit
//      EditAddress1 - address line 1 for the user to edit
//      EditAddress2 - address line 2 for the user to edit
//      EditCity - city for the user to edit
//      EditState - state for the user to edit
//      EditZip - zip for the user to edit
//      EditPhoneNumber - phone number for the user to edit
//      AllowPhoneNumberDisplay - set to 1 to allow phone number displays
//      EditHomePhone - second phone number for the user to edit
//      InstructorResourceID - set to instructor id if the user information is for an instructor
//      InstructorsDescription - instructor description to edit
//      InstructorPicture - set to one if the instructor picture is enabled
//
// RETURN:
//		true
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function displayUserInformation(
								$NewUser,
								$ScriptName,
								$ScriptUserLevel,
								$EditUserName,
								$EditFirstName,
								$EditLastName,
								$EditEmail,
								$EditPersonID,
								$EditUserLevel,
								$EditCounter,
								$EditMiddleName,
								$EditPassword,
								$EditTitle,
								$EditAddress1,
								$EditAddress2,
								$EditCity,
								$EditState,
								$EditZip,
								$EditPhoneNumber,
								$AllowPhoneNumberDisplay,
								$EditHomePhone,
								$InstructorResourceID,
								$InstructorsDescription,
								$InstructorPicture
								)
{
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
	global $UserLevelDisabledName, $UserLevelNormalName, $UserLevelSuperName, $UserLevelOfficeName; 
	global $UserLevelMaintenanceName, $UserLevelAdminName;
	global $enablessl;
	
	// set the size of the input boxes
	$InputBoxSize = 25;

	// put up the information form with the user information filled in
	if (!$NewUser) echo "<h2>Edit UserName: $EditUserName (" . buildName($EditFirstName, $EditLastName) . ")</h2>"; 
	echo "<center>";
	echo "<table border=0 width='100%'>";
	echo "<caption>Required items are in <font color=\"red\">red</font></caption>";
	echo "<tr><td width='30%'>";
	echo "<table border=0>";
	
    // if the user is being forced to update their information
    if ($ScriptName == "UserInformation")
    {
		// let them set a new password
    	echo "<INPUT TYPE=hidden NAME=\"username\" VALUE=\"" . $EditUserName . "\">";
		echo "<tr><td><font color=\"red\">Password</font></td><td><INPUT TYPE=password NAME=\"password\" VALUE=\"\" SIZE=$InputBoxSize MAXLENGTH=32></td></tr>";
		echo "<tr><td>Re-type <font color=\"red\">password</font></td><td><INPUT TYPE=password NAME=\"password2\" VALUE=\"\" SIZE=$InputBoxSize MAXLENGTH=32></td></tr>";
    }
    else
    {
    	// username, password, retype password
    	if ($ScriptUserLevel == $UserLevelAdmin || $ScriptUserLevel == $UserLevelOffice) 
    	{
    		// administrator or office user - let them see the username and password, allow changes
    		echo "<tr><td><font color=\"red\">Username</font></td><td><INPUT NAME=\"username\" VALUE=\"" . $EditUserName . "\" SIZE=$InputBoxSize MAXLENGTH=20></td></tr>";
    		echo "<tr><td><font color=\"red\">Password</font></td><td><INPUT NAME=\"password\" VALUE=\"" . $EditPassword . "\" SIZE=$InputBoxSize MAXLENGTH=32></td></tr>";
    		echo "<tr><td>Re-type <font color=\"red\">password</font></td><td><INPUT NAME=\"password2\" VALUE=\"" . $EditPassword . "\" SIZE=$InputBoxSize MAXLENGTH=32></td></tr>";
    	}
    	else if ($ScriptUserLevel == $UserLevelNormal || $ScriptUserLevel == $UserLevelSuper)
    	{
    		// normal user or super user - don't let them change the username or password
    		echo "<INPUT TYPE=hidden NAME=\"username\" VALUE=\"" . $EditUserName . "\">";
    		echo "<tr><td><font color=\"red\">Password</font></td><td><INPUT TYPE=password NAME=\"password\" VALUE=\"\" SIZE=$InputBoxSize MAXLENGTH=32></td></tr>";
    	}
    	else
    	{
    		// everybody else - let them set a new username and password
    		echo "<tr><td><font color=\"red\">Username</font></td><td><INPUT NAME=\"username\" VALUE=\"" . $EditUserName . "\" SIZE=$InputBoxSize MAXLENGTH=20></td></tr>";
    		echo "<tr><td><font color=\"red\">Password</font></td><td><INPUT TYPE=password NAME=\"password\" VALUE=\"" . $EditPassword . "\" SIZE=$InputBoxSize MAXLENGTH=32></td></tr>";
    		echo "<tr><td>Re-type <font color=\"red\">password</font></td><td><INPUT TYPE=password NAME=\"password2\" VALUE=\"" . $EditPassword . "\" SIZE=$InputBoxSize MAXLENGTH=32></td></tr>";
    	}
    }
    
    // display phone number choice
	echo "<tr>";
	echo "<td align=center colspan=2>";
	echo "<input type=checkbox name=Allow_Phone_Number_Display value=1 ";
	if ($AllowPhoneNumberDisplay == 1) echo "CHECKED";
	echo ">List Phone Numbers Online";
	echo "</td>";
	echo "</tr>";
	
	// user information
	echo "</table></td>";
	echo "<td width='70%'>";
	echo "<table border=0>";
	echo "<tr>";
	echo "<td>Title</td>";
	echo "<td><font color=\"red\">First Name</font></td>";
	echo "<td>Middle Name</td>";
	echo "<td><font color=\"red\">Last Name</font></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><INPUT NAME=\"title\" SIZE=$InputBoxSize MAXLENGTH=24 VALUE=\"" . stripslashes($EditTitle) . "\"></td>";
	echo "<td><INPUT NAME=\"first_name\" SIZE=$InputBoxSize MAXLENGTH=16 VALUE=\"" . stripslashes($EditFirstName) . "\"></td>";
	echo "<td><INPUT NAME=\"middle_name\" SIZE=$InputBoxSize MAXLENGTH=16 VALUE=\"" . stripslashes($EditMiddleName) . "\"></td>";
	echo "<td><INPUT NAME=\"last_name\" SIZE=$InputBoxSize MAXLENGTH=24 VALUE=\"" . stripslashes($EditLastName) . "\"></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><font color=\"red\">Email</font></td>";
	echo "<td>Address line 1</td>";
	echo "<td>Address line 2</td>";
	echo "<td>City</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><INPUT NAME=\"email\" SIZE=$InputBoxSize MAXLENGTH=64 VALUE=\"" . stripslashes($EditEmail) . "\"></td>";
	echo "<td><INPUT NAME=\"address1\" SIZE=$InputBoxSize MAXLENGTH=128 VALUE=\"" . stripslashes($EditAddress1) . "\"></td>";
	echo "<td><INPUT NAME=\"address2\" SIZE=$InputBoxSize MAXLENGTH=128 VALUE=\"" . stripslashes($EditAddress2) . "\"></td>";
	echo "<td><INPUT NAME=\"city\" SIZE=$InputBoxSize MAXLENGTH=64 VALUE=\"" . stripslashes($EditCity) . "\"></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>State</td>";
	echo "<td>Zip Code</td>";
	echo "<td>User Level</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><INPUT NAME=\"state\" SIZE=4 MAXLENGTH=2 VALUE=\"" . $EditState . "\"></td>";
	echo "<td><INPUT NAME=\"zip\" SIZE=12 MAXLENGTH=10 VALUE=\"" . $EditZip . "\"></td>";

    // setup the user level types
	$UserLevelTypes[$UserLevelDisabled] = $UserLevelDisabledName;
	$UserLevelTypes[$UserLevelNormal] = $UserLevelNormalName;
	$UserLevelTypes[$UserLevelSuper] = $UserLevelSuperName;
	$UserLevelTypes[$UserLevelOffice] = $UserLevelOfficeName;
	$UserLevelTypes[$UserLevelMaintenance] = $UserLevelMaintenanceName;
	$UserLevelTypes[$UserLevelAdmin] = $UserLevelAdminName;
	
    // if the user is being forced to update their information
    if ($ScriptName == "UserInformation")
    {
		// display the userlevel, but don't let them change it
		echo "<td><B>$UserLevelTypes[$EditUserLevel]</B></td>";
    	echo "<INPUT TYPE=hidden NAME=\"user_level\" VALUE=\"" . $EditUserLevel . "\">";
    }
    else
    {
    	// if we are an admistrator, allow the user level to be changed
    	if ($ScriptUserLevel == $UserLevelAdmin || $ScriptUserLevel == $UserLevelOffice)
    	{
    		// user is an administrator or office user
    		echo "<td><SELECT NAME='user_level'>";
    		for($i = 0; $i <= $UserLevelAdmin; $i++)
    			echo "<OPTION VALUE=" . $i . ($i == $EditUserLevel ? " SELECTED" : "") . ">$UserLevelTypes[$i]";
    		echo "</SELECT></td>";
    	}
    	else
    	{
    		// not an administrator or office user
    		echo "<td><B>$UserLevelTypes[$EditUserLevel]</B></td>";
    	}
    }
	echo "</tr>";

	echo "<tr>";
	echo "<td><font color=\"red\">Phone Number 1</font></td>";
	echo "<td>Phone Number 2</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td><INPUT NAME=\"phone_number\" SIZE=$InputBoxSize MAXLENGTH=25 VALUE=\"" . stripslashes($EditPhoneNumber) . "\"></td>";
	echo "<td><INPUT NAME=\"Home_Phone\" SIZE=$InputBoxSize MAXLENGTH=25 VALUE=\"" . stripslashes($EditHomePhone) . "\"></td>";
	echo "</tr>";
	
	// if this is an instructor
	if ($InstructorResourceID > 0)
    {
        // put up the instructor information so it can be modified
        echo "<tr>";
        echo "<td colspan=2>Instructor Description</td>";
        echo "</tr>";
            
        echo "<tr>";
        echo "<td colspan=2><input name='InstructorsDescription' size=56 value='" . stripslashes($InstructorsDescription) . "'></td>";
        echo "</tr>";
    	echo "</table>";
    	echo "</td>";
    	echo "</tr>";
    	echo "</table>";
    	echo "</td>";
     	echo "</tr>";     
    
        // generate the picture upload information
        echo "<br>";
        echo "<table border=0>";
        if(!empty($InstructorPicture)) 
        {
            echo "<caption>" . 
                 "Note: Picture scale automatically adjusted to 300 pixels in width for " .
                 "display on this page. Click on the image to view the full size. " . 
                 "It is not necessary to upload the image again if you are satisfied with the following picture:" .
                 "</caption>";
        }
        echo "<tr>";
        echo "<td>Picture:</td>";
        echo "<td>";
        echo "<input type='file' name='picture'>";
        echo "<br>";
        if(!empty($InstructorPicture)) 
        {
            echo "Delete Picture: <input type='checkbox' name='delete_picture'>";
            echo "</td>";
            echo "<td>";
            echo "<a href=\"image.php?src=" . $InstructorPicture . "\">";
            echo "<img src=\"image.php?src=" . $InstructorPicture . "&width=300\"></a>";
            echo "<input type=\"hidden\" name=\"old_picture\" value=\"". $InstructorPicture . "\">";
        }
        echo "</td>";
        echo "</tr>";
        echo "</table>";
   	
    	echo "</table>";
    	echo "</td>";
    	echo "</tr>";
    	echo "</table>";
    }
    else
    {
        // not an instructor, close out the table
    	echo "</table>";
    	echo "</td>";
    	echo "</tr>";
    	echo "</table>";
    }
	
	echo "</center>";
	
	// save the instructor information
    echo "<INPUT TYPE=hidden NAME='InstructorResourceID' VALUE=" . $InstructorResourceID . ">";
		
	?>
	
	<SCRIPT LANGUAGE="JavaScript">
	function do_submit() 
	{
		<?php if($enablessl && !isset($HTTPS)) { ?>
		
		if(document.forms["<?php echo $ScriptName ?>"].usessl.checked == true)
			document.forms["<?php echo $ScriptName ?>"].action = <?php echo "\"https://" . getenv("SERVER_NAME") . getenv("SCRIPT_NAME") . "\";" ?>
		
		<?php } ?>
	
    	<?php 
        // if the user is being forced to update there information
        if ($ScriptName == "UserInformation")
        {
    		// forcing the user to update their data. make sure they enter a password
    	    ?>
			if(document.forms["<?php echo $ScriptName ?>"].password.value == "") 
			{
				alert("Please enter a password");
				return false;
			}
			if(document.forms["<?php echo $ScriptName ?>"].password.value != document.forms["<?php echo $ScriptName ?>"].password2.value) 
			{
				alert("Your passwords don't match!");
				return false;
			}
       		<?php 
        }
        else
        {
    		if ($ScriptUserLevel == $UserLevelAdmin || $ScriptUserLevel == $UserLevelOffice) 
    		{
    			// administrator or office user- let them see the username and password, allow changes
    			?>
    			if(document.forms["<?php echo $ScriptName ?>"].username.value == "") 
    			{
    				alert("Please enter a username!");
    				return false;
    			}
    			if(document.forms["<?php echo $ScriptName ?>"].password.value == "") 
    			{
    				alert("Please enter a password");
    				return false;
    			}
    			if(document.forms["<?php echo $ScriptName ?>"].password.value != document.forms["<?php echo $ScriptName ?>"].password2.value) 
    			{
    				alert("Your passwords don't match!");
    				return false;
    			}
    			<?php
    		}
    		else if ($ScriptUserLevel == $UserLevelNormal || $ScriptUserLevel == $UserLevelSuper)
    		{
    			// normal user or super user - validate the password
    			?>
    			if(document.forms["<?php echo $ScriptName ?>"].password.value == "") 
    			{
    				alert("Please enter your password to update your user information");
    				return false;
    			}
    			<?php
    		}
    		else
    		{
    			// everybody else - let them set a new username and password
    			?>
    			if(document.forms["<?php echo $ScriptName ?>"].username.value == "") 
    			{
    				alert("Please enter a username!");
    				return false;
    			}
    			if(document.forms["<?php echo $ScriptName ?>"].password.value == "") 
    			{
    				alert("Please enter a password");
    				return false;
    			}
    			if(document.forms["<?php echo $ScriptName ?>"].password.value != document.forms["<?php echo $ScriptName ?>"].password2.value) 
    			{
    				alert("Your passwords don't match!");
    				return false;
    			}
    			<?php
    		}
		}
		?>
		
		if(document.forms["<?php echo $ScriptName ?>"].first_name.value == "") 
		{
			alert("Please enter your first name");
			return false;
		}
		if(document.forms["<?php echo $ScriptName ?>"].last_name.value == "") 
		{
			alert("Please enter your last name");
			return false;
		}
		if(document.forms["<?php echo $ScriptName ?>"].email.value == "") 
		{
			alert("Please enter an Email address!");
			return false;
		}
		if(document.forms["<?php echo $ScriptName ?>"].phone_number.value == "") 
		{
			alert("Please enter a phone number!");
			return false;
		}
		
		document.forms["<?php echo $ScriptName ?>"].submit();
		return true;
	}
	</SCRIPT>
	<?php
	
	return true;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: toTimeString(&$dur, &$units)
// 
// PURPOSE: Convert the time duration into a number of hours, minutes or seconds
//          expressed in units.
// 
// PARAMETERS:
//      dur - currently selected day for the date selector
//      units - currently selected month for the date selector
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function toTimeString(&$dur, &$units)
{
	global $lang;
	if($dur >= 60)
	{
		$dur /= 60;
		
		if($dur >= 60)
		{
			$dur /= 60;
			
			if(($dur >= 24) && ($dur % 24 == 0))
			{
				$dur /= 24;
				
				if(($dur >= 7) && ($dur % 7 == 0))
				{
					$dur /= 7;
					
					if(($dur >= 52) && ($dur % 52 == 0))
					{
						$dur  /= 52;
						$units = $lang["years"];
					}
					else
						$units = $lang["weeks"];
				}
				else
					$units = $lang["days"];
			}
			else
				$units = $lang["hours"];
		}
		else
			$units = $lang["minutes"];
	}
	else
		$units = $lang["seconds"];
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: genDateSelector(
//                            $prefix, 
//                            $FormName, 
//                            $day, 
//                            $month, 
//                            $year, 
//                            $OnChangeHandler,
//                            $JavaHandler)
// 
// PURPOSE: Generates a date selector for choosing the month, day and year.
// 
// PARAMETERS:
//      prefix - prefix for the control names
//      FormName - name of the form that contains the control
//      day - the current day of the month
//      month - the current month of the year
//      year - the current year
//      OnChangeHandler - optional. javascript OnChange procedure
//      JavaHandler - optional. javascript procedure to call after a change is made
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function genDateSelector(
                        $prefix, 
                        $FormName, 
                        $day, 
                        $month, 
                        $year, 
                        $OnChangeHandler = "",
                        $JavaHandler = "")
{
    global $ImageRootPath;
    global $pview;
    
	if($day   == 0) $day = date("d");
	if($month == 0) $month = date("m");
	if($year  == 0) $year = date("Y");
	
    // day selector
	if (strlen($OnChangeHandler) > 0)
	    echo "<SELECT NAME='${prefix}day' id='${prefix}day' onChange=$OnChangeHandler()>\n";
	else
	    echo "<SELECT NAME='${prefix}day' id='${prefix}day'>\n";
	
	for($i = 1; $i <= 31; $i++)
		echo "<OPTION VALUE=\"$i\"" . ($i == $day ? " SELECTED" : "") . ">$i\n";
	
	echo "</SELECT>";
	
	// month selector
	if (strlen($OnChangeHandler) > 0)
    	echo "<SELECT NAME='${prefix}month' id='${prefix}month' onChange=$OnChangeHandler()>\n";
    else
    	echo "<SELECT NAME='${prefix}month' id='${prefix}month'>\n";
	
	for($i = 1; $i <= 12; $i++)
	{
		$m = strftime("%b", mktime(0, 0, 0, $i, 1, $year));
		
		print "<OPTION VALUE=\"$i\"" . ($i == $month ? " SELECTED" : "") . ">$m\n";
	}
	
	echo "</SELECT>";
	
	// year selector
	if (strlen($OnChangeHandler) > 0)
	    echo "<SELECT NAME='${prefix}year' id='${prefix}year' onChange=$OnChangeHandler()>\n";
	else
	    echo "<SELECT NAME='${prefix}year' id='${prefix}year'>\n";
	
	$min = min($year, date("Y")) - 5;
	$max = max($year, date("Y")) + 5;
	
	for($i = $min; $i <= $max; $i++)
		print "<OPTION VALUE=\"$i\"" . ($i == $year ? " SELECTED" : "") . ">$i\n";
	
	echo "</SELECT>";
	
    // don't generate popcal buttons in print view
    if (!$pview)
    {
    	// generate the popup calendar button
    	echo "<nbr>";
    	echo "<a href=\"javascript:popcal(document.$FormName.${prefix}day.selectedIndex + 1, " .  
    										"'document.$FormName.${prefix}day'," . 
    										"document.$FormName.${prefix}month.selectedIndex + 1, " .
    										"'document.$FormName.${prefix}month', " .
    										"document.$FormName.${prefix}year.selectedIndex + $min, " .
    										"'document.$FormName.${prefix}year', " .
    										"$min, " .
    										"'$JavaHandler');\">";
     	echo "	<img src='" . $ImageRootPath . "calendar.gif' border=0 alt='click here to edit the date'></a>";
    	echo "</nobr>";
    }
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: fatal_error($need_header, $message)
// 
// PURPOSE: Used to display serious errors such as database errors without 
//          sending incomplete HTML pages. This is only used for errors which 
//          "should never happen", not those caused by bad inputs. If 
//          $need_header!=0 output the top of the page too, else assume the
//          caller did that. Alway outputs the bottom of the page and exits.
// 
// PARAMETERS:
//      need_header - outputs HTML header if set to true
//      message - the error message to display
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function fatal_error($need_header, $message)
{
	global $lang;
	if ($need_header) print_header(0, 0, 0, 0, 0, 0, "");
	echo $message;
	include "trailer.inc";
	exit;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: slashes($s)
// 
// PURPOSE: Apply backslash-escape quoting. Use this for GET/POST form parameters.
// 
// PARAMETERS:
//      s - string to add slashes to
//
// RETURN:
//		The string with backslash-escape quoting applied.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function slashes($s)
{
	return addslashes($s);
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: unslashes($s)
// 
// PURPOSE: Remove backslash-escape quoting. Use this whenever you need the 
//          actual value of a GET/POST form parameter.
// 
// PARAMETERS:
//      s - string to remove slashes from
//
// RETURN:
//		The string with backslash-escape quoting removed.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function unslashes($s)
{
	return stripslashes($s);
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: get_default_resource()
// 
// PURPOSE: Return a default area; used if no area is already known. This returns the
//          lowest area ID in the database (no guaranty there is an area 1).
//          This could be changed to implement something like per-user defaults.
// 
// PARAMETERS:
//      none
//
// RETURN:
//		The default resource from the database.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function get_default_resource()
{
	$res = sql_query(
	        "SELECT name " . 
	        "FROM AircraftScheduling_schedulable " .
	        "WHERE schedulable=1 ". 
	        "ORDER BY schedulable_id");
    if($res && ($row = sql_row($res, 0)))
	  return $row[0];
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: day_name($daynumber)
// 
// PURPOSE: Get the local day name based on language. Note 2000-01-02 is a Sunday.
// 
// PARAMETERS:
//      daynumber - the number of days since January 2, 2000.
//
// RETURN:
//		The name of the day.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function day_name($daynumber)
{
	return strftime("%A", mktime(0, 0, 0, 1, 2+$daynumber, 2000));
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: tdcell($colclass)
// 
// PURPOSE: Output a start table cell tag <td> with color class and fallback 
//          color. The colors for CSS browsers can be found in the style sheet.
//          The colors in the array below are fallback for non-CSS browsers only.
// 
// PARAMETERS:
//      colclass - an entry type (A-J), "white" for empty, or "red" for highlighted.
//
// RETURN:
//		none.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function tdcell($colclass)
{
	# This should be 'static $ecolors = array(...)' but that crashes PHP3.0.12!
	static $ecolors;
	if (!isset($ecolors)) $ecolors = array("A"=>"#FFCCFF", "B"=>"#99CCCC",
		"C"=>"#FF9999", "D"=>"#FFFF99", "E"=>"#C0E0FF", "F"=>"#FFCC99",
		"G"=>"#FF6666", "H"=>"#66FFFF", "I"=>"#DDFFDD", "J"=>"#CCCCCC",
		"red"=>"#FFF0F0", "white"=>"#FFFFFF");
	if (isset($ecolors[$colclass]))
		echo "<td class=\"$colclass\" bgcolor=\"$ecolors[$colclass]\">";
	else
		echo "<td class=\"$colclass\">";
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: show_colour_key()
// 
// PURPOSE: Display the entry-type color key. This has up to 2 rows, up to 5 columns.
// 
// PARAMETERS:
//      none
//
// RETURN:
//		none.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function show_colour_key()
{
	global $typel;
	echo "<table border=0><tr>\n";
	$nct = 0;
	for ($ct = "A"; $ct <= "J"; $ct++)
	{
		if (!empty($typel[$ct]))
		{
			if (++$nct > 5)
			{
				$nct = 0;
				echo "</tr><tr>";
			}
			tdcell($ct);
			echo "$typel[$ct]</td>\n";
		}
	}
	echo "</tr></table>\n";
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: getmicrotime()
// 
// PURPOSE: Useful for benchmarking scripts
// 
// PARAMETERS:
//      none
//
// RETURN:
//		the microsec time.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function getmicrotime() 
{
	list($usec, $sec) = explode(" ",microtime()); 
	return ((float)$usec + (float)$sec); 
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: expand_certificate_abreviation($certificate)
// 
// PURPOSE: Given a certificate abreviation, expand it to the full name
// 
// PARAMETERS:
//      certificate - the abreviation for the certificate
//
// RETURN:
//		the full name of the certificate
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function expand_certificate_abreviation($certificate)
{
	$cert = preg_replace("/^GI$/", "/Ground Instructor/", $certificate);
	$cert = preg_replace("/^CFI/", "/Certified Flight Instructor/", $cert);
	$cert = preg_replace("/ME/", "/ Multi-Engine/", $cert);
	$cert = preg_replace("/I$/", "/ Instrument/", $cert);
	$cert = preg_replace("/A/", "/ Advanced/", $cert);
	return $cert;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: LookupEmailAddress($FullName)
// 
// PURPOSE: Given a full name, search for the email address
// 
// PARAMETERS:
//      FullName - the name of the user
//
// RETURN:
//		the email if it is found
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.29   Nov 18, 2004    JCovington Add logic to return to the correct page
//                                   after user login, weekly view, etc.
// 
// -----------------------------------------------------------------------------
function LookupEmailAddress($FullName)
{
	// get a record with all the user names and emails
	$res = sql_query("SELECT first_name, last_name, email FROM AircraftScheduling_person");
	
	// do we have valid database information?
	if($res) 
	{
		// valid database information
		for($i=0; ($row = sql_row($res, $i)); $i++) 
		{
			// is this the name we are looking for?
			if ($FullName == buildName($row[0], $row[1]))
			{
				// yes, is the email address valid?
				if ($row[2] != "email")
				{
					// yes, return the email address
					return $row[2];
				}
				else
				{
					// no, return null
					return "";
				}
			}
		}
	} 
	else
	{
		// invalid database information
		echo "<H2>LookupEmailAddress. Unable to retreive email for $FullName</H2>" . sql_error(); 
	}

	// email not found, return null
	return "";
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: CreateJournalEntry($TimeStamp, $Username, $Description)
// 
// PURPOSE: Log a journal entry.
// 
// PARAMETERS:
//		TimeStamp - time stamp for this entry
//		Username - user name of the person making the modfication
//		Description - description of the modification
//
// RETURN:
//		none
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
function CreateJournalEntry($TimeStamp, $Username, $Description)
{
    global $MaxJournalDays;
    
	// insert the journal entry into the database
	$sql = "INSERT INTO AircraftScheduling_journal " .
				        "(timestamp, username, description) " .
				 "VALUES ($TimeStamp, \"$Username\", \"$Description\")";		
	MySQLExecuteCommand($sql);
	
	// delete any old journal entries from the database
	$sql = "DELETE FROM AircraftScheduling_journal " .
				 "WHERE timestamp < " . strtotime("-$MaxJournalDays");		
	MySQLExecuteCommand($sql);
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: TimeZoneAdjustment()
// 
// PURPOSE: Return the bias for the timezones.
// 
// PARAMETERS:
//		none
//
// RETURN:
//		The number of seconds between the timezone the server resides in
//      and the local time zone.
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.16    Mar 21, 2004  JCovington  Adjust modification and creation times
//                                   for local timezone.
// 
// -----------------------------------------------------------------------------
function TimeZoneAdjustment()
{
	global $Timezone_Server, $Timezone_Local;
	return ($Timezone_Server - $Timezone_Local) * 60 * 60;
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: minicals(
//                     $year, 
//                     $month, 
//                     $day, 
//                     $resource, 
//                     $InstructorResource, 
//                     $makemodel)
// 
// PURPOSE: Draw the mini calendars for the day, month and week view
// 
// PARAMETERS:
//       year - year for the HTML link
//       month - month for the HTML link
//       day - day for the HTML link
//       resource - resource information for the HTML link 
//       InstructorResource - instructor information for the HTML link
//       makemodel - make and model information for the HTML link
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.16    Mar 21, 2004  JCovington  Adjust modification and creation times
//                                   for local timezone.
// 
// -----------------------------------------------------------------------------
function minicals($year, $month, $day, $resource, $InstructorResource, $makemodel)
{
	global $lang;
	
	// override the calendar class so that we can return the correct link for each
	// date on the calendar
	class MyCalendar extends Calendar
	{
	    var $resource;
	    var $InstructorResource;
	    var $makemodel;
		var $FirstMonth; 
		var $LastMonth;
	    
		// constructor to set the needed information for the links
		function __construct(
							$FirstMonth, 
							$LastMonth, 
							$resource, 
							$InstructorResource, 
							$makemodel)
		{
			$this->resource  = $resource;
			$this->InstructorResource  = $InstructorResource;
			$this->makemodel = $makemodel;
			$this->FirstMonth = $FirstMonth;
			$this->LastMonth = $LastMonth;
		}

	    // set the date link HTML
		function getDateLink($day, $month, $year)
		{
			// set the link for each of the days of the month
			$link = 
					"day.php
						?year=$year
						&month=$month
						&day=$day
						&resource=$this->resource
						&InstructorResource=$this->InstructorResource
						$this->makemodel";
			return $link;
		}

		// enable the month navigation
		function getCalendarLink($month, $year)
		{
			// if this link is on the first month , display it
			if (
				($year * 12 + $month) < 
					(date("Y", $this->FirstMonth) * 12 + date("m", $this->FirstMonth)))
			{
				// Redisplay the current page, but with some parameters
				// to set the new month and year
				$MonthLink = date("m", mktime(
									0, 
									0, 
									0, 
									date("m", $this->FirstMonth) - 1,
									date("d", $this->FirstMonth),
									date("Y", $this->FirstMonth)));
				$s = getenv('SCRIPT_NAME');
				return "$s?month=$MonthLink&year=$year";
			}
			// if this link is on the last month, display it
			else if (
				($year * 12 + $month) > 
					(date("Y", $this->LastMonth) * 12 + date("m", $this->LastMonth)))
			{
				// Redisplay the current page, but with some parameters
				// to set the new month and year
				$MonthLink = date("m", mktime(
									0, 
									0, 
									0, 
									date("m", $this->FirstMonth) + 1,
									date("d", $this->FirstMonth),
									date("Y", $this->FirstMonth)));
				$s = getenv('SCRIPT_NAME');
				return "$s?month=$MonthLink&year=$year";
			}
			else
			{
				// don't enable navigation on intermediate months
				return "";
			}
		}
	}	

	// get the dates for the calendars
	$thismonth = mktime(0, 0, 0, $month, $day, $year);
	$nextmonth = mktime(0, 0, 0, date("m", $thismonth)+1, 1, date("Y", $thismonth));
	$monthafter = mktime(0, 0, 0, date("m", $thismonth)+2, 1, date("Y", $thismonth));

	// get a copy of the calendar
	$cal = new MyCalendar(
							$thismonth, 
							$monthafter, 
							$resource, 
							$InstructorResource, 
							$makemodel);

	// First, create an array of month names, January through December
	$ShortMonths = array("Jan", "Feb", "Mar", "Apr",
							"May", "Jun", "Jul", "Aug", "Sept",
							"Oct", "Nov", "Dec");
	$cal->setMonthNames($ShortMonths);

	// create the current month calendar
	echo "<td>";
	echo $cal->getMonthView(date("m", $thismonth), date("Y", $thismonth));
	echo "</td>";
	
	// create the next month calendar
	echo "<td>";
	echo $cal->getMonthView(date("m", $nextmonth), date("Y", $nextmonth));
	echo "</td>";
	
	// create the month after calendar
	echo "<td>";
	echo $cal->getMonthView(date("m", $monthafter), date("Y", $monthafter));
	echo "</td>";
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: AircraftSchedulingMail(
//                     				$EMailAddress, 
//                     				$EMailSubject, 
//                     				$EMailBody)
// 
// PURPOSE: Send email to the given address. 
// 
// PARAMETERS:
//       EMailAddress - email address
//       EMailSubject - subject for the email
//       EMailBody - body for the email
//
// RETURN:
//		TRUE if the message was sent
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.19    Apr 3, 2004   JCovington  Use PHPMailer class for sending email.
// 
// -----------------------------------------------------------------------------
function AircraftSchedulingMail($EMailAddress, $EMailSubject, $EMailBody)
{
	global $email_host, $email_username, $email_password, $email_return_address;
	global $email_type;
	
    // if the email address is valid, send the email
    if (Len(Trim($EMailAddress)) > 0)
    {
    	$mail = new PHPMailer();
    	
        // set the email sent type
        if (strtoupper($email_type) == "SMTP")
        {
        	$mail->IsSMTP();                    // send via SMTP
        	$mail->Host     = $email_host;      // SMTP servers
        	$mail->SMTPAuth = true;             // turn on SMTP authentication
        	$mail->Username = $email_username;  // SMTP username
        	$mail->Password = $email_password;  // SMTP password	
        }
        else
        {
        	$mail->IsSendmail();                // send via SendMail
        }
    	$mail->From     = $email_return_address;
    	$mail->FromName = "AircraftScheduling Admin";
    	$mail->AddAddress($EMailAddress); 
    	$mail->Subject  =  $EMailSubject;
    	$mail->IsHTML(false);              // set email format to not HTML
    	$mail->Body     = $EMailBody;
        $mail->Encoding = "8bit";
        $mail->Helo = "localhost.localdomain";
    	$mail->AltBody  =  "";
 
    	return $mail->Send();
    }
}

//-----------------------------------------------------------------------------
// 
// FUNCTION: FormatPhoneNumber($PhoneOne, $PhoneTwo)
// 
// PURPOSE: Format the phone numbers for display. The user may have two phone 
//          numbers on file, so add the two together to make the phone number.
//          This way both numbers are displayed if they are both specified.
// 
// PARAMETERS:
//       PhoneOne - first phone number for the user
//       PhoneTwo - second phone number for the user
//
// RETURN:
//		none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// HISTORY:   Date:      Author:     Comment:
// 1.25   Aug 11, 2004    JCovington Add multiple phone numbers for users.
// 
// -----------------------------------------------------------------------------
function FormatPhoneNumber($PhoneOne, $PhoneTwo)
{
    // the user may have two phone numbers on file, so add the two
    // together to make the phone number for the entries. This way
    // both numbers are displayed if they are both specified.
    $EntryPhoneNumber = "";
    if (strlen($PhoneOne) > 0) 
    {
        $EntryPhoneNumber = $PhoneOne;
        if (strlen($PhoneTwo) > 0) 
            $EntryPhoneNumber = $EntryPhoneNumber . "/" . $PhoneTwo;
    }
    else
    {
        if (strlen($PhoneTwo) > 0) 
            $EntryPhoneNumber = $PhoneTwo;
    }
    
    return $EntryPhoneNumber;
}

//********************************************************************
// PrinterSetup(FontSize as Integer)
//
// Purpose:  Set the print properties (font, zoom, etc).
//
// Inputs:
//   FontSize - the size of the font to use
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrinterSetup($FontSize)
{
    // printer font setup
    $locFontSize = $FontSize / 5;
    echo "<font size='$locFontSize' face='Courier New'>";
}

//********************************************************************
// RoundToDecimalPlaces(
//                   $InputValue,
//                   $NumberDecimals = 2)
//                   As Double
//
// Purpose:  Round a floating point value to the requested decimal 
//           places. Two is assumed if a value is not given.
//
// Inputs:
//   InputValue - the value to round
//   NumberDecimals - optional. number of decimal places to round to.
//
// Outputs:
//   none
//
// Returns:
//   The rounded value.
//
// Notes:
//
//*********************************************************************
function RoundToDecimalPlaces($InputValue, $NumberDecimals = 2)
{
    // round by formating and taking the value of the result
    $RoundToDecimalPlaces = Val(number_format($InputValue, $NumberDecimals, ".", ""));
    
    // return the results
    return $RoundToDecimalPlaces;
}

//********************************************************************
// GetUsernameFromName(NameOfUser)
//
// Purpose:  Given a name of a user (first and last), lookup the 
//           username (login name).
//
// Inputs:
//   NameOfUser - first and last name of user to look up username for.
//
// Outputs:
//   none
//
// Returns:
//   The username of the person or -1 if an error occurs.
//
// Notes:
//
//*********************************************************************
function GetUsernameFromName($NameOfUser)
{	
    global $DatabaseNameFormat;
    
    $Username = sql_query1(
                "SELECT username " .
                "FROM AircraftScheduling_person " .
                "WHERE $DatabaseNameFormat='$NameOfUser' " .
                "LIMIT 1");
    return $Username;
}

//********************************************************************
// GetNameFromUsername(Username)
//
// Purpose:  Given a username (login name), lookup the 
//           name of a user (first and last).
//
// Inputs:
//   Username - username to look for.
//
// Outputs:
//   none
//
// Returns:
//   The first and last name of the person or -1 if an error occurs.
//
// Notes:
//
//*********************************************************************
function GetNameFromUsername($Username)
{	
    global $DatabaseNameFormat;
    
    $NameOfUser = sql_query1(
                "SELECT $DatabaseNameFormat " .
                "FROM AircraftScheduling_person " .
                "WHERE username='$Username' " .
                "LIMIT 1");
    return $NameOfUser;
}

//********************************************************************
// GetLastName($Name)
//
// Purpose:  Return the last name given a string with first and last name
//
// Inputs:
//   Name - the name to parse for the last name
//
// Outputs:
//   none
//
// Returns:
//   The last name found.
//*********************************************************************
function GetLastName($Name)
{
    global $DatabaseNameFormat;
    
    $LastName = sql_query1(
                "SELECT last_name " .
                "FROM AircraftScheduling_person " .
                "WHERE $DatabaseNameFormat='$Name'" .
                "LIMIT 1");
    return $LastName;
}

//********************************************************************
// GetFirstName($Name)
//
// Purpose:  Return the first name given a string with first and last name
//
// Inputs:
//   Name - the name to parse for the first name
//
// Outputs:
//   none
//
// Returns:
//   The first name found.
//*********************************************************************
function GetFirstName($Name)
{
    global $DatabaseNameFormat;
    
    $FirstName = sql_query1(
                "SELECT first_name " .
                "FROM AircraftScheduling_person " .
                "WHERE $DatabaseNameFormat='$Name'" .
                "LIMIT 1");
    return $FirstName;
}

//********************************************************************
// LoadDBWBFields($WBFields, $LoadJavaScript = true)
//
// Purpose:      Load the weight and balance fields from the database.
//               The fields share a memo field and have the format:
//                   Aircraft Weight, Fore CG Limit, Aft CG Limit;
//               We will load the fields into an arrays so that they may
//               be accessed for weight and balance calculations.
//
// Inputs:
//   WBFields - weight and balance fields read from the database
//   LoadJavaScript - optional. set true to save the WB information
//                    for the javascript code.
//
// Outputs:
//   none
//
// Returns:
//
// Notes:
//
//*********************************************************************
function LoadDBWBFields($WBFields, $LoadJavaScript = true)
{        
    global $WBAircraftWeight, $WBForeCG, $WBAftCG, $NumWBFieldRecords;

    // get the weight and balance field from the database record
    if (strlen($WBFields) > 0)
    {
        // get the tokens from the fields
        $NumWBFieldRecords = 0;
        while (strlen($WBFields) > 0)
        {
            $WBAircraftWeight[$NumWBFieldRecords] = GetNextToken($WBFields, ",");
            $WBForeCG[$NumWBFieldRecords] = GetNextToken($WBFields, ",");
            $WBAftCG[$NumWBFieldRecords] = GetNextToken($WBFields, ";");
            $NumWBFieldRecords++;
        }
        
        // should we load the javascript arrays
        if ($LoadJavaScript)
        {
            // build the global arrays for the javascript processing
            echo "<SCRIPT LANGUAGE=\"JavaScript\">";
            echo "var NumWBFieldRecords = $NumWBFieldRecords;";
        
            // generate the code to load the javascipt arrays
            echo "var WBAircraftWeight = new Array(";
            for ($Entry = 0; $Entry < $NumWBFieldRecords; $Entry++)
            {
                if ($Entry != $NumWBFieldRecords - 1)
                    echo $WBAircraftWeight[$Entry] . ",";
                else
                    echo $WBAircraftWeight[$Entry] . ");";
            }
            echo "var WBForeCG = new Array(";
            for ($Entry = 0; $Entry < $NumWBFieldRecords; $Entry++)
            {
                if ($Entry != $NumWBFieldRecords - 1)
                    echo $WBForeCG[$Entry] . ",";
                else
                    echo $WBForeCG[$Entry] . ");";
            }
            echo "var WBAftCG = new Array(";
            for ($Entry = 0; $Entry < $NumWBFieldRecords; $Entry++)
            {
                if ($Entry != $NumWBFieldRecords - 1)
                    echo $WBAftCG[$Entry] . ",";
                else
                    echo $WBAftCG[$Entry] . ");";
            }
            echo "</SCRIPT>";           
        } 
    }
}

//********************************************************************
// DisplayNameColumns(
//              NumberElements, 
//              NumberColumns, 
//              UserNameArray, 
//              NameArray)
//
// Purpose: Display the information in the number of columns
//          requested.
//
// Inputs:
//   NumberElements - number of elements to print 
//   NumberColumns - number of columns to print
//   UserNameArray - the array of usernames
//   NameArray - the array of names
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function DisplayNameColumns(
                        $NumberElements, 
                        $NumberColumns, 
                        $UserNameArray, 
                        $NameArray)
{ 
    // compute the number of rows
    $NumberRows = (int) ($NumberElements / $NumberColumns);
    if ($NumberRows * 2 < $NumberElements) $NumberRows++;
    
    // print the elements of the table
    for ($Row = 0; $Row < $NumberRows; $Row++)
    {
        // print the row
   		echo "<tr>";
   		
        // print the columns
        for ($Column=0; $Column < $NumberColumns; $Column++)
        {
            // compute the offset
            $ColumnOffset = $NumberRows * $Column;
            
            // if we have a valid subscript
            if ($Row + $ColumnOffset < $NumberElements)
            {
                // if we have a valid name, print the name
                If (Len($UserNameArray[$Row + $ColumnOffset]) > 0)
                {                        
        			// attended safety meeting checkbox
          		    echo "<td><center><input name='" . $UserNameArray[$Row + $ColumnOffset] . "' TYPE='checkbox'></center></td>";
        		    
        		    // username column
        			echo "<td align=left>" . $UserNameArray[$Row + $ColumnOffset] . "</td>";
        			
        			// display name column
        			echo "<td align=left>" . $NameArray[$Row + $ColumnOffset] . "</td>";
                }
            }
            
            // space between columns (except for the last)
            if ($Column < $NumberColumns - 1)
            {
                for ($Spaces=0; $Spaces < 2; $Spaces++)
                {
                    echo "<td class=CL> </td>";
                }
            }
        }
        
        // complete the row
        echo "</tr>";
    }
}

//********************************************************************
// move_picture_into_position(
//              image, 
//              renameto)
//
// Purpose: Move an uploaded file to the correct directory.
//
// Inputs:
//   image - name of the uploaded file
//   renameto - name of the file to save the uploaded image to
//
// Outputs:
//   none
//
// Returns:
//   results of the upload
//*********************************************************************
function move_picture_into_position($image, $renameto)
{
    global $ImageRootPath;
    
	$info = GetImageSize($image);
	switch($info[2]) 
	{
		case 1:
			$src = preg_replace("/.gif$/", "/.jpg/", $image);
			exec ("convert $image $src") && unlink($image);
			break;
		case 2:
			$src = $image;
			break;
		case 3:
			$src = preg_replace("/.png$/", "/.jpg/", $image);
			exec ("convert $image $src") && unlink($image);
			break;
	}
	return move_uploaded_file($src, $ImageRootPath . "/" . "$renameto");
}

?>
