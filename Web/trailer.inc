<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: trailer.inc,v 1.4 2001/12/20 07:05:57 mbarclay Exp $

global $weekstarts;
global $pview;
global $lang;

// number of date entries to display (should be an even number)
$NumberDateEntries = 12;

// colors for tables
$BorderColor = '#CCCCCC';
$BackgroundColor = '#fde1dd';

if ( $pview != 1 ) 
{
    if (user_logged_on() || !$User_Must_Login)
    { 
        // show the month and week links if the user is logged in        
        if(!isset($year))
        	$year = strftime("%Y");
        
        if(!isset($month))
        	$month = strftime("%m");
        
        if(!isset($day))
        	$day = strftime("%d");
        
        if (empty($resource))
        	$params = "";
        else
        	$params = "&resource=$resource";
        
        if (!empty($InstructorResource))
        	$params .= "&InstructorResource=$InstructorResource";
        
        if (!empty($makemodel))
                $params .= "$makemodel";
        
        if (!empty($resource_id))
                $params .= "&resource_id=$resource_id";
        
        if (!empty($InstructorResource))
                $params .= "&InstructorResource=$InstructorResource";

		echo "<BR>";
		echo "<TABLE WIDTH='100%'>";
		echo "<TR>";
		echo "<TD BGCOLOR='$BorderColor'>";
		echo "<TABLE WIDTH='100%' BORDER=0>";
        
        // display the day links
		echo "<TR>";
		echo "<TD BGCOLOR='$BackgroundColor'>";
        echo "<B>$lang[viewday]:</B>";
		echo "</TD>";
        for($i = -($NumberDateEntries / 2); $i < ($NumberDateEntries / 2); $i++)
        {
			echo "<TD BGCOLOR='$BackgroundColor'>";
        	$ctime = mktime(0, 0, 0, $month, $day + $i, $year);
        
        	$str = strftime(empty($dateformat)? "%b %d" : "%d %b", $ctime);
        
        	$cyear  = date("Y", $ctime);
        	$cmonth = date("m", $ctime);
        	$cday   = date("d", $ctime);
        	echo "<a href=\"day.php?year=$cyear&month=$cmonth&day=$cday$params\">$str</a>\n";
			echo "</TD>";
        }
        
        // display the week links
		echo "<TR>";
		echo "<TD BGCOLOR='$BackgroundColor'>";
        echo "<B>$lang[viewweek]:</B>";
		echo "</TD>";
        
        if (!empty($room)) $params .= "&room=$room";
        
        $ctime = mktime(0, 0, 0, $month, $day, $year);
        # How many days to skip back to first day of week:
        $skipback = (date("w", $ctime) - $weekstarts + 7) % 7;       	
        for ($i = 0; $i < $NumberDateEntries; $i++)
        {
			echo "<TD BGCOLOR='$BackgroundColor'>";
        	$ctime = mktime(0, 0, 0, $month, $day + 7 * $i - $skipback, $year);
        	$str = strftime(empty($dateformat)? "%b %d" : "%d %b", $ctime);
        	
        	$cday   = date("d", $ctime);
        	$cmonth = date("m", $ctime);
        	$cyear  = date("Y", $ctime);
        	echo "<a href=\"week.php?year=$cyear&month=$cmonth&day=$cday$params\">$str</a>\n";
			echo "</TD>";
        }
        
        // display the month links
		echo "<TR>";
		echo "<TD BGCOLOR='$BackgroundColor'>";
        echo "<B>$lang[viewmonth]:</B>";
		echo "</TD>";
        for ($i = 0; $i < $NumberDateEntries; $i++)
        {
			echo "<TD BGCOLOR='$BackgroundColor'>";
        	$ctime = mktime(0, 0, 0, $month + $i, 1, $year);
        	$str = strftime("%b %Y", $ctime);
        	
        	$cmonth = date("m", $ctime);
        	$cyear  = date("Y", $ctime);
        	echo "<a href=\"month.php?year=$cyear&month=$cmonth$params\">$str</a>\n";
			echo "</TD>";
        }
		echo "</TABLE>";
		echo "</TABLE>";
        
        echo "<HR>";

        // if we don't already have parameters in the request string, the pview 
        // parameter is the first parameter
        if (strpos($_SERVER["REQUEST_URI"], "?") > 0)
        	echo '<a href="' . $_SERVER["REQUEST_URI"] . '&pview=1">' . $lang["ppreview"] . '</a>';
        else
        	echo '<a href="' . $_SERVER["REQUEST_URI"] . '?pview=1">' . $lang["ppreview"] . '</a>';
    }  
    else
    {
        // user is not logged in, print a seperator
        echo "<BR><BR><HR>";
    }
}
else
{
    // user selected print preview mode, print a seperator
    echo "<BR><BR><HR>";
}
  
// show the copyright notice
echo "<CENTER>Copyright 2003-2021, <A href='mailto:jcovington950@gmail.com'>Jim Covington</A></CENTER>";
echo "<CENTER>Based on the <a href='http://openfbo.sourceforge.net/'>OpenFBO Booking System</a> written by Matt Barclay</CENTER>";
echo "<CENTER>and the <a href='http://sourceforge.net/project/?group_id=5113'>Meeting Room Booking System</a></CENTER><BR>";

?>
