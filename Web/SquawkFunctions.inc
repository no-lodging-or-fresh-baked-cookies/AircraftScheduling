<?php
//-----------------------------------------------------------------------------
// 
// SquawkFunctions
// 
// PURPOSE: Contains general functions to display and modify squawks within
//          the database.
// 
// PARAMETERS:
//      none
// 
// REQUREMENTS IMPLEMENTED:
//      none
//
// COMMENTS:
// 
// -----------------------------------------------------------------------------

require_once("StringFunctions.inc");
require_once("DateFunctions.inc");
require_once("mysql.inc");

//********************************************************************
// BuildAircraftSquawkList(
//                        $TailNumber, 
//                        $AllowModifications)
//
// Purpose: Build a list of active aircraft squawks for the requested
//          aircraft.
//
// Inputs:
//   TailNumber - tailnumber of the aircraft to display squawks
//   AllowModifications - set to 1 to allow modifications of the squawks
//
// Outputs:
//   none
//
// Returns:
//   A list of squawks seperated by newlines.
//*********************************************************************
function BuildAircraftSquawkList(
                                $TailNumber, 
                                $AllowModifications)
{
    global $NotRepairedString, $RepairedString, $DeferredString, $ClosedString;

    // print the open aircraft squawks
    // get the information from the database about the selected aircraft
    $sql = "SELECT " . 
                "     Date, " .                     // 0
                "     Grounding, " .                // 1
                "     Description, " .              // 2
                "     KeyCode, " .                  // 3
                "     Repair_Date, " .              // 4
                "     Repair_Description, " .       // 5
                "     Initial_Tach, " .             // 6
                "     Repair_Tach, " .              // 7
                "     Mechanic " .                  // 8
                "FROM Squawks WHERE " .
                "     (Aircraft = '" . Trim($TailNumber) . "')";    
             
    // if modifications are allowed
    if ($AllowModifications)
    {
        // modifications are allowed, select all squawks
        $sql = $sql . "ORDER BY Date";
    }
    else
    {
        // modifications are not allowed, select unrepaired squawks
        $sql = $sql . " AND (Repair_Date = ' ') " . 
                "ORDER BY Date";
    }
    $res = sql_query($sql);
    
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {        
        $SquawkText = "";
        $RowNumber = 1;
        
        // put the title in the Squawk display
        $SquawkText = $SquawkText . "<thead>";
         
        // if modifications are allow
        if ($AllowModifications)
        {
            // modifications are allowed
        
            // columns sizes
            $Column1Width = "15%";
            $Column2Width = "15%";
            $Column3Width = "15%";
            $Column4Width = "55%";
            
            // build the headers
            $SquawkText = $SquawkText . 
                "<tr><td align=left width=$Column1Width><b>Date</b></td>" . 
                "<td align=left width=$Column2Width><b>Repair Date</b></td>" . 
                "<td align=left width=$Column3Width><b>Grounding</b></td>" . 
                "<td align=left width=$Column4Width><b>Description</b></td></tr>";

            // group the columns
            $SquawkText = $SquawkText . "<colgroup span=1>";
            $SquawkText = $SquawkText . "<colgroup span=1>";
            $SquawkText = $SquawkText . "<colgroup span=1>";
            $SquawkText = $SquawkText . "<colgroup span=1>";
        }
        else
        {
            // modifications are not allowed
        
            // columns sizes
            $Column1Width = "15%";
            $Column2Width = "15%";
            $Column3Width = "0%";
            $Column4Width = "70%";
            
            // build the headers
            $SquawkText = $SquawkText . 
                "<tr><td align=left width=$Column1Width><b>Date</b></td>" . 
                "<td align=left width=$Column2Width><b>Grounding</b></td>" . 
                "<td align=left width=$Column4Width><b>Description</b></td></tr>";

            // group the columns
            $SquawkText = $SquawkText . "<colgroup span=1>";
            $SquawkText = $SquawkText . "<colgroup span=1>";
            $SquawkText = $SquawkText . "<colgroup span=1>";
        }
        $SquawkText = $SquawkText . "</thead>";

        //see if any squawks were found for this aircraft
        if (sql_count($res) > 0)
        {                        
            // build the squawk text
            $SquawkText = $SquawkText . "<tbody>";
            for($i=0; $SquawkRow = sql_row($res, $i); $i++) 
            {                
                // set the grounding status
                if ($SquawkRow[1] == 0) $GroundingStatus = "No";
                else  $GroundingStatus = "Yes";
             
                // if modifications are allow
                if ($AllowModifications)
                {
                    // modifications are allowed, build the URL
                    $ModURL = "?ModifySquawk=1" .
                              "&RowNumber=$RowNumber" .
                              "&TailNumber=$TailNumber" .
                              "&SquawkEntryDate=$SquawkRow[0]" .
                              "&SquawkTachTime=$SquawkRow[6]" .
                              "&SquawkUserName=$SquawkRow[3]" .
                              "&SquawkDescription=$SquawkRow[2]" .
                              "&SquawkIsGrounded=$SquawkRow[1]" .
                              "&SquawkRepairDate=$SquawkRow[4]" .
                              "&SquawkRepairTach=$SquawkRow[7]" .
                              "&SquawkRepairMechanic=$SquawkRow[8]" .
                              "&SquawkRepairDescription=$SquawkRow[5]" . 
                              "&AllowModifications=" . $AllowModifications;
                
                    // count the rows that we have added so the javascript will not the 
                    // table entry to update
                    $RowNumber++;
                    
                    // date of entry
                    $SquawkField = 
                            "<tr><td align=left width=$Column1Width>" . 
                            "<a href=\"javascript:AddModifySquawk(1, '" . addslashes($ModURL) . "')\">" . 
                                    FormatField($SquawkRow[0], "Date") . "</a></td>";
                    
                    // repair date
                    if ($SquawkRow[4] == $DeferredString)
                    {
                        // repair is deferred
                        $SquawkField = $SquawkField .
                                "<td align=left width=$Column2Width>" . 
                                "<a href=\"javascript:AddModifySquawk(1, '" . addslashes($ModURL) . "')\">" . 
                                        $DeferredString . "</a></td>";
                    }
                    else if ($SquawkRow[4] == $ClosedString)
                    {
                        // repair is deferred
                        $SquawkField = $SquawkField .
                                "<td align=left width=$Column2Width>" . 
                                "<a href=\"javascript:AddModifySquawk(1, '" . addslashes($ModURL) . "')\">" . 
                                        $ClosedString . "</a></td>";
                    }
                    else
                    {
                        // repair is not deferred or closed
                        $SquawkField = $SquawkField .
                                "<td align=left width=$Column2Width>" . 
                                "<a href=\"javascript:AddModifySquawk(1, '" . addslashes($ModURL) . "')\">" . 
                                        FormatField($SquawkRow[4], "Date") . "</a></td>";
                    }
                    
                    // grounding status
                    $SquawkField = $SquawkField .  
                        "<td align=center width=$Column3Width>" . 
                            "<a href=\"javascript:AddModifySquawk(1, '" . addslashes($ModURL) . "')\">" .
                            "$GroundingStatus</a></td>";
                    
                    // description
                    $SquawkField = $SquawkField .  
                        "<td align=left width=$Column4Width>" .
                            "<a href=\"javascript:AddModifySquawk(1, '" . addslashes($ModURL) . "')\">" .
                            "$SquawkRow[2]</a></td></tr>";
                }
                else
                {
                    // modifications are not allowed
                    // date of entry
                    $SquawkField = 
                            "<tr><td align=left width=$Column1Width>" . 
                            FormatField($SquawkRow[0], "Date") . "</td>";
                    
                    // grounding status
                    $SquawkField = $SquawkField .  
                        "<td align=center width=$Column2Width>$GroundingStatus</td>";
                    
                    // description
                    $SquawkField = $SquawkField .  
                        "<td align=left width=$Column4Width>$SquawkRow[2]</td></tr>";
                }

            
                // add the record to the return field
                $SquawkText = $SquawkText . $SquawkField;
            }                
            $SquawkText = $SquawkText . "</tbody>";
        }
    }
    else
    {
        // error processing database request, tell the user
        DisplayDatabaseError("BuildAircraftSquawkList", $sql);
        $SquawkText = "";
    }
    
    // return the results
    return $SquawkText;
}

