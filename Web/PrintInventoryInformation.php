<?php
//-----------------------------------------------------------------------------
// 
// PrintInventoryInformation.php
// 
// PURPOSE: Print inventory information in various ways.
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
//      GoBackParameters - parameters to return to previous page
//      PrintInventoryInformation - set to submit to print aircraft information
//      AircraftCancel - set to Cancel to cancel the printing.
//      debug_flag - set to non-zero to enable debug output information
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// -----------------------------------------------------------------------------

    include "global_def.inc";
    include "config.inc";
    include "AircraftScheduling_auth.inc";
    include "$dbsys.inc";
    include "functions.inc";
    require_once("CurrencyFunctions.inc");
    require_once("DatabaseFunctions.inc");

    // initialize variables
    $all = '';
    $PrintInventoryOption = 0;
    $PrintInventoryQtyLowOptions = 0;
    $PrintWholesaleInventoryOptions = 0;
    $PrintRetailInventoryOptions = 0;
    $PrintMaintenamceInventoryOptions = 0;
    $SortSelection = "Part_Number";

    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["all"])) $all = $rdata["all"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    if(isset($rdata["PrintInventoryInformation"])) $PrintInventoryInformation = $rdata["PrintInventoryInformation"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["PrintInventoryOption"])) $PrintInventoryOption = $rdata["PrintInventoryOption"];
    if(isset($rdata["PrintInventoryQtyLowOptions"])) $PrintInventoryQtyLowOptions = $rdata["PrintInventoryQtyLowOptions"];
    if(isset($rdata["PrintWholesaleInventoryOptions"])) $PrintWholesaleInventoryOptions = $rdata["PrintWholesaleInventoryOptions"];
    if(isset($rdata["PrintRetailInventoryOptions"])) $PrintRetailInventoryOptions = $rdata["PrintRetailInventoryOptions"];
    if(isset($rdata["PrintMaintenamceInventoryOptions"])) $PrintMaintenamceInventoryOptions = $rdata["PrintMaintenamceInventoryOptions"];
    if(isset($rdata["SortSelection"])) $SortSelection = $rdata["SortSelection"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
        
    //********************************************************************
    // BuildInventorySelector($SortSelection)
    //
    // Purpose: Display a selector for inventory report sorting.
    //
    // Inputs:
    //   SortSelection - current report sorting to select
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildInventorySelector($SortSelection)
    {
        // build the inventory sort selection
        // build the select HTML	
		echo "<SELECT NAME='SortSelection' id='SortSelection'>";
				
		// Category
		echo "<OPTION " .
				"VALUE='Category'" . 
				(Ucase($SortSelection) == UCase("Category") ? " SELECTED" : "") . 
				">Category";

		// date
		echo "<OPTION " .
				"VALUE='Date'" . 
				(Ucase($SortSelection) == UCase("Date") ? " SELECTED" : "") . 
				">Date";
		
		// Description
		echo "<OPTION " .
				"VALUE='Description'" . 
				(Ucase($SortSelection) == UCase("Description") ? " SELECTED" : "") . 
				">Description";
		
		// part number
		echo "<OPTION " .
				"VALUE='Part_Number'" . 
				(Ucase($SortSelection) == UCase("Part_Number") ? " SELECTED" : "") . 
				">Part Number";
		
		// Position
		echo "<OPTION " .
				"VALUE='Position'" . 
				(Ucase($SortSelection) == UCase("Position") ? " SELECTED" : "") . 
				">Position";
		
		// Quantity In Stock
		echo "<OPTION " .
				"VALUE='Quantity_In_Stock'" . 
				(Ucase($SortSelection) == UCase("Quantity_In_Stock") ? " SELECTED" : "") . 
				">Quantity In Stock";
		
		// Reorder Quantity
		echo "<OPTION " .
				"VALUE='Reorder_Quantity'" . 
				(Ucase($SortSelection) == UCase("Reorder_Quantity") ? " SELECTED" : "") . 
				">Reorder Quantity";
		
		// Retail Price
		echo "<OPTION " .
				"VALUE='Retail_Price'" . 
				(Ucase($SortSelection) == UCase("Retail_Price") ? " SELECTED" : "") . 
				">Retail Price";
		
		// Unit Price
		echo "<OPTION " .
				"VALUE='Unit_Price'" . 
				(Ucase($SortSelection) == UCase("Unit_Price") ? " SELECTED" : "") . 
				">Unit Price";

        // end the select box
		echo "</SELECT>";	
    }

    //********************************************************************
    // PrintInventory(SortParameter, SortName)
    //
    // Purpose:  Print the inventory sorted by the key specified in
    //           SortParameter
    //
    // Inputs:
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintInventory($SortParameter, $SortName)
    {
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        
        include "DatabaseConstants.inc";
        
        $MaxLineLength = 91;
        $LeftMargin = 1;
        $PageLength = 60;
        $HeaderLength = 1;
        $PartNumberLength = 14;
        $DescriptionLength = 20;
        $UnitPriceLength = 8;
        $RetailPriceLength = 8;
        $RemainingLength = 8;
        $LowLevelLength = 5;
        $PositionLength = 8;
        $CategoryLength = 13;
    
        // printer setup
        $PageHeader = "************ " . $AircraftScheduling_company . " INVENTORY ************";
        PrinterSetup(9);
        
        // build the $UnderLine string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
        
        // print the column header
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                    JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                    JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                    JustifyField("Retail", $CenterJustify, $RetailPriceLength) . " " .
                    JustifyField("Remain", $CenterJustify, $RemainingLength) . " " .
                    JustifyField("Low", $CenterJustify, $LowLevelLength) . " " .
                    JustifyField("Position", $CenterJustify, $PositionLength) . " " .
                    JustifyField("Category", $CenterJustify, $CategoryLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(" " . "<br>");
        
        // create a query to get all the inventory items
        $InventoryResult = SQLOpenRecordset(
                            "SELECT * FROM Inventory ORDER BY " . $SortParameter . "");
        
        // loop through all the inventory items and print the items
		$TextLines = 0;
        $PageNumber = 0;
        for($InventoryCnt=0; $InventoryRST = sql_row($InventoryResult, $InventoryCnt); $InventoryCnt++)  
        {
            // if we have filled the page, send the page to the printer
            if ($TextLines > $PageLength) 
            {
                // print the footer
                $PageNumber++;
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                              "<br>");
                
                // at max page length, print the page
                PrintNewPage();
                $TextLines = 0;
        
                // skip some space at the top of the form
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                             JustifyField("Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
                             "<br>");
               
                // skip some space below the header
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                }
        
                // print the column header
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                            JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                            JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                            JustifyField("Retail", $CenterJustify, $RetailPriceLength) . " " .
                            JustifyField("Remain", $CenterJustify, $RemainingLength) . " " .
                            JustifyField("Low", $CenterJustify, $LowLevelLength) . " " .
                            JustifyField("Position", $CenterJustify, $PositionLength) . " " .
                            JustifyField("Category", $CenterJustify, $CategoryLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(" <br>");
            }
            
            // print the item record
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField($InventoryRST[$InventoryPart_Number_offset], $LeftJustify, $PartNumberLength) . " " .
                        JustifyField($InventoryRST[$InventoryDescription_offset], $LeftJustify, $DescriptionLength) . " " .
                        JustifyField(FormatField($InventoryRST[$InventoryUnit_Price_offset], "Currency"), $RightJustify, $UnitPriceLength) . " " .
                        JustifyField(FormatField($InventoryRST[$InventoryRetail_Price_offset], "Currency"), $RightJustify, $RetailPriceLength) . " " .
                        JustifyField($InventoryRST[$InventoryQuantity_In_Stock_offset], $RightJustify, $RemainingLength) . " " .
                        JustifyField($InventoryRST[$InventoryReorder_Quantity_offset], $RightJustify, $LowLevelLength) . " " .
                        JustifyField($InventoryRST[$InventoryPosition_offset], $RightJustify, $PositionLength) . " " .
                        JustifyField($InventoryRST[$InventoryCategory_offset], $RightJustify, $CategoryLength) . "<br>");
            $TextLines++;
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the last page (if needed)
        if ($TextLines > 0) 
        {
            // skip to the bottom of the page
            for ($i = $TextLines; $i <= $PageLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the footer
            $PageNumber++;
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                          "<br>");
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintInventoryQtyLow(SortParameter, SortName)
    //
    // Purpose:  Print the inventory sorted by the key specified in
    //           SortParameter for the items that are low in stock
    //
    // Inputs:
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintInventoryQtyLow($SortParameter, $SortName)
    {
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        
        include "DatabaseConstants.inc";
        
        $MaxLineLength = 89;
        $LeftMargin = 2;
        $PageLength = 60;
        $HeaderLength = 1;
        $PartNumberLength = 16;
        $DescriptionLength = 22;
        $UnitPriceLength = 9;
        $RetailPriceLength = 9;
        $RemainingLength = 9;
        $LowLevelLength = 9;
        $PositionLength = 10;
    
        // printer setup
        $PageHeader = "************ " . $AircraftScheduling_company . " INVENTORY ************";
        PrinterSetup(9);
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Low Stock Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
        
        // print the column header
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                    JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                    JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                    JustifyField("Retail", $CenterJustify, $RetailPriceLength) . " " .
                    JustifyField("Remain", $CenterJustify, $RemainingLength) . " " .
                    JustifyField("Low Level", $CenterJustify, $LowLevelLength) . " " .
                    JustifyField("Position", $CenterJustify, $PositionLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(" " . "<br>");
        
        // create a query to get all the inventory items
        $InventoryResult = SQLOpenRecordset(
            "SELECT * FROM Inventory WHERE (Inventory.Quantity_In_Stock < Inventory.Reorder_Quantity) " .
                    "ORDER BY " . $SortParameter);
        
        // loop through all the inventory items and print the items
        $TextLines = 0;
        $PageNumber = 0;
        for($InventoryCnt=0; $InventoryRST = sql_row($InventoryResult, $InventoryCnt); $InventoryCnt++)  
        {
            // if we have filled the page, send the page to the printer
            if ($TextLines > $PageLength) 
            {
                // print the footer
                $PageNumber++;
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                              "<br>");
                
                // at max page length, print the page
                PrintNewPage();
                $TextLines = 0;
        
                // skip some space at the top of the form
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                             JustifyField("Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
                             "<br>");
               
                // skip some space below the header
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                }
        
                // print the column header
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                            JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                            JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                            JustifyField("Retail", $CenterJustify, $RetailPriceLength) . " " .
                            JustifyField("Remain", $CenterJustify, $RemainingLength) . " " .
                            JustifyField("Low Level", $CenterJustify, $LowLevelLength) . " " .
                            JustifyField("Position", $CenterJustify, $PositionLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the item record
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField($InventoryRST[$InventoryPart_Number_offset], $LeftJustify, $PartNumberLength) . " " .
                        JustifyField($InventoryRST[$InventoryDescription_offset], $LeftJustify, $DescriptionLength) . " " .
                        JustifyField(FormatField($InventoryRST[$InventoryUnit_Price_offset], "Currency"), $RightJustify, $UnitPriceLength) . " " .
                        JustifyField(FormatField($InventoryRST[$InventoryRetail_Price_offset], "Currency"), $RightJustify, $RetailPriceLength) . " " .
                        JustifyField($InventoryRST[$InventoryQuantity_In_Stock_offset], $RightJustify, $RemainingLength) . " " .
                        JustifyField($InventoryRST[$InventoryReorder_Quantity_offset], $RightJustify, $LowLevelLength) . " " .
                        JustifyField($InventoryRST[$InventoryPosition_offset], $RightJustify, $PositionLength) . "<br>");
            $TextLines++;
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the last page (if needed)
        if ($TextLines > 0) 
        {
            // skip to the bottom of the page
            for ($i = $TextLines; $i <= $PageLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the footer
            $PageNumber++;
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                          "<br>");
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintWholesaleInventory(SortParameter, SortName)
    //
    // Purpose:  Print the wholesale inventory
    //
    // Inputs:
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintWholesaleInventory($SortParameter, $SortName)
    {
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $WholesaleInventory, $RetailInventory, $MaintenanceInventory;
        
        include "DatabaseConstants.inc";
        
        $MaxLineLength = 97;
        $LeftMargin = 2;
        $PageLength = 60;
        $HeaderLength = 1;
        $PartNumberLength = 16;
        $DescriptionLength = 32;
        $UnitPriceLength = 12;
        $AmountLength = 12;
        $TotalLength = 12;
        $PositionLength = 8;
        $PageHeader = $AircraftScheduling_company . " WHOLESALE INVENTORY SORTED BY " . $SortName;
        
        $TextLines = 0;
    
        // printer setup
        PrinterSetup(9);
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Current as of " . FormatField("now", "Date"), $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Prepared by:___________________________________", $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Called by:_____________________________________", $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Checked by:____________________________________", $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the column header
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                    JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                    JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                    JustifyField("Amount", $CenterJustify, $AmountLength) . " " .
                    JustifyField("Total", $CenterJustify, $TotalLength) . " " .
                    JustifyField("Position", $CenterJustify, $PositionLength) . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        
        // create a query to get all the inventory items
        $InventoryResult = SQLOpenRecordset(
            "SELECT * FROM Inventory ORDER BY " . $SortParameter);
        
        // loop through all the inventory items and print the items
        $TotalAmount = 0;
        $PageNumber = 0;
        $PageTotal = 0;
        for($InventoryCnt=0; $InventoryRST = sql_row($InventoryResult, $InventoryCnt); $InventoryCnt++)  
        {
            // if we have filled the page, send the page to the printer
            if ($TextLines > $PageLength) 
            {
                // print the totals for this page
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("Totals This Page", $LeftJustify, $PartNumberLength) . " " .
                            JustifyField(" ", $LeftJustify, $DescriptionLength) . " " .
                            JustifyField(" ", $RightJustify, $UnitPriceLength) . " " .
                            JustifyField(" ", $RightJustify, $AmountLength) . " " .
                            JustifyField(FormatField(Str($PageTotal), "Currency"), $RightJustify, $TotalLength) . 
                            "<br>");
                $TextLines++;
                
                // print the footer
                $PageNumber++;
                PrintNonBreakingString(" <br>");
                PrintNonBreakingString(" <br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                              "<br>");
                
                // at max page length, print the page
                PrintNewPage();
                $TextLines = 0;
                $PageTotal = 0;
        
                // skip some space at the top of the form
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                    $TextLines++;
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" <br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Current as of " . FormatField("now", "Date"), $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" <br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Prepared by:___________________________________", $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Called by:_____________________________________", $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Checked by:____________________________________", $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                
                // skip some space below the header
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                    $TextLines++;
                }
                
                // print the column header
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                            JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                            JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                            JustifyField("Amount", $CenterJustify, $AmountLength) . " " .
                            JustifyField("Total", $CenterJustify, $TotalLength) . " " .
                            JustifyField("Position", $CenterJustify, $PositionLength) . "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                
            }
            
            // print the item record if it is a wholesale item
            if ($InventoryRST[$InventoryInventory_Type_offset] == $WholesaleInventory) 
            {
                $ItemAmount = $InventoryRST[$InventoryUnit_Price_offset] * $InventoryRST[$InventoryQuantity_In_Stock_offset];
                $TotalAmount = $TotalAmount + $ItemAmount;
                $PageTotal = $PageTotal + $ItemAmount;
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField($InventoryRST[$InventoryPart_Number_offset], $LeftJustify, $PartNumberLength) . " " .
                            JustifyField($InventoryRST[$InventoryDescription_offset], $LeftJustify, $DescriptionLength) . " " .
                            JustifyField(FormatField($InventoryRST[$InventoryUnit_Price_offset], "Currency"), $RightJustify, $UnitPriceLength) . " " .
                            JustifyField($InventoryRST[$InventoryQuantity_In_Stock_offset], $RightJustify, $AmountLength) . " " .
                            JustifyField(FormatField(Str($ItemAmount), "Currency"), $RightJustify, $TotalLength) . " " .
                            JustifyField($InventoryRST[$InventoryPosition_offset], $RightJustify, $PositionLength) . "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
            }
        }
        
        // print the last page (if needed)
        if ($TextLines > 0) 
        {
            // print the totals for this page
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
            $TextLines++;
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("Totals This Page", $LeftJustify, $PartNumberLength) . " " .
                        JustifyField(" ", $LeftJustify, $DescriptionLength) . " " .
                        JustifyField(" ", $RightJustify, $UnitPriceLength) . " " .
                        JustifyField(" ", $RightJustify, $AmountLength) . " " .
                        JustifyField(FormatField(Str($PageTotal), "Currency"), $RightJustify, $TotalLength) .
                        "<br>");
            $TextLines++;
            
            // print the grand total
            PrintNonBreakingString(Space($LeftMargin) .
                          " " . "<br>");
            $TextLines++;
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("Grand Total", $LeftJustify, $PartNumberLength) . " " .
                        JustifyField(" ", $LeftJustify, $DescriptionLength) . " " .
                        JustifyField(" ", $RightJustify, $UnitPriceLength) . " " .
                        JustifyField(" ", $RightJustify, $AmountLength) . " " .
                        JustifyField(FormatField(Str($TotalAmount), "Currency"), $RightJustify, $TotalLength) . 
                        "<br>");
            $TextLines++;
            
            // skip to the bottom of the page
            for ($i = $TextLines; $i <= $PageLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the footer
            $PageNumber++;
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                          "<br>");
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintRetailInventory(SortParameter, SortName)
    //
    // Purpose:  Print the retail inventory
    //
    // Inputs:
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintRetailInventory($SortParameter, $SortName)
    {
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        
        include "DatabaseConstants.inc";
        
        $MaxLineLength = 89;
        $LeftMargin = 2;
        $PageLength = 60;
        $HeaderLength = 1;
        $PartNumberLength = 16;
        $DescriptionLength = 32;
        $UnitPriceLength = 12;
        $AmountLength = 12;
        $PositionLength = 8;
        
        $TextLines = 0;
    
        // printer setup
        $PageHeader = $AircraftScheduling_company . " PRICE LIST SORTED BY " . $SortName;
        PrinterSetup(9);
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" <br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Current as of " . FormatField("now", "Date"), $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the column header
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                    JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                    JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                    JustifyField("Quantity", $CenterJustify, $AmountLength) . " " .
                    JustifyField("Position", $CenterJustify, $PositionLength) . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        
        // create a query to get all the inventory items
        $InventoryResult = SQLOpenRecordset(
            "SELECT * FROM Inventory ORDER BY " . $SortParameter);
        
        // loop through all the inventory items and print the items
        $PageNumber = 0;
        for($InventoryCnt=0; $InventoryRST = sql_row($InventoryResult, $InventoryCnt); $InventoryCnt++)  
        {
            // if we have filled the page, send the page to the printer
            if ($TextLines > $PageLength) 
            {
                // print the footer
                $PageNumber++;
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                              "<br>");
                
                // at max page length, print the page
                PrintNewPage();
                $TextLines = 0;
        
                // skip some space at the top of the form
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                    $TextLines++;
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" <br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Current as of " . FormatField("now", "Date"), $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                
                // skip some space below the header
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                    $TextLines++;
                }
                
                // print the column header
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                            JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                            JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                            JustifyField("Quantity", $CenterJustify, $AmountLength) . " " .
                            JustifyField("Position", $CenterJustify, $PositionLength) . "<br>");
                            
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;                
            }
            
            // print the item record
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField($InventoryRST[$InventoryPart_Number_offset], $LeftJustify, $PartNumberLength) . " " .
                        JustifyField($InventoryRST[$InventoryDescription_offset], $LeftJustify, $DescriptionLength) . " " .
                        JustifyField(FormatField($InventoryRST[$InventoryRetail_Price_offset], "Currency"), $RightJustify, $UnitPriceLength) . " " .
                        JustifyField($InventoryRST[$InventoryQuantity_In_Stock_offset], $RightJustify, $AmountLength) . " " .
                        JustifyField($InventoryRST[$InventoryPosition_offset], $RightJustify, $PositionLength) . "<br>");
            $TextLines++;
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the last page (if needed)
        if ($TextLines > 0) 
        {
            // skip to the bottom of the page
            for ($i = $TextLines; $i < $PageLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the footer
            $PageNumber++;
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                          "<br>");
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintMaintenanceInventory(SortParameter, SortName)
    //
    // Purpose:  Print the maintenace inventory
    //
    // Inputs:
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMaintenanceInventory($SortParameter, $SortName)
    {
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $WholesaleInventory, $RetailInventory, $MaintenanceInventory;
        
        include "DatabaseConstants.inc";
        
        $MaxLineLength = 97;
        $LeftMargin = 2;
        $PageLength = 60;
        $HeaderLength = 1;
        $PartNumberLength = 16;
        $DescriptionLength = 32;
        $UnitPriceLength = 12;
        $AmountLength = 12;
        $TotalLength = 12;
        $PositionLength = 8;
        
        $TextLines = 0;
    
        // printer setup
        $PageHeader = $AircraftScheduling_company . " MAINTENANCE INVENTORY SORTED BY " . $SortName;
        PrinterSetup(9);
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Current as of " . FormatField("now", "Date"), $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Prepared by:___________________________________", $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Called by:_____________________________________", $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" <br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Checked by:____________________________________", $LeftJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the column header
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                    JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                    JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                    JustifyField("Amount", $CenterJustify, $AmountLength) . " " .
                    JustifyField("Total", $CenterJustify, $TotalLength) . " " .
                    JustifyField("Position", $CenterJustify, $PositionLength) . 
                    "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        PrintNonBreakingString(" " . "<br>");
        $TextLines++;
        
        // create a query to get all the inventory items
        $InventoryResult = SQLOpenRecordset(
            "SELECT * FROM Inventory ORDER BY " . $SortParameter);
        
        // loop through all the inventory items and print the items
        $TotalAmount = 0;
        $PageNumber = 0;
        $PageTotal = 0;
        for($InventoryCnt=0; $InventoryRST = sql_row($InventoryResult, $InventoryCnt); $InventoryCnt++)  
        {
            // if we have filled the page, send the page to the printer
            if ($TextLines > $PageLength) 
            {
                // print the totals for this page
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("Totals This Page", $LeftJustify, $PartNumberLength) . " " .
                            JustifyField(" ", $LeftJustify, $DescriptionLength) . " " .
                            JustifyField(" ", $RightJustify, $UnitPriceLength) . " " .
                            JustifyField(" ", $RightJustify, $AmountLength) . " " .
                            JustifyField(FormatField(Str($PageTotal), "Currency"), $RightJustify, $TotalLength) . 
                            "<br>");
                $TextLines++;
                
                // print the footer
                $PageNumber++;
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                              "<br>");
                
                // at max page length, print the page
                PrintNewPage();
                $TextLines = 0;
                $PageTotal = 0;
        
                // skip some space at the top of the form
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                    $TextLines++;
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Current as of " . FormatField("now", "Date"), $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Prepared by:___________________________________", $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Called by:_____________________________________", $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Checked by:____________________________________", $LeftJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                
                // skip some space below the header
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                    $TextLines++;
                }
                
                // print the column header
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("Part No", $CenterJustify, $PartNumberLength) . " " .
                            JustifyField("Description", $CenterJustify, $DescriptionLength) . " " .
                            JustifyField("Price", $CenterJustify, $UnitPriceLength) . " " .
                            JustifyField("Amount", $CenterJustify, $AmountLength) . " " .
                            JustifyField("Total", $CenterJustify, $TotalLength) . " " .
                            JustifyField("Position", $CenterJustify, $PositionLength) . 
                            "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
                
            }
            
            // print the item record if it is a maintenance item
            if ($InventoryRST[$InventoryInventory_Type_offset] == $MaintenanceInventory) 
            {
                $ItemAmount = $InventoryRST[$InventoryUnit_Price_offset] * $InventoryRST[$InventoryQuantity_In_Stock_offset];
                $TotalAmount = $TotalAmount + $ItemAmount;
                $PageTotal = $PageTotal + $ItemAmount;
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField($InventoryRST[$InventoryPart_Number_offset], $LeftJustify, $PartNumberLength) . " " .
                            JustifyField($InventoryRST[$InventoryDescription_offset], $LeftJustify, $DescriptionLength) . " " .
                            JustifyField(FormatField($InventoryRST[$InventoryUnit_Price_offset], "Currency"), $RightJustify, $UnitPriceLength) . " " .
                            JustifyField($InventoryRST[$InventoryQuantity_In_Stock_offset], $RightJustify, $AmountLength) . " " .
                            JustifyField(FormatField(Str($ItemAmount), "Currency"), $RightJustify, $TotalLength) . " " .
                            JustifyField($InventoryRST[$InventoryPosition_offset], $RightJustify, $PositionLength) . 
                            "<br>");
                $TextLines++;
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
            }
        }
        
        // print the last page (if needed)
        if ($TextLines > 0) 
        {
            // print the totals for this page
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . 
                          "<br>");
            $TextLines++;
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("Totals This Page", $LeftJustify, $PartNumberLength) . " " .
                        JustifyField(" ", $LeftJustify, $DescriptionLength) . " " .
                        JustifyField(" ", $RightJustify, $UnitPriceLength) . " " .
                        JustifyField(" ", $RightJustify, $AmountLength) . " " .
                        JustifyField(FormatField(Str($PageTotal), "Currency"), $RightJustify, $TotalLength) . 
                        "<br>");
            $TextLines++;
            
            // print the grand total
            PrintNonBreakingString(Space($LeftMargin) .
                          " " . "<br>");
            $TextLines++;
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("Grand Total", $LeftJustify, $PartNumberLength) . " " .
                        JustifyField(" ", $LeftJustify, $DescriptionLength) . " " .
                        JustifyField(" ", $RightJustify, $UnitPriceLength) . " " .
                        JustifyField(" ", $RightJustify, $AmountLength) . " " .
                        JustifyField(FormatField(Str($TotalAmount), "Currency"), $RightJustify, $TotalLength) . 
                        "<br>");
            $TextLines++;
            
            // skip to the bottom of the page
            for ($i = $TextLines; $i <= $PageLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the footer
            $PageNumber++;
            PrintNonBreakingString(" <br>");
            PrintNonBreakingString(" <br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                          "<br>");
            PrintNewPage();
        }
    }

    //********************************************************************
    // PrintSelectedInventory()
    //
    // Purpose:  Print the selected inventory.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSelectedInventory()
    {        
        global $PrintInventoryOption;
        global $PrintInventoryQtyLowOptions;
        global $PrintWholesaleInventoryOptions;
        global $PrintRetailInventoryOptions;
        global $PrintMaintenamceInventoryOptions;
        global $SortSelection;
        
        // build the sort name
        $SortName = Replace($SortSelection, "_", " ");

        // should we print the inventory?
    	if ($PrintInventoryOption == 1) 
    	{
            PrintInventory($SortSelection, $SortName);
    	}
         
        // should we print the quantity low inventory items?
    	if ($PrintInventoryQtyLowOptions == 1) 
    	{
            PrintInventoryQtyLow($SortSelection, $SortName);
    	}
         
        // should we print the wholesale inventory
    	if ($PrintWholesaleInventoryOptions == 1) 
    	{
            PrintWholesaleInventory($SortSelection, $SortName);
    	}
         
        // should we print the retail inventory
    	if ($PrintRetailInventoryOptions == 1) 
    	{
            PrintRetailInventory($SortSelection, $SortName);
    	}
         
        // should we print the maintenance inventory
    	if ($PrintMaintenamceInventoryOptions == 1) 
    	{
            PrintMaintenanceInventory($SortSelection, $SortName);
    	}
    }
    
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
    #if we dont know the right date then make it up 
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
    
    // are we authorized to perform this function?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }

    // this script will call itself whenever the submit or cancel button is pressed
    // we will check here for the checkout and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $PrintInventoryInformation == "Submit") 
    {
        // submit button was selected

        // updates to the charge are complete, take them back to the last screen
        // after the confirmation sheet is printed
        if(isset($goback))
        {
            // goback is set, take them back there
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                $ReturnURL = $goback . CleanGoBackParameters($GoBackParameters);
            else
                // goback parameters not set, use the default
        	    $ReturnURL = $goback . "?" .
        	                "day=$day&month=$month&year=$year" .
        	                "&resource=$resource" .
        	                "&resource_id=$resource_id" .
        	                "&InstructorResource=$InstructorResource" .
        	                "$makemodel";
        }
        else
        {
            // goback is not set, use the default
        	$ReturnURL = "index.php?" .
                        "day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" .
                        "$makemodel";
        }
        
        // include the print functions here so that the javascript won't
        // interfer with the header functions
        require_once("PrintFunctions.inc");
        
        // setup the print functions
        SetupPrintFunctions($ReturnURL);
        
        // print the requested inventory
        PrintSelectedInventory();
        
        // finish the print form
        CompletePrintFunctions();
                    
        // finished with this part of the script
        exit;
    }
    else if(count($_POST) > 0 && $AircraftCancel == "Cancel") 
    {
        // user canceled the Submit, take them back to the last screen
        if(isset($goback))
        {
            // goback is set, take them back there
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                header("Location: $goback" . CleanGoBackParameters($GoBackParameters));
            else
                // goback parameters not set, use the default
        	    header("Location: " . $goback . "?" .
        	                "day=$day&month=$month&year=$year" .
        	                "&resource=$resource" .
        	                "&resource_id=$resource_id" .
        	                "&InstructorResource=$InstructorResource" .
        	                "$makemodel");
        }
        else
        {
            // goback is not set, use the default
        	Header("Location: index.php?" .
                        "day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" .
                        "$makemodel");
        }
		exit();
    }

    // neither submit or cancel were selected display the main screen
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='PrintInventoryInformation.php' METHOD='POST'>";
	
	// set the default print selections
    $PrintInventoryOption = 1;
    $PrintInventoryQtyLowOptions = 0;
    $PrintWholesaleInventoryOptions = 0;
    $PrintRetailInventoryOptions = 0;
    $PrintMaintenamceInventoryOptions = 0;

    // start the table to display the report information
    echo "<center>";
    echo "<h2>Print Inventory Selection</h2>";
    echo "<table border=0>";
    
    // Sort Inventory Reports By: 
    echo "<tr>";
    echo "<td class=CC colspan=2>Sort Inventory Reports By:&nbsp;";
    BuildInventorySelector($SortSelection);
    echo "</td>";
    echo "</tr>";
    
    // skip some space
    echo "<tr>";
    echo "<td>&nbsp;";
    echo "</td>";
    echo "</tr>";
     
    // Print Inventory
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintInventoryOption value=1 ";
	if ($PrintInventoryOption == 1) echo "checked";
	echo ">Print Inventory";
    echo "</td>";
    echo "</tr>";
     
    // Print Low Quantity Items
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintInventoryQtyLowOptions value=1 ";
	if ($PrintInventoryQtyLowOptions == 1) echo "checked";
	echo ">Print Low Quantity Items";
    echo "</td>";
    echo "</tr>";
     
    // Print Wholesale Inventory
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintWholesaleInventoryOptions value=1 ";
	if ($PrintWholesaleInventoryOptions == 1) echo "checked";
	echo ">Print Wholesale Inventory";
    echo "</td>";
    echo "</tr>";
     
    // Print Retail Inventory
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintRetailInventoryOptions value=1 ";
	if ($PrintRetailInventoryOptions == 1) echo "checked";
	echo ">Print Retail Inventory";
    echo "</td>";
    echo "</tr>";
     
    // Print Maintenance Inventory
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintMaintenamceInventoryOptions value=1 ";
	if ($PrintMaintenamceInventoryOptions == 1) echo "checked";
	echo ">Print Maintenance Inventory";
    echo "</td>";
    echo "</tr>";

    // finished with the table
    echo "</table>";
            
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>\n";
   
    // generate the update and cancel buttons
    echo "<TABLE>";
    echo "<TR>";
    echo "<TD><input name='PrintInventoryInformation' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
