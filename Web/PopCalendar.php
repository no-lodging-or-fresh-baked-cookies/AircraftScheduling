<?php

//-----------------------------------------------------------------------------
// 
// PopCalendar.php
// 
// PURPOSE: Draw the popup calendars 
// 
// PARAMETERS:
//       day - day for the HTML link
//       dayField - control to update when a selection is made
//       month - month for the HTML link
//       monthField - control to update when a selection is made
//       year - year for the HTML link
//       yearField - control to update when a selection is made
//       YearStart - first year in the year drop down box
//       JavaHandler - optional javascript procedure to call when a change is made
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
// 1.16    Mar 21, 2004  JCovington  Add a popup calendar for date goto buttons.
// 
// -----------------------------------------------------------------------------

    include "global_def.inc";
    include "style.inc";
    include "calendar.inc";
    
    // set default data
    $day = 1;
    $JavaHandler = "";
	$dayField = "";
	$monthField = "";
	$yearField = "";
	$YearStart = "";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["dayField"])) $dayField = $rdata["dayField"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["monthField"])) $monthField = $rdata["monthField"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["yearField"])) $yearField = $rdata["yearField"];
    if(isset($rdata["YearStart"])) $YearStart = $rdata["YearStart"];
    if(isset($rdata["JavaHandler"])) $JavaHandler = $rdata["JavaHandler"];
    
    // override the calendar class so that we can return the correct link for each
    // date on the calendar
    class MyCalendar extends Calendar
    {
    	    var $dayField;
    	    var $monthField;
    	    var $yearField;
    	    var $YearStart;
    	    
    		// constructor to set the needed information for the links
    		function __construct(
    							$dayField, 
    							$monthField, 
    							$yearField,
    							$YearStart)
    		{
    			$this->dayField  = $dayField;
    			$this->monthField  = $monthField;
    			$this->yearField = $yearField;
    			$this->YearStart = $YearStart;
    		}
        // set the date link HTML
    	function getDateLink($day, $month, $year)
    	{
    	    global $JavaHandler;
    	    
    		$link = 
    		    "javascript:returndate(" .
    		            "$day," .
    		            "'$this->dayField'," .
    		            "$month," .
    		            "'$this->monthField'," .
    		            "$year," .
    		            "'$this->yearField'," .
    		            "$this->YearStart," .
    		            "'$JavaHandler');";
    		return $link;
    	}
    
    	// enable the month navigation
    	function getCalendarLink($month, $year)
    	{
    	    global $JavaHandler;
    	    
            // Redisplay the current page, but with some parameters
            // to set the new month and year
            $s = getenv('SCRIPT_NAME');
            return 
               "$s?month=$month&year=$year&dayField=$this->dayField&monthField=$this->monthField&yearField=$this->yearField&YearStart=$this->YearStart&JavaHandler=$JavaHandler";
        }
    }	
    
    // get the dates for the calendar
    $thismonth = mktime(0, 0, 0, $month, $day, $year);
    
    // get a copy of the calendar
    $cal = new MyCalendar($dayField, $monthField, $yearField, $YearStart);
    
    // First, create an array of month names, January through December
    $ShortMonths = array("Jan", "Feb", "Mar", "Apr",
    						"May", "Jun", "Jul", "Aug", "Sept",
    						"Oct", "Nov", "Dec");
    $cal->setMonthNames($ShortMonths);
    
    // create the current month calendar
    echo "<td>";
    echo $cal->getMonthView(date("m", $thismonth), date("Y", $thismonth));
    echo "</td>";

?>
<!-- ############################### javascript procedures ######################### -->
<script LANGUAGE="JavaScript"><!--
function ignore()
{
	return true;
}

function returndate(
                    vDay, 
                    vDayField, 
                    vMonth, 
                    vMonthField, 
                    vYear, 
                    vYearField, 
                    YearStart, 
                    JavaHandler)
{
	window.onerror=ignore;
    vDayIndex = vDay - 1;
    vMonthIndex = vMonth - 1;
    vYearIndex = vYear - YearStart;
	eval("window.opener." + vDayField + ".selectedIndex='" + vDayIndex + "';");
	eval("window.opener." + vMonthField + ".selectedIndex='" + vMonthIndex + "';");
	eval("window.opener." + vYearField + ".selectedIndex='" + vYearIndex + "';");
	
	// if we have a handler specified, call the handler to finish the updates
	if (JavaHandler.length > 0)
	{
	    // handler specified, call it
    	eval("window.opener." + JavaHandler + "();");
	}
	self.close();
}
//--></script>