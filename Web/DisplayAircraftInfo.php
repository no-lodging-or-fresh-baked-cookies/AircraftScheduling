<?php
//-----------------------------------------------------------------------------
// 
// DisplayAircraftInfo.php
// 
// PURPOSE: Displays the aircraft information screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      TailNumber - selected aircraft tailnumber
//      all - select all resources
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//      This file based on Meeting Room Booking System http://mrbs.sourceforge.net
//      It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
//      12/19/2001 and released under the terms of the GNU Public License
//
//      AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
//      a lot more than AircraftScheduling so I renamed it to keep from causing confusion.
// 
// -----------------------------------------------------------------------------
    
    include "global_def.inc";
    include "config.inc";
    include "AircraftScheduling_auth.inc";
    include "$dbsys.inc";
    include "functions.inc";
    require_once("DatabaseFunctions.inc");
    require_once("SquawkFunctions.inc");
    
    // initialize variables
    $all = '';
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["name"])) $name = $rdata["name"];
    if(isset($rdata["all"])) $all = $rdata["all"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    
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
    
    if (empty($resource))
    	$resource = get_default_resource();
    
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
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal) && $User_Must_Login)
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    
    $sql = "SELECT 
                    n_number, 
                    serial_number, 
                    panel_picture, 
                    picture, 
    				ROUND(a.hourly_cost, 2), 
    				ROUND(empty_weight, 1), 
    				ROUND(max_gross ,1), 
    				year, 
    				code, 
    				make, 
    				model, 
    				a.description, 
    				ifr_cert, 
    				status,
    				ROUND(Current_Hobbs, 1),
    				ROUND(tach1, 1),
                    ROUND(Hrs_Till_100_Hr, 1),                
                    Annual_Due,
                    Aircraft_Color, 
                    `Current_User`,
                    CurrentKeyCode,
					ICAO_Equipment_Codes,
					ICAO_Transponder,
					ICAO_ADSB_Type
					
    		FROM AircraftScheduling_aircraft a, 
    			AircraftScheduling_make b, 
    			AircraftScheduling_model c, 
    			AircraftScheduling_equipment_codes d 
    		WHERE n_number='$name' AND 
    			a.make_id=b.make_id AND 
    			a.model_id=c.model_id AND 
    			a.code_id=d.code_id";
    
    $res = sql_query($sql);
    
    if($res) 
    {
        // we have good database information, display the aircraft status
        $row = sql_row($res, 0);
        echo "<center>"; 
        
        // header
        echo "<H2><B>Aircraft - " . $row[0] . "</B></H2>";
        
        // display the aircraft image if it is specified       
        echo "<table border=0>";
        echo "<tr>";
        echo "	<td>";
		if($row[3]) 
			echo "<a href=\"image.php?src=$row[3]\"><img src=\"image.php?src=$row[3]&width=300\"></a>"; 
        echo "	</td>";
        	  
        // display the panel image if it is specified       
        echo "	<td>";
		if($row[2]) 
			echo "<a href=\"image.php?src=$row[2]\"><img src=\"image.php?src=$row[2]&width=300\"></a>"; 
        echo "	</td>";
        echo "</tr>";
        echo "</table>";
        
        // determine aircraft status
        $AircraftStatus = LookupAircraftStatusString($row[13]);
        
        // if we have a current user that has checked out the aircraft, 
        // show the name and user name in the status
        if ($row[19])
        {
            // aircraft is in use, show the user information
            $AircraftStatus = $AircraftStatus . " by $row[19] ($row[20])";
        }
        
        // display the aircraft status information
        echo "<table border=0>";
        if($row[4]) echo "<tr><td>Rental Rate:</td><td><B>$CurrencyPrefix$row[4]/hour</B></td></tr>";
        if($row[9]) echo "<tr><td>Make:</td><td><B>$row[9]</B></td></tr>";
        if($row[10]) echo "<tr><td>Model:</td><td><B>$row[10]</B></td></tr>";
        if($row[7]) echo "<tr><td>Year:</td><td><B>$row[7]</B></td></tr>";
        if($row[5]) echo "<tr><td>Empty Weight:</td><td><B>$row[5]</B></td></tr>";
        if($row[6]) echo "<tr><td>Max Gross Weight:</td><td><B>$row[6]</B></td></tr>";
        if($row[8])
		{
			echo "<tr><td>Equipment Code:</td><td>" . 
				"<B>" .
				(Len(Trim($row[21])) == 0 ? "S" : $row[21]) .
				"/" . 
				(Len(Trim($row[22])) == 0 ? "C" : $row[22]) .
				(Len(Trim($row[23])) == 0 ? "" : $row[23]) .
				"</B></td></tr>";
		}
        if($row[18]) echo "<tr><td>Aircraft Color:</td><td><B>$row[18]</B></td></tr>";
        if($row[1]) echo "<tr><td>Serial Number:</td><td><B>$row[1]</B></td></tr>";
        if($row[14]) echo "<tr><td>Current Hobbs:</td><td><B>$row[14]</B></td></tr>";
        if($row[15]) echo "<tr><td>Current Tach:</td><td><B>$row[15]</B></td></tr>";
        if($row[16]) echo "<tr><td>Hours until 100 hr:</td><td><B>$row[16]</B></td></tr>";
        if($row[17]) echo "<tr><td>Annual Due:</td><td><B>" . date("d-M-Y", strtotime($row[17])) . "</B></td></tr>";
        echo "<tr><td>Status:</td><td><B>$AircraftStatus</B></td></tr>";
        echo "</table>";
        
        // aircraft description
        $TextRows = 8;
        $TextColumns = 70;
        echo "<table border=0>";
        echo "<tr>";
        echo "<BR>";
        echo "<th>Description</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<TD CLASS=TL>";
        echo "<TEXTAREA NAME='AircraftDescription' ROWS=$TextRows COLS=$TextColumns WRAP='virtual' READONLY>" . 
                htmlentities ( $row[11] ) . "
                </TEXTAREA></TD></TR>";
        echo "</tr>";
        echo "</table>";
        
        // if we are controlling squawks
        if ($AllowSquawkControl)
        {
            // if the user is not required to login, we may be here without a valid user
            // so if the user is logged in, let them add squawks to the database, otherwise
            // just let them look at them. 
            if(getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
            {
                // user is logged in, let them add aircraft squawks
                DisplayAircraftSquawks(
                                        $row[0], 
                                        getUserName(), 
                                        $row[15], 
                                        $TextRows, 
                                        $TextColumns, 
                                        1, 
                                        0);
            }
            else
            {
                // user is not logged in, only let them look at aircraft squawks
                DisplayAircraftSquawks(
                                        $row[0], 
                                        getUserName(), 
                                        $row[15], 
                                        $TextRows, 
                                        $TextColumns, 
                                        0, 
                                        0);
            }
        }
        
        // disclaimer
        if(!empty($row[5]) or !empty($row[6]))
            echo "<br><b>This data for informational purposes only and not for flight planning.  See the POH or AFM for official data.</b><br>";
        
        echo "<BR>";
        
        // generate return URL
        GenerateReturnURL(
                            $goback, 
                            "?day=$day&month=$month&year=$year$makemodel" . 
                            "&resource=$resource" .
                            "&resource_id=$resource_id"
                            );
        echo "</center>";
    }
    else
    {
        // error getting database information
        DisplayDatabaseError("DisplayAircraftInfo.php", $sql);
    }

    include "trailer.inc";
?>