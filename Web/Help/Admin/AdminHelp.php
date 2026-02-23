<?php
//-----------------------------------------------------------------------------
// 
// AdminHelp.php
// 
// PURPOSE: Display the admin help system.
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
    
    // display the header information
    ?>
    <html xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
    
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="ProgId" content="Word.Document">
    <meta name="GENERATOR" content="Microsoft FrontPage 3.0">
    <meta name="Originator" content="Microsoft Word 9">
    <!--[if !mso]>
    <style>
    v\:* {behavior:url(#default#VML);}
    o\:* {behavior:url(#default#VML);}
    w\:* {behavior:url(#default#VML);}
    .shape {behavior:url(#default#VML);}
    </style>
    <![endif]-->
    <title>Administration Screen</title>
    <!--[if gte mso 9]><xml>
     <o:DocumentProperties>
      <o:Author>Jim Covington</o:Author>
      <o:Template>Normal</o:Template>
      <o:LastAuthor>Jim Covington</o:LastAuthor>
      <o:Revision>24</o:Revision>
      <o:TotalTime>41</o:TotalTime>
      <o:Created>2000-09-23T19:04:00Z</o:Created>
      <o:LastSaved>2000-09-27T17:25:00Z</o:LastSaved>
      <o:Pages>2</o:Pages>
      <o:Words>335</o:Words>
      <o:Characters>1910</o:Characters>
      <o:Company>none</o:Company>
      <o:Lines>15</o:Lines>
      <o:Paragraphs>3</o:Paragraphs>
      <o:CharactersWithSpaces>2345</o:CharactersWithSpaces>
      <o:Version>9.3821</o:Version>
     </o:DocumentProperties>
    </xml><![endif]-->
    <!--[if gte mso 9]><xml>
     <o:shapedefaults v:ext="edit" spidmax="1029"/>
    </xml><![endif]-->
    <!--[if gte mso 9]><xml>
     <o:shapelayout v:ext="edit">
      <o:idmap v:ext="edit" data="1"/>
     </o:shapelayout></xml><![endif]-->
    <!-- $MVD$:app("RoboHELP HTML Edition by Blue Sky Software, portions by MicroVision Dev. Inc.","769") -->
    <!-- $MVD$:template("","0","0") -->
    <!-- $MVD$:color("18","f0d5a2","Head1","0") -->
    <!-- $MVD$:color("19","ed9564","Cornflower Blue","0") -->
    <!-- $MVD$:color("20","e22b8a","Blue Violet","0") -->
    <!-- $MVD$:color("21","f9ebd4","D4ebf9","0") -->
    <!-- $MVD$:color("22","d6e9d8","d8e9d6","0") -->
    <!-- $MVD$:color("23","79f8fb","tw-yellow","0") -->
    <!-- $MVD$:color("24","d4d4d4","d4d4d4","0") -->
    <!-- $MVD$:color("25","ffc082","82C0FF","0") -->
    <!-- $MVD$:fontset("Verdana","Verdana") -->
    </head>
    
    <body bgcolor="white" lang="EN-US" link="blue" vlink="blue" style="tab-interval:.5in">
    <div class="Section1">
    
    <h1>Administration Screen</h1>
    
    <p style="mso-margin-top-alt:auto"><!--[if gte vml 1]><v:shapetype id="_x0000_t75"
     coordsize="21600,21600" o:spt="75" o:preferrelative="t" path="m@4@5l@4@11@9@11@9@5xe"
     filled="f" stroked="f">
     <v:stroke joinstyle="miter"/>
     <v:formulas>
      <v:f eqn="if lineDrawn pixelLineWidth 0"/>
      <v:f eqn="sum @0 1 0"/>
      <v:f eqn="sum 0 0 @1"/>
      <v:f eqn="prod @2 1 2"/>
      <v:f eqn="prod @3 21600 pixelWidth"/>
      <v:f eqn="prod @3 21600 pixelHeight"/>
      <v:f eqn="sum @0 0 1"/>
      <v:f eqn="prod @6 1 2"/>
      <v:f eqn="prod @7 21600 pixelWidth"/>
      <v:f eqn="sum @8 21600 0"/>
      <v:f eqn="prod @7 21600 pixelHeight"/>
      <v:f eqn="sum @10 21600 0"/>
     </v:formulas>
     <v:path o:extrusionok="f" gradientshapeok="t" o:connecttype="rect"/>
     <o:lock v:ext="edit" aspectratio="t"/>
    </v:shapetype><v:shape id="_x0000_i1025" type="#_x0000_t75" style='width:216.56pt;
     height:371.25pt'>
     <v:imagedata src="Help/Admin/AdminHelp_Files/Admin.jpg" o:title=""/>
    </v:shape><![endif]--><![if !vml]><img width="288" height="495"
    src="Help/Admin/AdminHelp_Files/Admin.jpg" v:shapes="_x0000_i1025"><![endif]> </p>
    
    <hr>
    
    <p>The Administration screen provides for the control and configuration of the
        program. The following links are provided to configure various parts of
        the program:
    </p>
    
    <p class="mvd-p-note">Note: If aircraft checkout is disabled, the configuration of the 
        screen will change. In addition, the Administration screen will change depending on the
        privileges that the administrator has set for your login. If your login is not
        allowed access to configure a part of the program, the link will not appear.</p>
    
    <?php

    // indent everything
    echo "<ul>";

    // display the aircraft section
    echo "<li><p>Aircraft</p>";
    echo "<ul>";
        echo "<li>";
    	echo "<b><a " . 
            "href='$HomeDirectory/DisplayHelpHTML.php" . 
            "?HelpHTMLFile=Help/ModifyAircraftMakeModel/ModifyAircraftMakeModel.htm" . 
            "&goback=" . GetScriptName() .  
            "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    	    "'>Maintain Aircraft Make and Model Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayAircraftScreen/DisplayAircraftScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Aircraft Management Screen</a></b>";
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
                "?HelpHTMLFile=Help/PrintAircraftFaultRecords/PrintAircraftFaultRecords.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Aircraft Fault Records</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/ExportDatabaseInformationScreen/ExportDatabaseInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Export Aircraft Information</a></b>";
	echo "</ul>";
	echo "<br>";

    // display the Configuration section
    echo "<li><p>Configuration</p>";
    echo "<ul>";
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
                "?HelpHTMLFile=Help/ProgramConfigurationScreen/ProgramConfigurationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Program Configuration</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/ViewJournalEntries/ViewJournalEntries.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>View Journal Entries</a></b>";
	echo "</ul>";
	echo "<br>";

    // display the Flights section
    echo "<li><p>Flights</p>";
    echo "<ul>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayFlightInformationScreen/DisplayFlightInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Modify Flight Information</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/ExportDatabaseInformationScreen/ExportDatabaseInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Export Flight Information</a></b>";
	echo "</ul>";
	echo "<br>";

    // display the Inventory section
    echo "<li><p>Inventory</p>";
    echo "<ul>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayCategoryScreen/ModifyCategoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Modify Category Information</a></b>";
		echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/DisplayInventoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Modify Maintenance Inventory Information</a></b>";
		echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/DisplayInventoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Modify Maintenance Retail Information</a></b>";
		echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayInventoryScreen/DisplayInventoryScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Modify Wholesale Inventory Information</a></b>";
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
                "?HelpHTMLFile=Help/ExportDatabaseInformationScreen/ExportDatabaseInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Export Inventory Information</a></b>";
	echo "</ul>";
	echo "<br>";

    // display the Billing section
    echo "<li><p>Billing</p>";
    echo "<ul>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayMemberChargesScreen/DisplaymemberChargesScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Display User Charges Screen</a></b>";
		echo "<li>";
			echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/DisplayMemberChargesScreen/ModifyCharge.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
			    "'>Modify Charge Information</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintSanityCheck/PrintSanityCheck.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Billing Sanity Check Information</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintDailyBillsReports/PrintDailyBillsReports.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Daily Billing Information</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintDailyDARReport/PrintDailyDARReport.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Daily DAR Information</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/PrintMonthlyBillsReports/PrintMonthlyBillsReports.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Print Monthly Billing Information</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/ExportDatabaseInformationScreen/ExportDatabaseInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Export Charge Information</a></b>";
	echo "</ul>";
	echo "<br>";

    // display the Users section
    echo "<li><p>Users</p>";
    echo "<ul>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/InstructorManagement/InstructorManagement.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Instructor Management</a></b>";
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
                "?HelpHTMLFile=Help/SafetyMeetingDatesScreen/SafetyMeetingDatesScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Set Safety Meeting Dates</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/RenameUsernameScreen/RenameUsername.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Rename Username Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/UserManagementScreen/UserManagementScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>User Management Screen</a></b>";
    	echo "<li>";
    		echo "<b><a " . 
                "href='$HomeDirectory/DisplayHelpHTML.php" . 
                "?HelpHTMLFile=Help/ExportDatabaseInformationScreen/ExportDatabaseInformationScreen.htm" . 
                "&goback=" . GetScriptName() .  
                "&GoBackParameters=" . BuildGoBackParameters("?" . $HTTP_SERVER_VARS["QUERY_STRING"]) .
    		    "'>Export User Information</a></b>";
	echo "</ul>";
	echo "<br>";
    
    // end screen list
    echo "</li>";
    
    // end the indent of everything
    echo "</ul>";

    // end the document
    echo "</div>";
    echo "</body>";
    echo "</html>";
?>