//********************************************************************
// DisplayAircraftSquawks(
//                        $TailNumber, 
//                        $CurrentUserName, 
//                        $EndingTach,
//                        $TextRows, 
//                        $TextColumns, 
//                        $AllowNew,
//                        $AllowModifications)
//
// Purpose: Display a table to the user with the active aircraft squawks.
//
// Inputs:
//   TailNumber - tailnumber of the aircraft to display squawks
//   CurrentUserName - name of the user for entering squawks 
//   EndingTach - tach for new squawks
//   TextRows - number of rows for the TEXTAREA control
//   TextColumns - number of columns for the TEXTAREA control
//   AllowNew - set to 1 to allow new squawks to be added
//   AllowModifications - set to 1 to allow modifications of the squawks
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function DisplayAircraftSquawks(
                                $TailNumber, 
                                $CurrentUserName, 
                                $EndingTach,
                                $TextRows, 
                                $TextColumns, 
                                $AllowNew,
                                $AllowModifications)
{
    // get the squawk tracking status for this aircraft
    $sql = "SELECT TrackSquawks FROM AircraftScheduling_aircraft WHERE n_number='$TailNumber'";
    $TrackSquawks = sql_query1($sql);

    // if squawk tracking is enabled for this aircraft tail number or this is a new
    // aircraft
    if ($TrackSquawks == 1 || !IsAircraftID($TailNumber))
    {
        // setup the table for the squawk list                           
        echo "<table height=8% width=75%>";
        echo "<tr>";
        echo "<th align=center>Squawks</td>";
        echo "</tr>";
        
        // put the squawks in an iframe so that the table will scroll
        echo "<tr><td>";
        echo "<iframe width='100%' height='100' name='SquawkFrame' " . 
               "src=SquawkFrame.php?TailNumber=$TailNumber&AllowModifications=$AllowModifications>";
        echo "</iframe>"; 
        echo "</td></tr>";
        
        // put the add new button if requested
        if ($AllowNew)
        {
            $ModURL = "?ModifySquawk=0" . 
                      "&SquawkUserName=" . $CurrentUserName .
                      "&SquawkTachTime=" . $EndingTach . 
                      "&TailNumber=" . $TailNumber .
                      "&AllowModifications=" . $AllowModifications;
    
            echo "<tr>";
            echo "<td align=left>";
            echo "<input name='NewSquawks' type=button value='New Squawk' " . 
                 "ONCLICK=\"AddModifySquawk(0, '$ModURL')\">";
            echo "</td></tr>";
        }
        
        // finish the table
        echo "</TABLE>";
    }
}

?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// AddModifySquawk(ModifySquawk, ModURL)
//
// Purpose: Add or modify squawk for this aircraft.
//
// Inputs:
//   ModURL - URL to modify or update the squawk
//   ModifySquawk - set true if modifying a squawk
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function AddModifySquawk(ModifySquawk, ModURL)
{  
    var xScreenSize, yScreenSize; 
    var xOffset, yOffset;
    var xMax = parent.document.body.clientWidth;
    var yMax = parent.document.body.clientHeight;
    
    if (ModifySquawk)
    {
        xScreenSize = 625;
        yScreenSize = 550;
    }
    else
    {
        xScreenSize = 625;
        yScreenSize = 340;
    }
    
    if (navigator.appName == "Microsoft Internet Explorer")
    {
        // IE
        var top = parent.screenTop;
        var left = parent.screenLeft;
    }
    else
    {
        // netscape
        var top = window.screenY;
        var left = window.screenX;
    }
    yOffset = (yMax - yScreenSize)/2 + top; 
    xOffset = (xMax - xScreenSize)/2 + left;        
    
    openPopWin("", xScreenSize, yScreenSize, "", xOffset, yOffset);
    popWin.location.href = "AddModifySquawks.php" + ModURL;
}

//-->
</script>
