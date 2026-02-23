<?php
//-----------------------------------------------------------------------------
// 
// site_faq.inc
// 
// PURPOSE: Display the help system FAQ page.
// 
// PARAMETERS:
//      none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//      This is mostly HTML. The PHP wrapper is so that certain parts
//      can be turned off if aircraft checkout and checkin is not enabled.
// 
// -----------------------------------------------------------------------------
    
?>

    <p>
    
    <b><a name=top>Authentication</a></b>
    <menu>
    <a href="#authenticate"><u>How do I login?</u></a><br>
    <a href="#forgotpass"><u>I forgot my password!</u></a><br>
    <a href="#aircraft_delete"><u>Why can't I delete/alter an aircraft or instructor schedule?</u></a><br>
    <a href="#updateaddress"><u>How do I update my user information (name, address, phone number, etc.)?</u></a><br>
    <a href="#updatepassword"><u>How do I change my password?</u></a><br>
    <a href="#no_phonenumber"><u>How do I prevent the display of my phone numbers?</u></a><br>
    </menu>
    
    <b>Schedule/Altering Aircraft or Instructor Schedules</b>
    <menu>
    <a href="#scheduling"><u>How do I schedule an aircraft or instructor?</u></a><br>
    <a href="#delete"><u>How do I delete a schedule?</u></a><br>
    <a href="#repeating"><u>How do I make a recurring schedule?</u></a><br>
    <a href="#repeating_delete"><u>How do I delete one instance of a recurring schedule?</u></a><br>
    <a href="#too_many"><u>My aircraft or instructor schedule failed to be created because of "<i>too many entries</i>"!</u></a><br>
    <a href="#multiple_users"><u>What happens if multiple people schedule the same aircraft or instructor?</u></a><br>
    <a href="#standby"><u>How do I request to be on standby for an aircraft or instructor?</u></a><br>
    </menu>
  
	<?
 	if ($AllowAircraftCheckout)
	{
	?>    
        <b>Checkout/Checkin an Aircraft</b>
        <menu>
        <a href="#CheckoutAircraft"><u>Check out an aircraft?</u></a><br>
        <a href="#CheckinAircraft"><u>Check in an aircraft?</u></a><br>
        </menu>
	<?
    }
	?>    
    
    <b>Administration</b>
    <menu>
    <a href="#AddNewMember"><u>Add a new user to the database?</u></a><br>
    <a href="#ModifyExistingMember"><u>Modify an existing user's information?</u></a><br>
    <a href="#AddNewAircraft"><u>Add a new aircraft to the database?</u></a><br>
    <a href="#ModifyExistingAircraft"><u>Modify an existing aircraft information?</u></a><br>
  
	<?
 	if ($AllowAircraftCheckout)
	{
	?>    
        <a href="#CheckUserCurrency"><u>Check a user's currency?</u></a><br>
        <a href="#CheckMemberBill"><u>Check a user's bill?</u></a><br>
	<?
    }
	?>    
    </menu>
    
    <b>About The AircraftScheduling Booking System</b>
    <menu>
    <a href="#about"><u>How does the system work and who wrote it?</u></a><br>
    </menu>
    
    
    <hr>
    <p>
    <a name=authenticate><b>How do I sign in?</b></a>
    <menu>
  
	<?
 	if ($AllowAircraftCheckout)
	{
	?>    
        <p>
        To sign in, select the <A HREF="logon.php<? echo "?" . $_SERVER["QUERY_STRING"] ?>">Sign In</A> link from the blue bar at the top of the screen.
        Enter the username and password that was assigned when you joined.
        </p>
	<?
    }
    else
    {
	?>    
        <p>
        To sign in, select the <A HREF="logon.php<? echo "?" . $_SERVER["QUERY_STRING"] ?>">Sign In</A> link from the blue bar at the top of the screen.
        Enter the username and password that you picked when you <A HREF="register.php">registered</A> for access.
        </p>
        <p>
        All users must <A HREF="register.php">register</A> for access to the system. After your registration
        is complete, the account is disabled until the system administrator has enabled the account. This
        will usually occur within one day of registration.
        </p>
	<?
    }
	?>    
    <p>
    Some functions are restricted to certain users, and other users will get the message 
    <i>You do not have access rights to modify this item</i>. See your system administrator 
    if you are not able to create and modify your aircraft or instructor schedules.
    </p>
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name="forgotpass"><b>I forgot my password!</b></a>
    <menu>
    <p>
    To have your password sent to your Email address, <a href='email_pass.php'>use this form</a>.
    Your password will be sent to the Email address you have on file.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name="aircraft_delete"><b>Why can't I delete/alter an aircraft or instructor schedule?</b></a>
    <menu>
    <p>
    In order to delete or alter an aircraft or instructor schedule, you must be logged in as the
    same person that scheduled .  Contact one of the
    office staff or the person who initially scheduled the aircraft or instructor to
    have it deleted or changed.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    <p>
    
    <a name="updateaddress"><b>How do I update my user information (name, address, phone number, etc.)?</b></a>
    <menu>
    <p>
    You may update your user information (name, address, etc.) after you have signed in, by selecting 
    <A HREF="change_user_info.php<? echo "?" . $_SERVER["QUERY_STRING"] ?>">Change User Info</A> in the top bar. 
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <a name="updatepassword"><b>How do I change my password?</b></a>
    <menu>
    <p>
    You may change your password after you have signed in, by selecting 
    <A HREF="change_pass.php<? echo "?" . $_SERVER["QUERY_STRING"] ?>">Change Password</A> in the top bar. 
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <a name="no_phonenumber"><b>How do I prevent the display of my phone numbers?</b></a>
    <menu>
    <p>
    You may prevent your phone numbers from being displayed by selecting 
    <A HREF="change_user_info.php<? echo "?" . $_SERVER["QUERY_STRING"] ?>">Change User Info</A> in the top bar after you have signed in. 
    Clearing the selection for the Display Phone Numbers checkbox. 
    <p>
    Note that any existing scheduled
    entries will continue to display your phone number. All schedules created in the future will show
    "UNLISTED" instead of the phone number. If you wish, you may go back and edit your existing
    schedule entries to clear the phone number there.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name=scheduling><b>How do I schedule an aircraft or instructor?</b></a>
    <menu>
    <p>
    Clicking on the desired time brings you into the booking screen.
    The screen will be slightly different depending on how you logged 
    on. 
    <p>
    As a normal user, your name and phone number will be filled in.
    The name cannot be changed, but a different phone number may be
    entered. If you are logged in as one of the office staff (super user), you can
    make reservations for other users so the name field is now a 
    drop down list of all users. As you select a user, their default
    phone number is placed in the phone number field. The phone number
    may be changed as desired.
    <p>
    Enter any notes about the schedule in the description field. This field
    may be left blank if desired.
    <p>
    If you are scheduling an aircraft and wish to schedule an instructor
    for the same time, select the instructor from the drop down list. Set to none
    (the default) if you want to schedule just the aircraft.
    <p>
    Select the date for the schedule. The default date is the date selected
    on the main screen. The date may be changed to any date desired.
    <p>
    Enter the starting time. The default time is the time selected on the main
    screen. The time may be changed to any time desired.
    <p>
    Enter the duration of the booking or select All day as desired. The time may
    be entered in the selected units (minutes, hours, days or weeks). The default
    time is 2 hours.
    <p>
    See <a href="#repeating">How do I make a recurring schedule?</a> on the use of
    the repeat information items.
    <p>
    When all entries are complete, press the Save button to complete the scheduling.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name="delete"><b>How do I delete a schedule?</b></a>
    <menu>
    <p>
    Select the day/aircraft/instructor/time that you want to delete and select <b>Delete Entry</b>.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name=repeating><b>How do I make a recurring schedule?</b></a>
    <menu>
    <p>
    Clicking on the desired time brings you into the booking screen.
    Select the appropriate <b>Repeat Type</b>. The aircraft or instructor will be scheduled
    at the same time, until the <b>Repeat End Date</b>, on the days
    determined by the <b>Repeat Type</b>.
    <p>
    A <i>Daily</i> repeat schedules every day.
    A <i>Weekly</i> repeat schedules those days of the week that you
    check under <b>Repeat Day</b>. For example, use Weekly repeat
    to schedule the aircraft or instructor every Monday, Tuesday, and Thursday; check
    those days under Repeat Day. If you check no days under Repeat Day,
    the schedule will repeat on the same day of each week as the first
    scheduled day.
    A <i>Monthly</i> repeat schedules the same day of each month,
    for example the 15th of the month.
    A <i>Yearly</i> repeat schedules the same month and day of the month,
    for example every March 15th.
    Finally, a <i>Monthly, corresponding day</i> repeat schedules one day
    each month, the same weekday and ordinal position within the month.
    Use this repeat type to schedule the first Monday, second Tuesday,
    or fourth Friday of each month, for example.
    Do not use this repeat type after the 28th day of the month.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name="repeating_delete"><b>How do I delete one instance of a recurring schedule?</b></a>
    <menu>
    <p>
    Select the day/aircraft/instructor/time that you want to delete and select <b>Delete Entry</b>.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name="too_many"><b>My schedule failed to be created because of "<i>too many entries</i>"!</b></a>
    <menu>
    <p>
    Any schedule cannot create more than 365 entries.  There needs to be some limit on the
    number of schedule created.  This number can be increased if necessary.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name="multiple_users"><b>What happens if multiple people schedule the same aircraft or instructor?</b></a>
    <menu>
    <p>
    The short answer is: The first person to click on
    the <b>Submit</b> button wins.<br>
    Behind the scenes, the system is using a proper multi-user, multi-threaded
    relational database than can handle many thousands of simultaneous users.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
    
    <p>
    <a name="standby"><b>How do I request to be on standby for an aircraft or instructor?</b></a>
    <menu>
    <p>
    If the aircraft or instructor that you wish to schedule is already booked for a time period,
    you may request the same time on a standby basis. To be added to the standby request list for a 
    particular time, select the time period that has an existing schedule to bring up the details
    for that booking. Select the <b>Add Standby Request</b> and fill in the requested information.
    If the original schedule is canceled, you will be notified by email (assuming you have a valid
    email on file) that the time period has been given up and you are now scheduled.
    <p>
    Any number of standby requests may be made. The system will schedule the next person in order
    any time an existing booking is canceled.
    <p>
    Note that the system will not assign your standby request unless the entire time period is
    available. In other words, if you have requested a two hour block and only 1.5 hours is available,
    the system will not assign any of the available time to you.
    </menu>
    <a href="#top">Top</a><hr>
    </p>
  
	<?
 	if ($AllowAircraftCheckout)
	{
	?>
        <p>
        <a name="CheckoutAircraft"><b>Check Out An Aircraft</b></a>
        <menu>
        <p>
            To check out an aircraft requires the following steps: 
            
            <ol style="font-family: Verdana; font-size: 10pt">
              <li><p style="mso-margin-top-alt:auto">Select the Aircraft Checkout link in the
                menu bar at the top of the screen to take the user to the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckoutScreen/AircraftCheckoutScreen.htm">Aircraft Check Out</a> Screen.</p>
              </li>
            </ol>
            
            <p class="mvd-p-note">Note: If you already have an aircraft checked out, the Aircraft
            Checkout link will not be shown, the Aircraft Checkin link will be shown instead. Only
            one aircraft may be checked out by a user at a time.
            
            <ol style="font-family: Verdana; font-size: 10pt" start="2">
              <li><p style="mso-margin-top-alt:auto">A drop down box at the top of the 
              <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckoutScreen/AircraftCheckoutScreen.htm">Aircraft Check Out</a>
              screen may be used to select the desired aircraft to checkout. The system will look
              within two hours of the current time for a scheduled aircraft and select that aircraft
               as the default aircraft to checkout. If this is not the desired aircraft, select 
               the correct one from the list.</p>
              </li>
              <li><p style="mso-margin-top-alt:auto">After filling in the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help//AircraftCheckoutScreen/WeightBalanceFrame.htm">Weight and Balance</a> fields of
                the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckoutScreen/AircraftCheckoutScreen.htm">Aircraft Check Out</a>
                Screen and assuming that the pilot's <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckoutScreen/FlightCurrencyFrame.htm">Flight Currency</a> and <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckoutScreen/FlightStatus.htm">Flight Status</a> are valid and the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckoutScreen/AircraftDataFrame.htm">Aircraft Status</a> is valid, the
                program will allow the aircraft to be checked out.</p>
              </li>
            </ol>
        </menu>
        <a href="#top">Top</a><hr>
        </p>
        
        <p>
        <a name="CheckinAircraft"><b>Check In An Aircraft</b></a>
        <menu>
        <p>
            To check in an aircraft requires the following steps: 
            
            <ol style="font-family: Verdana; font-size: 10pt">
              <li><p style="mso-margin-top-alt:auto">Select the Aircraft Checkin link in the
                menu bar at the top of the screen to take the user to the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckinScreen/AircraftCheckinScreen.htm">Aircraft Check-in</a> Screen.</p>
              </li>
              <li><p style="mso-margin-top-alt:auto">After filling in the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckinScreen/CheckinInformationFrame.htm">Check-in Information</a>
                fields of the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckinScreen/AircraftCheckinScreen.htm">Aircraft
                check-in</a> Screen and entering any new aircraft squawks using the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/AircraftCheckinScreen/SquawkDataDisplay.htm">Squawk Data</a> fields, the program
                will allow the aircraft to be checked in.</p>
              </li>
            </ol>
        </menu>
        <a href="#top">Top</a><hr>
        </p>
    <?
    } 
    ?>
     
    <p>
    <a name="AddNewMember"><b>Add A New User</b></a>
    <menu>
    <p>
        To add a new user to the database requires the following steps: 
                
        <ol style="font-family: Verdana; font-size: 10pt">
          <li><p style="mso-margin-top-alt:auto">
          If you have the necessary privileges to enter a new user, there will be
          an Admin link in the menu bar at the top of the screen. The Admin link takes
          you to the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen.</p>
          </li>
          <li><p style="mso-margin-top-alt:auto">
          From the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen, select User Management
          to take you to the 
          <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/UserManagementScreen.htm">User Display Screen</a></p>
          </li>
          <li><p style="mso-margin-top-alt: auto">From the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/UserManagementScreen.htm">User Display Screen</a>, select
            the Add User link to start the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/AddMemberScreen/AddMemberScreen.htm">Add New User</a>
            screen.</p>
          </li>
          <li><p style="mso-margin-top-alt: auto">After all entries are complete in the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/AddMemberScreen/AddMemberScreen.htm">Add New User</a>,
            select the Submit button and the new user is saved in the database.</p>
          </li>
        </ol>
            </menu>
    <a href="#top">Top</a><hr>
    </p>
     
    <p>
    <a name="ModifyExistingMember"><b>Modify An Existing User</b></a>
    <menu>
    <p>
        To modify an existing user's information in the database requires the following
        steps: 
                
        <ol style="font-family: Verdana; font-size: 10pt">
          <li><p style="mso-margin-top-alt:auto">
          If you have the necessary privileges to modify a user, there will be
          an Admin link in the menu bar at the top of the screen. The Admin link takes
          you to the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen.</p>
          </li>
          <li><p style="mso-margin-top-alt:auto">
          From the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen, select User Management
          to take you to the 
          <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/UserManagementScreen.htm">User Display Screen</a></p>
          </li>
          <li><p style="mso-margin-top-alt: auto">From the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/UserManagementScreen.htm">User Display Screen</a>, select
            the link from the username of the user to start the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/ModifyMemberScreen/ModifyMemberScreen.htm">Modify User</a>
            screen.</p>
          </li>
          <li><p style="mso-margin-top-alt: auto">After any entries are modified in the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/ModifyMemberScreen/ModifyMemberScreen.htm">Modify User</a>
            screen, select the Submit button and the updated user information is saved in the database.</p>
          </li>
        </ol>
    </menu>
    <a href="#top">Top</a><hr>
    </p>
     
    <p>
    <a name="AddNewAircraft"><b>Add A New Aircraft</b></a>
    <menu>
    <p>
        To add a new aircraft to the database requires the following steps: 
        
        <ol style="font-family: Verdana; font-size: 10pt">
          <li><p style="mso-margin-top-alt:auto">
          If you have the necessary privileges to add a new aircraft, there will be
          an Admin link in the menu bar at the top of the screen. The Admin link takes
          you to the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen.</p>
          </li>
          <li><p style="mso-margin-top-alt:auto">
          From the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen, select Aircraft Management
          to take you to the 
          <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayAircraftScreen/DisplayAircraftScreen.htm">Aircraft Management Screen</a></p>
          </li>
          <li><p style="mso-margin-top-alt: auto">From the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayAircraftScreen/DisplayAircraftScreen.htm">Aircraft Management Screen</a>,
            select the Add Aircraft link to start the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AddAircraftScreen.htm">Add New Aircraft </a>screen.</p>
          </li>
          <li type="disc" value="5"><p style="mso-margin-top-alt: auto">After all entries are complete
            in the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayAircraftScreen/AddAircraftScreen/AddAircraftScreen.htm">Add New
            Aircraft</a>, select the Submit button and the new aircraft is saved in the database.</p>
          </li>
          <li type="disc" value="6"><p style="mso-margin-top-alt: auto">It is recommended that a
            keycode (member status of Aircraft) that corresponds to the new aircraft be added to the
            user list using the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserManagementScreen/AddMemberScreen/AddMemberScreen.htm">Add
            New User</a> screen. Any aircraft checked out using the tail number keycode will be
            recognized as a maintenance flight for all reports printed.</p>
          </li>
        </ol>
    </menu>
    <a href="#top">Top</a><hr>
    </p>
     
    <p>
    <a name="ModifyExistingAircraft"><b>Modify An Existing Aircraft</b></a>
    <menu>
    <p>
        To modify an existing aircraft in the database requires the following steps: 
        
        <ol style="font-family: Verdana; font-size: 10pt">
          <li><p style="mso-margin-top-alt:auto">
          If you have the necessary privileges to modify an aircraft, there will be
          an Admin link in the menu bar at the top of the screen. The Admin link takes
          you to the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen.</p>
          </li>
          <li><p style="mso-margin-top-alt:auto">
          From the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen, select Aircraft Management
          to take you to the 
          <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayAircraftScreen/DisplayAircraftScreen.htm">Aircraft Management Screen</a></p>
          </li>
          <li><p style="mso-margin-top-alt: auto">From the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayAircraftScreen/DisplayAircraftScreen.htm">Aircraft Management Screen</a>,
            select the tailnumber link to start the <a
            href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayAircraftScreen/ModifyAircraftScreen/ModifyAircraftScreen.htm">Modify
            Aircraft</a> screen.</p>
          </li>
        </ol>
        
        <p style="mso-margin-top-alt: auto" class="mvd-P-Note">Note: The aircraft type may be
        changed from the <a
        href="DisplayHelpHTML.php?HelpHTMLFile=Help/ModifyAircraftMakeModel/ModifyAircraftMakeModel.htm">Modify Aircraft Make and Model</a> screen. 
       
        <ol style="font-family: Verdana; font-size: 10pt">
          <li type="disc" value="4"><p style="mso-margin-top-alt: auto">After any entries are modified
            in the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayAircraftScreen/ModifyAircraftScreen/ModifyAircraftScreen.htm">Modify
            Aircraft</a>, select the Submit button and the updated aircraft information is saved in the
            database.</p>
          </li>
        </ol>
    </menu>
    <a href="#top">Top</a><hr>
    </p>
  
	<?
 	if ($AllowAircraftCheckout)
	{
	?>
        <p>
        <a name="CheckUserCurrency"><b>Check A User's Currency</b></a>
        <menu>
            <p style="mso-margin-top-alt: auto">To check a user's currency: 
            
            <ol style="font-family: Verdana; font-size: 10pt">
              <li><p style="mso-margin-top-alt:auto">Select the Check Currency link in the
                menu bar at the top of the screen to take the user to the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserCurrencyScreen/UserCurrencyScreen.htm">Check Currency Screen<strong>.</strong></a></p>
            </ol>
        </menu>
        <a href="#top">Top</a><hr>
        </p>
        
        <p>
        <a name="CheckMemberBill"><b>Check A User's Bill</b></a>
        <menu>
        <p>
            To check a user's bill in the database can be accomplished in two ways. The first
            method is usually used by the manager or office staff to update a user's charges. This
            is accomplished by: 
            
            <ol style="font-family: Verdana; font-size: 10pt">
              <li><p style="mso-margin-top-alt:auto">
              If you have the necessary privileges to modify user charges, there will be
              an Admin link in the menu bar at the top of the screen. The Admin link takes
              you to the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen.</p>
              </li>
              <li><p style="mso-margin-top-alt:auto">
              From the <a href="DisplayHelpHTML.php?HelpHTMLFile=Help/Admin/AdminHelp.php">Admin</a> Screen, select Display User Charges
              to take you to the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayMemberChargesScreen/DisplayMemberChargesScreen.htm">Display User Charges</a>
                screen.</p>
              </li>
            </ol>
            
            <p style="mso-margin-top-alt: auto">The second method is usually used by the general
            membership when a user is checking their own billing information. This is accomplished
            by: 
            
            <ol style="font-family: Verdana; font-size: 10pt">
              <li><p style="mso-margin-top-alt:auto">Select the Display Charges link in the
                menu bar at the top of the screen to take the user to the <a
                href="DisplayHelpHTML.php?HelpHTMLFile=Help/DisplayMemberChargesScreen/DisplayMemberChargesScreen.htm">Display User Charges</a>
                screen<a href="DisplayHelpHTML.php?HelpHTMLFile=Help/UserCurrencyScreen/UserCurrencyScreen.htm"><strong>.</strong></a></p>
              </li>
            </ol>
        </menu>
        <a href="#top">Top</a><hr>
        </p>
    <?
    } 
    ?>
    
    <p>
    <a name=about><b>How does the system work and who wrote it?</b></a>
    <menu>
    <p>
    The <a href="http://openfbo.sourceforge.net/">OpenFBO Booking System</a>
    written by Matt Barclay is based on the 
    <a href="http://sourceforge.net/project/?group_id=5113">Meeting Room Booking System</a>.
    AircraftScheduling was created from OpenFBO by Jim Covington.
    <p>
    The system is written mostly in <a href="http://www.php.net">PHP</a>, 
    which is an open source programming language that can be embedded in web 
    pages similar in concept to Microsoft active server pages.  PHP is especially 
    good at accessing databases.
    <p>  
    The database used for the system is either
    <a href="http://www.mysql.com">MySQL</a>
    or <a href="http://www.postgresql.org">PostgreSQL</a>.
    MySQL is a very fast, multi-threaded, multi-user and robust 
    SQL (Structured Query Language) database server that is also GPL.
    PostgreSQL is a full-featured multi-user open source Object Relational SQL
    database server.
    <p> 
    The system will run on multiple platforms, including the PC architecture
    using the <a href="http://www.linux.com">Linux</a> operating system.
    Linux, is a free, open source, unix-like operating system.    
    
    </menu>
    <a href="#top">Top</a><hr>
    </p>


