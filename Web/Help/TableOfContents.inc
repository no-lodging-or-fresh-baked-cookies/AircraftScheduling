<?php
//-----------------------------------------------------------------------------
// 
// TableOfContents.inc
// 
// PURPOSE: Display the help systems table of contents.
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
    
    // initialize variables
    $HomeDirectory = ".";
                
    // space after previous title
    echo "<br>";	
    echo "<br>";
    
    // indent everything
    echo "<ul>";

    // display the general information
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/Introduction/Introduction.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Introduction</a></b>";
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/License.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Licensing Agreement</a></b>";
	
	// show the currency selection if the aircraft checkout 
	// functions are enabled
 	if ($AllowAircraftCheckout)
	{
        echo "<li>";
        echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/CurrencyRules.htm" . 
                        "&goback=" . GetScriptName() .  
    		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                    "'>Currency Rule Explanation</a></b>";
    }
    
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/site_faq.inc" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>FAQ</a></b>";

    // display the login screen information
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/LoginScreen/LoginScreen.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Login Screen</a></b>";

    // display the main screen information
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/MainScreen/MainScreen.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Main Screen</a></b>";
 	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/MainScreen/MonthlyScheduleScreen/MonthlyScheduleScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Monthly Schedule Screen</a></b>";
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/MainScreen/NavigationBar/NavigationBar.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Navigation Bar</a></b>";
 	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/MainScreen/WeeklyScheduleScreen/WeeklyScheduleScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Weekly Schedule Screen</a></b>";
    
    // screen help selections
    echo "<br>";
    echo "<br>";
    echo "<li><b>Screens</b>";
    echo "<ul>";
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/MainScreen/AddEntryScreen/AddEntryScreen.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Add Schedule Entry screen</a></b>";
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/Admin/AdminHelp.php" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Administration</a></b>";
 	if ($AllowAircraftCheckout)
	{
        echo "<li>";
        echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/AircraftCheckinScreen/AircraftCheckinScreen.htm" . 
                        "&goback=" . GetScriptName() .  
    		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                    "'>Aircraft Check-in Screen</a></b>";
		echo "<UL>";
		echo "	<LI>";
        echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/AircraftCheckinScreen/AircraftDataFrame.htm" . 
                        "&goback=" . GetScriptName() .  
    		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                    "'>Aircraft Status</a></b>";
		echo "	<LI>";
        echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/AircraftCheckinScreen/CheckinInformationFrame.htm" . 
                        "&goback=" . GetScriptName() .  
    		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                    "'>Check-in Information</a></b>";
		echo "	<LI>";
        echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/AircraftCheckinScreen/SquawkDataDisplay.htm" . 
                        "&goback=" . GetScriptName() .  
    		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                    "'>Squawk Information</a></b>";
		echo "	<UL>";
		echo "		<LI>";
        echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/NewSquawkScreen.htm" . 
                        "&goback=" . GetScriptName() .  
    		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                    "'>New Squawk Screen</a></b>";
		echo "	</UL>";
		echo "</UL>";

		echo "<li>";
			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/AircraftCheckoutScreen/AircraftCheckoutScreen.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Aircraft Check-out Screen</a></b>";
		echo "<ul>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/AircraftCheckoutScreen/AircraftDataFrame.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>Aircraft Status</a></b>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/AircraftCheckoutScreen/FlightCurrencyFrame.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>Flight Currency</a></b>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/AircraftCheckoutScreen/FlightStatus.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>Flight Status</a></b>";
         	if ($AllowAircraftCheckout)
        	{
    			echo "<li>";
    				echo "<b><a " . 
                        "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/AircraftCheckoutScreen/WeightBalanceFrame.htm" . 
                        "&goback=" . GetScriptName() .  
                        "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    				    "'>Weight and Balance</a></b>";
		    }
		echo "</ul>";
    }
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/DisplayAircraftScreen/DisplayAircraftScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Aircraft Management Screen</a></b>";
	echo "<ul>";
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AddAircraftScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Add New Aircraft</a></b>";
	echo "<ul>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AircraftInformation.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Aircraft Information Fields</a></b>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AircraftSquawkFields.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Aircraft Squawk Fields</a></b>";
		echo "<ul>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/NewSquawkScreen.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>New Squawk</a></b>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/EditSquawks.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>Edit Squawks</a></b>";
		echo "</ul>";
     	if ($AllowAircraftCheckout)
    	{
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AircraftWeightBalance.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Aircraft Weight and Balance Fields</a></b>";
        }
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/OwnerInformation.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Owner Information Fields</a></b>";
	echo "</ul>";
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/DisplayAircraftScreen/ModifyAircraftScreen/ModifyAircraftScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Modify Aircraft</a></b>";
	echo "<ul>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AircraftInformation.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Aircraft Information Fields</a></b>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AircraftSquawkFields.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Aircraft Squawk Fields</a></b>";
		echo "<ul>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/NewSquawkScreen.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>New Squawk</a></b>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/EditSquawks.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>Edit Squawks</a></b>";
		echo "</ul>";			
     	if ($AllowAircraftCheckout)
    	{
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AircraftWeightBalance.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Aircraft Weight and Balance Fields</a></b>";
		}
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/OwnerInformation.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Owner Information Fields</a></b>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayAircraftScreen/ModifyAircraftScreen/ModifyAircraftScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Delete Aircraft</a></b>";
	echo "</ul>";
	echo "</ul>";

	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/ChangeLoginNotice/ChangeLoginNotice.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Change Login Notice Message</a></b>";
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/ChangePassword/ChangePassword.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Change Password Screen</a></b>";
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/ChangeUserInformation/ChangeUserInformation.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Change User Information Screen</a></b>";

 	if ($AllowAircraftCheckout)
	{
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayCategoryScreen/DisplayCategoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Category Management Screen</a></b>";
    	echo "<ul>";
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayCategoryScreen/AddNewCategoryScreen.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Add New Category</a></b>";
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayCategoryScreen/ModifyCategoryScreen.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Modify Category</a></b>";
    	echo "</ul>";
    	echo "<li>";
    		echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/UserCurrencyScreen/UserCurrencyScreen.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Check Currency Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/CurrencyRules.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Currency Rules</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayChargeInformationScreen/DisplayChargeInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Display Charge Information Screen</a></b>";
    	echo "<ul>";
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayMemberChargesScreen/AddNewCharge.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Add New Charge</a></b>";
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayMemberChargesScreen/ModifyCharge.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Modify Charge</a></b>";
    	echo "</ul>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayMemberChargesScreen/DisplaymemberChargesScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Display User Charges Screen</a></b>";
    	echo "<ul>";
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayMemberChargesScreen/ChargeSummary.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Charge Summary</a></b>";
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayMemberChargesScreen/FlightCharges.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Flight Charges</a></b>";
    		echo "<ul>";
    			echo "<li>";
    				echo "<b><a " . 
                        "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/DisplayFlightInformationScreen/AddNewFlightScreen.htm" . 
                        "&goback=" . GetScriptName() .  
                        "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    				    "'>Add New Flight</a></b>";
    			echo "<li>";
    				echo "<b><a " . 
                        "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/DisplayFlightInformationScreen/ModifyFlightScreen.htm" . 
                        "&goback=" . GetScriptName() .  
                        "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    				    "'>Modify Flight</a></b>";
    		echo "</ul>";
    		echo "<li>";
    			echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/DisplayMemberChargesScreen/ItemsPurchased.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    			    "'>Items Purchased</a></b>";
    		echo "<ul>";
    			echo "<li>";
    				echo "<b><a " . 
                        "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/DisplayMemberChargesScreen/AddNewCharge.htm" . 
                        "&goback=" . GetScriptName() .  
                        "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    				    "'>Add New Charge</a></b>";
    			echo "<li>";
    				echo "<b><a " . 
                        "href='$HomeDirectory/DisplayHelpHTML.php" . 
                        "?HelpHTMLFile=Help/DisplayMemberChargesScreen/ModifyCharge.htm" . 
                        "&goback=" . GetScriptName() .  
                        "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    				    "'>Modify Charge</a></b>";
    		echo "</ul>";
    	echo "</ul>";
    }
 	if ($AllowAircraftCheckout)
	{
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/DisplayInventoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Display Inventory Screen</a></b>";
        echo "<ul>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/AddNewInventoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Add New Inventory Item</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/ModifyInventoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Modify Inventory Item</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/SellInventoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Sell Inventory Items</a></b>";
    echo "</ul>";
    }
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/DisplayFlightInformationScreen/DisplayFlightInformationScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Flight Management Screen</a></b>";
	echo "<ul>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayFlightInformationScreen/AddNewFlightScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Add New Flight</a></b>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayFlightInformationScreen/ModifyFlightScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Modify Flight</a></b>";
	echo "</ul>";
 	if ($AllowAircraftCheckout)
	{	
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/ExportDatabaseInformationScreen/ExportDatabaseInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Export Information</a></b>";
	}
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/InstructorManagement/InstructorManagement.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Instructor Management</a></b>";
	echo "<ul>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/InstructorManagement/ModifyInstructor/ModifyInstructor.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Modify Instructor Screen</a></b>";
	echo "</ul>";
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/LoginScreen/LoginScreen.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Login Screen</a></b>";
    echo "<li>";
    echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/MainScreen/MainScreen.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Main Screen</a></b>";
 	if ($AllowAircraftCheckout)
	{
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/ModifyAircraftMakeModel/ModifyAircraftMakeModel.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Maintain Aircraft Make and Model Screen</a></b>";
    }
    echo "<li>";
        echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/MainScreen/AddEntryScreen/AddEntryScreen.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Modify Schedule Entry screen</a></b>";
 	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/MainScreen/MonthlyScheduleScreen/MonthlyScheduleScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Monthly Schedule Screen</a></b>";
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/MainScreen/NavigationBar/NavigationBar.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Navigation Bar</a></b>";
 	if ($AllowAircraftCheckout)
	{
    	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/ProgramConfigurationScreen/ProgramConfigurationScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Program Configuration</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintAircraftFaultRecords/PrintAircraftFaultRecords.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Aircraft Fault Records</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintAircraftInformationScreen/PrintAircraftInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Aircraft Information Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintDailyBillsReports/PrintDailyBillsReports.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Daily Bills And Reports Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintDailyDARReport/PrintDailyDARReport.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Daily DAR Report Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintInventoryInformation/PrintInventoryInformation.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Inventory Information Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintMemberInformation/PrintMemberInformation.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print User Information Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintMemberStatistics/PrintMemberStatistics.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print User Statistics Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintSanityCheck/PrintSanityCheck.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Monthly Billing Sanity Check Information Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintMonthlyBillsReports/PrintMonthlyBillsReports.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Monthly Bills and Reports Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/ReportScreen/ReportScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Report Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/SafetyMeetingDatesScreen/SafetyMeetingDatesScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Safety Meeting Dates</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/SellInventoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Sell Inventory Items</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/SellInventoryMultipleScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Sell Inventory Item to Multiple Users</a></b>";
	}
	echo "<li>";
        echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/MainScreen/ShowEntryScreen/ShowEntryScreen.htm" . 
                    "&goback=" . GetScriptName() .  
		            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
                "'>Show Schedule Entry screen</a></b>";
	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/UserManagementScreen/UserManagementScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>User Management Screen</a></b>";
	echo "<ul>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/UserManagementScreen/AddMemberScreen/AddMemberScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Add New User</a></b>";
     	if ($AllowAircraftCheckout)
    	{
		echo "<ul>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/UserManagementScreen/AddMemberScreen/CurrencyInformation.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>Currency Fields</a></b>";
		echo "</ul>";
	    }
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/UserManagementScreen/ModifyMemberScreen/ModifyMemberScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Modify Existing User</a></b>";
     	if ($AllowAircraftCheckout)
    	{
    		echo "<ul>";
			echo "<li>";
				echo "<b><a " . 
                    "href='$HomeDirectory/DisplayHelpHTML.php" . 
                    "?HelpHTMLFile=Help/UserManagementScreen/AddMemberScreen/CurrencyInformation.htm" . 
                    "&goback=" . GetScriptName() .  
                    "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
				    "'>Currency Fields</a></b>";
    		echo "</ul>";
	    }
	echo "</ul>";
 	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/ViewJournalEntries/ViewJournalEntries.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>View Journal Entries</a></b>";
 	echo "<li>";
		echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/MainScreen/WeeklyScheduleScreen/WeeklyScheduleScreen.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
		    "'>Weekly Schedule Screen</a></b>";
   
    // end screen list
    echo "</li>";
    echo "</ul>";
                
    // space after the selections
    echo "</li>";
    
    // end the indent of everything
    echo "</ul>";
?>

