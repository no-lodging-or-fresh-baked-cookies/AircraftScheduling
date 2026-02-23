<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: JAVALib.inc,v 1.11 2004/03/24 07:05:57 jcovington Exp $

###########################################################################
#   AircraftScheduling Define Java common library file
###########################################################################

# common javascript procedures
?>
<!--BEGIN Pop-up Windows Script------------------------------------------>
<SCRIPT Language="JavaScript">
<!--
// Copyright 1999 - 2002 by Ray Stott, Pop-up Windows Script ver 2.0
// OK to use if this copyright is included
// Script is available at http://www.crays.com/jsc          

var popWin = null    // use this when referring to pop-up window
var winCount = 0
var winName = "popWin"
function openPopWin(winURL, winWidth, winHeight, winFeatures, winLeft, winTop){
  var d_winLeft = 20  // default, pixels from screen left to window left
  var d_winTop = 20   // default, pixels from screen top to window top
  winName = "popWin" + winCount++ //unique name for each pop-up window
  closePopWin()           // close any previously opened pop-up window
  if (openPopWin.arguments.length >= 4)  // any additional features? 
    winFeatures = "," + winFeatures
  else 
    winFeatures = "" 
  if (openPopWin.arguments.length == 6)  // location specified
    winFeatures += getLocation(winWidth, winHeight, winLeft, winTop)
  else
    winFeatures += getLocation(winWidth, winHeight, d_winLeft, d_winTop)
  popWin = window.open(winURL, winName, "width=" + winWidth 
           + ",height=" + winHeight + winFeatures)
  }
function closePopWin(){    // close pop-up window if it is open 
  if (navigator.appName != "Microsoft Internet Explorer" 
      || parseInt(navigator.appVersion) >=4) //do not close if early IE
    if(popWin != null) if(!popWin.closed) popWin.close() 
  }
function getLocation(winWidth, winHeight, winLeft, winTop){
  return ""
  }
//-->
</SCRIPT>
<SCRIPT Language="JavaScript1.2">  // for Netscape 4+ and IE 4+
<!--
function getLocation(winWidth, winHeight, winLeft, winTop){
  var winLocation = ""
  if (winLeft < 0)
    winLeft = screen.width - winWidth + winLeft
  if (winTop < 0)
    winTop = screen.height - winHeight + winTop
  if (winTop == "cen")
    winTop = (screen.height - winHeight)/2 - 20
  if (winLeft == "cen")
    winLeft = (screen.width - winWidth)/2
  if (winLeft>0 & winTop>0)
    winLocation =  ",screenX=" + winLeft + ",left=" + winLeft   
                + ",screenY=" + winTop + ",top=" + winTop
  else
    winLocation = ""
  return winLocation
  }
//-->
</SCRIPT>
<!--END Pop-up Windows Script------------------------------------------->

<script language="javascript">
<!--

function popcal(vDay, vDayField, vMonth, vMonthField, vYear, vYearField, vYearStart, JavaHandler)
{   
    var xScreenSize = 130, yScreenSize = 140;
    var xMax = document.body.clientWidth;
    var yMax = document.body.clientHeight;
    var xOffset;
    var yOffset;
    
    if (navigator.appName == "Microsoft Internet Explorer")
    {
        // IE
        var top = self.screenTop;
        var left = self.screenLeft;
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
    popWin.location.href = "PopCalendar.php?" +
                "day=" + vDay +  
                "&dayField=" + vDayField +  
                "&month=" + vMonth +
                "&monthField=" + vMonthField +
                "&year=" + vYear +
                "&yearField=" + vYearField +
                "&YearStart=" + vYearStart +
                "&JavaHandler=" + JavaHandler;
}
//-->
</script>

<script language="javascript">
<!--
// generic positive number decimal formatting function
function format(expr, decplaces) 
{
    // raise incoming value by power of 10 times the
    // number of decimal places; round to an integer; convert to string
    var str = "" + Math.round(eval(expr) * Math.pow(10, decplaces));
    
    // if we have NaN, return a null string
    if (str == "NaN")
    {
        // not a number, return null string
        return "";
    }
    else
    {    
        // pad small value strings with zeros to the left of rounded number
        while (str.length <= decplaces) 
        {
            str = "0" + str;
        }
        
        // establish location of decimal point
        var decpoint = str.length - decplaces;
        
        // assemble final result from: (a) the string up to the position of
        // the decimal point; (b) the decimal point; and (c) the balance
        // of the string. Return finished product.
        if (decplaces > 0)
            return str.substring(0, decpoint) + "." + str.substring(decpoint,str.length);
        else
            return str.substring(0, decpoint);
    }
}

// turn incoming expression into a dollar value
function dollarize(expr) 
{
    return CurrencyPrefix + format(expr,2);
}
 
//-->
</script>

<script language="javascript">
<!--
// remove invalid characters from a string
function RemoveInvalidChars(StringExpression) 
{    
    // remove " 
    StringExpression = StringExpression.replace(/"/g, " ");

    // remove '" 
    StringExpression = StringExpression.replace(/'/g, " ");
    
    // return the updated string
    return StringExpression;
}
 
//-->
</script>

<?php

