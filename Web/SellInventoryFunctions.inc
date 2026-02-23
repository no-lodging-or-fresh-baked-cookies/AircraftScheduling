<?php
//-----------------------------------------------------------------------------
// 
// SellInventoryFunctions.inc
// 
// PURPOSE: Provides the functions for selling inventory items.
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
require_once("DatabaseFunctions.inc");

//********************************************************************
// LoadInventoryPartNumbers(PartNumber, $RowNumber)
//
// Purpose:  Load the inventory items into the part number control.
//
// Inputs:
//   PartNumber - currently selected inventory item
//   RowNumber - number of the row we are generating
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function LoadInventoryPartNumbers($PartNumber, $RowNumber)
{
    // build the select HTML	
	echo "<SELECT NAME='PartNumber$RowNumber' id='PartNumber$RowNumber' " . 
	        "Onchange='" .
	          "UpdateInventoryControl(\"PartNumber\", $RowNumber)" . 
	          "'>";
    
    // put "None" as a choice
	echo "<OPTION " .
			"VALUE='" . "None" . "'" . 
			("None" == $PartNumber ? " SELECTED" : "") . 
			">None";
    
    // load the part numbers into the combo box
    LoadPartNumbers($PartNumber, 25);

	// finished with the select
	echo "</SELECT>";	
}

//********************************************************************
// LoadInventoryDescriptions(PartDescription, $RowNumber)
//
// Purpose:  Load the inventory items into the descriptions control.
//
// Inputs:
//   Description - currently selected inventory item
//   RowNumber - number of the row we are generating
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function LoadInventoryDescriptions($PartDescription, $RowNumber)
{
    // build the select HTML	
	echo "<SELECT NAME='PartDescription$RowNumber' id='PartDescription$RowNumber' " . 
	        "Onchange='" .
	          "UpdateInventoryControl(\"PartDescription\", $RowNumber)" . 
	          "'>";
    
    // put "None" as a choice
	echo "<OPTION " .
			"VALUE='" . "None" . "'" . 
			("None" == $PartDescription ? " SELECTED" : "") . 
			">None";
    
    // load the part numbers into the combo box
    LoadDescriptions($PartDescription, 25);

	// finished with the select
	echo "</SELECT>";	
}

//********************************************************************
// BuildSellItemTable($TotalRows)
//
// Purpose:  Build the table to allow the user to select the items 
//           to sell.
//
// Inputs:
//   TotalRows - total number of rows to generate
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildSellItemTable($TotalRows)
{
    global $PartNumber;
    global $CurrencyPrefix;
	global $PartDescription;
	global $Quantity;
	global $Price;
	
    // set the column sizes
    $Column1Width = "30%";
    $Column2Width = "40%";
    $Column3Width = "10%";
    $Column4Width = "10%";
    $Column5Width = "10%";
    
    // start the table
    echo "<table id='SellInventory' border=1>";
    
    // display the headers
    //echo "<tr>";
    echo "<th width=$Column1Width>Part Number</td>";
    echo "<th width=$Column2Width>Description</td>";
    echo "<th width=$Column3Width>Quantity<br>Purchased</td>";
    echo "<th width=$Column4Width>Retail Price</td>";
    echo "<th width=$Column5Width>Total Price</td>";
    echo "</tr>";
    
    // generate the requested rows
    for ($i = 0; $i < $TotalRows; $i++)
    {
        // display the row
        echo "<tr>";
        
        // part number
        echo "<td width=$Column1Width>";
        LoadInventoryPartNumbers($PartNumber, $i);
        echo "</td>";
        
        // description
        echo "<td width=$Column2Width>";
        LoadInventoryDescriptions($PartDescription, $i);
        echo "</td>";
        
        // quantity purchased
        echo "<td width=$Column3Width>";
        echo "<INPUT TYPE=TEXT NAME='Quantity$i' ID='Quantity$i' ALIGN=RIGHT SIZE=9 VALUE='$Quantity'" .
                "OnChange='FormatControl($i)'" .
                ">";
        echo "</td>";
        
        // retail price
        echo "<td width=$Column4Width>";
        echo "<INPUT TYPE=TEXT NAME='Price$i' ID='Price$i' ALIGN=RIGHT SIZE=9 VALUE='" . 
                FormatField($Price, "Currency") . "' " .
                "OnChange='FormatControl($i)'" .
                ">";
        echo "</td>";
        
        // total price
        echo "<td width=$Column5Width align=right>";
        echo FormatField(0.00, "Currency");
        echo "</td>";
        
        // end the row
        echo "</tr>";
    }
    
    // total line
    echo "<tr>";
    echo "<td colspan=4 align=right>Totals</td>";
    echo "<td width=$Column5Width align=right>" . 
                FormatField(0.00, "Currency") . "</td>";
    echo "</tr>";
    
    // end the table
    echo "</table>";
}
    
//********************************************************************
// SetControlVariables()
//
// Purpose: Update the control values from the input values.
//          Since the control values may change based on the the number
//          of items sold, we will process the fields from the raw input.
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
function SetControlVariables()
{
    global $NumberItemsSold;
    global $PartNumberArray;
    global $PartDescriptionArray;
    global $QuantityArray;
    global $PriceArray;
    
    global $rdata;
    global $MaxSellItems;
    
    // initialize the variables
    $NumberItemsSold = 0;
    $PartNumberArray = array();
    $PartDescriptionArray = array();
    $QuantityArray = array();
    $PriceArray = array();
    
    // look for the control variables in the raw input
    for ($i = 0; $i < $MaxSellItems; $i++)
    {
        // if the variable is set, copy it to the arrays
        if(isset($rdata["PartNumber$i"]))
        {
            // variables are set, make sure it is a valid part number
            if (UCase($rdata["PartNumber$i"]) != "NONE")
            {
                // valid part number, copy the input variables
                $PartNumberArray[$NumberItemsSold] = $rdata["PartNumber$i"];
                $PartDescriptionArray[$NumberItemsSold] = $rdata["PartDescription$i"];
                $QuantityArray[$NumberItemsSold] = $rdata["Quantity$i"];
                $PriceArray[$NumberItemsSold] = GetNumber($rdata["Price$i"]);
                $NumberItemsSold++;
            }
        }
    }
}

//********************************************************************
// PrintConfirmation($NameOfUser)
//
// Purpose:  Print a confirmation sheet for the item sold
//
// Inputs:
//   NameOfUser - first and last name of the user to sell the item to.
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrintConfirmation($NameOfUser)
{
    global $RightJustify, $LeftJustify, $CenterJustify;
    global $AircraftScheduling_company;
    global $NumberItemsSold;
    global $PartNumberArray;
    global $PartDescriptionArray;
    global $QuantityArray;
    global $PriceArray;

    $MaxLineLength = 85;
    $LeftMargin = 2;
    $PageLength = 60;
    $HeaderLength = 1;
    $PartNumberLength = 14;
    $DescriptionLength = 27;
    $AmountLength = 6;
    $PriceLength = 6;
    $TotalLength = 10;
	$LineCounter = 0;
    
    // build the page header
    $PageHeader = "************ " . $AircraftScheduling_company . " Sales ************";

    // printer setup
    PrinterSetup(11);
    
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
    $LineCounter++;
    PrintNonBreakingString(" " . "<br>");
    $LineCounter++;
    PrintNonBreakingString(Space($LeftMargin) .
                  JustifyField("DATE: " . strftime("%d %b %y") . " " . strftime("%H:%M"), $CenterJustify, $MaxLineLength)
                  . "<br>");
    $LineCounter++;
    
    // skip some space below the header
    for ($i = 0; $i < $HeaderLength; $i++)
    {
        PrintNonBreakingString(" " . "<br>");
        $LineCounter++;
    }
    
    // print the keycode for the user
    PrintNonBreakingString(Space($LeftMargin) .
                            "USERNAME: " . " " . GetUsernameFromName($NameOfUser) . "<br>");
    $LineCounter++;
    PrintNonBreakingString(" " . "<br>");
    $LineCounter++;
                
    // print the name
    PrintNonBreakingString(Space($LeftMargin) . $NameOfUser . "<br>");
    $LineCounter++;
    PrintNonBreakingString(" " . "<br>");
    $LineCounter++;
                
    // print the column header
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("PART NO", $CenterJustify, $PartNumberLength) . " " .
                JustifyField("DESCRIPTION", $CenterJustify, $DescriptionLength) . " " .
                JustifyField("AMOUNT", $CenterJustify, $AmountLength) . "    " .
                JustifyField("PRICE", $CenterJustify, $PriceLength) . "    " .
                JustifyField("TOTAL COST", $CenterJustify, $TotalLength) . "<br>");
    $LineCounter++;
                
    // print the items
    $TotalCost = 0;
    for ($EntryNumber = 0; $EntryNumber < $NumberItemsSold; $EntryNumber++)
    {
        if ($LineCounter > $PageLength)
        {
            // at max page length, print the page
            PrintNewPage();
            $LineCounter = 0;
    
            // skip some space at the top of the form
            for ($i = 0; $i < $HeaderLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the page header
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField($PageHeader, $CenterJustify, $MaxLineLength)
                        . "<br>");
            $LineCounter++;
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("DATE: " . strftime("%d %b %y") . " " . strftime("%H:%M"), $CenterJustify, $MaxLineLength)
                          . "<br>");
            $LineCounter++;
            
            // skip some space below the header
            for ($i = 0; $i < $HeaderLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
                $LineCounter++;
            }
            
            // print the keycode for the user
            PrintNonBreakingString(Space($LeftMargin) .
                                    "USERNAME: " . " " . GetUsernameFromName($NameOfUser) . " (continued)"
                                    . "<br>");
            $LineCounter++;
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
                        
            // print the name
            PrintNonBreakingString(Space($LeftMargin) . $NameOfUser . "<br>");
            $LineCounter++;
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
                        
            // print the column header
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("PART NO", $CenterJustify, $PartNumberLength) . " " .
                        JustifyField("DESCRIPTION", $CenterJustify, $DescriptionLength) . " " .
                        JustifyField("AMOUNT", $CenterJustify, $AmountLength) . "    " .
                        JustifyField("PRICE", $CenterJustify, $PriceLength) . "    " .
                        JustifyField("TOTAL COST", $CenterJustify, $TotalLength)
                        . "<br>");
            $LineCounter++;
        }
        $TotalCost = $TotalCost + $QuantityArray[$EntryNumber] * $PriceArray[$EntryNumber];
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField($PartNumberArray[$EntryNumber], $LeftJustify, $PartNumberLength) . " " .
                    JustifyField($PartDescriptionArray[$EntryNumber], $LeftJustify, $DescriptionLength) . " " .
                    JustifyField(Str($QuantityArray[$EntryNumber]), $RightJustify, $AmountLength) . "    " .
                    JustifyField(FormatField(Str($PriceArray[$EntryNumber]), "Currency"), $RightJustify, $PriceLength) . "    " .
                    JustifyField(FormatField(
                                $QuantityArray[$EntryNumber] * $PriceArray[$EntryNumber],
                                "Currency"), $RightJustify, $TotalLength)
                    . "<br>");                                    
        $LineCounter++;
    }
    PrintNonBreakingString(" " . "<br>");
    $LineCounter++;
           
    // print the balance line
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("BALANCE", $LeftJustify, $PartNumberLength) . " " .
                JustifyField(" ", $LeftJustify, $DescriptionLength) . " " .
                JustifyField(" ", $RightJustify, $AmountLength) . "    " .
                JustifyField(" ", $RightJustify, $PriceLength) . "    " .
                JustifyField(FormatField(Val($TotalCost), "Currency"), $RightJustify, $TotalLength)
                . "<br>");
    $LineCounter++;
    PrintNonBreakingString(" " . "<br>");
    $LineCounter++;
    PrintNonBreakingString(" " . "<br>");
    $LineCounter++;

    // print the information lines
    PrintNonBreakingString(Space($LeftMargin) .
                "THIS WILL BE CHARGED TO YOUR ACCOUNT" . "<br>");
    $LineCounter++;
    PrintNonBreakingString(Space($LeftMargin) .
                "THANK YOU" . "<br>");
    $LineCounter++;
}

//********************************************************************
// SaveChargeInformation(NameOfUser)
//
// Purpose:  Save a charge for the given user.
//
// Inputs:
//   NameOfUser - first and last name of the person to sell the item to
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function SaveChargeInformation($NameOfUser)
{
    global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
    global $NumberItemsSold;
    global $PartNumberArray;
    global $PartDescriptionArray;
    global $QuantityArray;
    global $PriceArray;

    // go through all the items we are selling and update the database information
    for ($EntryNumber = 0; $EntryNumber < $NumberItemsSold; $EntryNumber++)
    {
        // decrement the quantity in stock field, don't let it go less than zero
        $DatabaseFields = array();
        $CurrentQuantity = sql_query1(
                                        "SELECT Quantity_In_Stock " .
                                        "FROM Inventory " .
                                        "WHERE Part_Number='$PartNumberArray[$EntryNumber]'");
        $QuantityInStock = $CurrentQuantity - $QuantityArray[$EntryNumber];
        if ($QuantityInStock < 0) $QuantityInStock = 0;
        SetDatabaseRecord("Quantity_In_Stock", $QuantityInStock, $RSConversionNumber, $DatabaseFields[0]);
        
        // save the changes to the inventory record
        UpdateDatabaseRecord(
                            "Inventory",
                            $DatabaseFields,
                            "Part_Number='$PartNumberArray[$EntryNumber]'");
        // get the part number information from the database
        $Unit_Price = sql_query1(
                            "SELECT Unit_Price " .
                            "FROM Inventory " .
                            "WHERE Part_Number='$PartNumberArray[$EntryNumber]'");
        $Category = sql_query1(
                            "SELECT Category " .
                            "FROM Inventory " .
                            "WHERE Part_Number='$PartNumberArray[$EntryNumber]'");

        // save the charge in the charges field
        $DatabaseFields = array();
        SetDatabaseRecord("Keycode",
                           GetUsernameFromName($NameOfUser), $RSConversionString, $DatabaseFields[0]);
        SetDatabaseRecord("Date",
                                FormatField("now", "DatabaseDate"),
                                $RSConversionDate, $DatabaseFields[1]);
        SetDatabaseRecord("Part_Number",
                                    $PartNumberArray[$EntryNumber],
                                    $RSConversionString, $DatabaseFields[2]);
        SetDatabaseRecord("Part_Description",
                                    $PartDescriptionArray[$EntryNumber], $RSConversionString, $DatabaseFields[3]);
        SetDatabaseRecord("Quantity",
                                    $QuantityArray[$EntryNumber], $RSConversionNumber, $DatabaseFields[4]);
        SetDatabaseRecord("Price",
                                    $PriceArray[$EntryNumber], $RSConversionNumber, $DatabaseFields[5]);
        SetDatabaseRecord("Unit_Price",
                                    $Unit_Price, $RSConversionNumber, $DatabaseFields[6]);
        SetDatabaseRecord("Total_Price",
                                    $PriceArray[$EntryNumber] *
                                    $QuantityArray[$EntryNumber], $RSConversionNumber, $DatabaseFields[7]);
        SetDatabaseRecord("Category",
                                     $Category, $RSConversionString, $DatabaseFields[8]);
                
        // save the changes
        AddDatabaseRecord("Charges", $DatabaseFields);
        
        // log the change in the journal
    	$Description = 
                        "Sold " . Str($QuantityArray[$EntryNumber]) .
                        " of part number " . $PartNumberArray[$EntryNumber] .
                        ", total price " . FormatField(Str($PriceArray[$EntryNumber] *
                                $QuantityArray[$EntryNumber]), "Currency") .
                        " to " . GetUsernameFromName($NameOfUser) .
                        " (" . $NameOfUser . ")";
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    }
}
    
// save the global variables for the javascript code
echo "<SCRIPT LANGUAGE=\"JavaScript\">";
echo "var CurrencyPrefix = '$CurrencyPrefix';";
echo "</SCRIPT>";

?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// FormatControl(RowNumber)
//
// Purpose: Format the fields when a control is updated.
//
// Inputs:
//   RowNumber - the row number within the table that we are updating
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function FormatControl(RowNumber)
{
    var TotalPriceCol = 4;      // column number for total price fields

    // quantity purchased
    QuantityPurchased = parseFloat(document.getElementById("Quantity" + RowNumber).value);
    document.getElementById("Quantity" + RowNumber).value = format(QuantityPurchased, 0);
    
    // retail price
    RetailPrice = document.getElementById("Price" + RowNumber).value;
    if (RetailPrice.substr(0, 1) == CurrencyPrefix)
        RetailPrice = parseFloat(RetailPrice.substr(1));
    document.getElementById("Price" + RowNumber).value = dollarize(RetailPrice);
    
    // total price
    var x = document.getElementById("SellInventory").rows;
    y = x[RowNumber + 1].cells;
    TotalPrice = QuantityPurchased * RetailPrice;
    y[TotalPriceCol].innerHTML = dollarize(TotalPrice);
    
    // total line (only two columns since the total cell spans 0 to 4)
    TotalCost = 0;
    for (i = 1; i <= MaxSellItems; i++)
    {
        y = x[i].cells;
        TotalPrice = y[TotalPriceCol].innerHTML;
        if (TotalPrice.substr(0, 1) == CurrencyPrefix)
            TotalPrice = parseFloat(TotalPrice.substr(1));
        TotalCost = TotalCost + TotalPrice;
    }
    y = x[MaxSellItems + 1].cells;
    y[1].innerHTML = dollarize(TotalCost);
}

//********************************************************************
// UpdateInventoryControl(UpdatedControl, RowNumber)
//
// Purpose: Update the inventory information when a new part number
//          or description is selected by the user.
//
// Inputs:
//   UpdatedControl - the name of the control that contains the new
//                    entry.
//   RowNumber - the row number within the table that we are updating
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function UpdateInventoryControl(UpdatedControl, RowNumber)
{
    // there are two controls that may change. if the user selected
    // a new part number, we will use the part number to get the 
    // description. if the user selected a new description, we will
    // update the part number
    if (UpdatedControl == "PartNumber")
    {
        // part number selected, update the description
        PartNumberIndex = document.getElementById("PartNumber" + RowNumber).selectedIndex;
        PartNumber = document.getElementById("PartNumber" + RowNumber).value;
        
        // if none is selected, clear these values
        if (PartNumber.toUpperCase() == "NONE")
        {
            // set the quantity and retail price
            Quantity = 0;
            RetailPrice = 0;
        }
        else
        {
            // valid part number selected
            // adjust for the "None" option
            PartNumberIndex = PartNumberIndex - 1;
        
            // select the description from the list
            DescriptionControlList = document.getElementById('PartDescription' + RowNumber);
            for (var i = 0; i < DescriptionControlList.length; i++)
            {
                // limit the compared string to the length specified in the control
                var CompareString = DescriptionList[PartNumberIndex].substr(0, DescriptionSizeLimit);
                if (DescriptionControlList.options[i].text.toUpperCase() == CompareString.toUpperCase())
                {
                    // select the index 
                    DescriptionControlList.selectedIndex = i;
                    
                    // save the index for updating the prices
                    InventoryArrayIndex = PartNumberIndex;
                    break;
                }
            }   
        
            // set the quantity and retail price
            Quantity = 1;
            RetailPrice = RetailPriceList[InventoryArrayIndex];
        }     
    }
    else
    {
        // description selected, update the part number
        PartDescription = document.getElementById("PartDescription" + RowNumber).value;
                   
        // if none is selected, clear these values
        if (PartDescription.toUpperCase() == "NONE")
        {
            // set the quantity and retail price
            Quantity = 0;
            RetailPrice = 0;
        }
        else
        {
           // the description list is alpabetic whereas the arrays are ordered by
            // part number so we will have to search for the index
            for (var i = 0; i < DescriptionList.length; i++)
            {
                if (DescriptionList[i].toUpperCase() == PartDescription.toUpperCase())
                {
                    // description found, add one for the "None" option
                    DescriptionIndex = i + 1;
                    
                    // save the index for updating the prices
                    InventoryArrayIndex = i;
                    break;
                }
            }
        
            // select the part number from the list
            PartNumberControlList = document.getElementById('PartNumber' + RowNumber);
            PartNumberControlList.selectedIndex = DescriptionIndex;
            
            // set the quantity and retail price
            Quantity = 1;
            RetailPrice = RetailPriceList[InventoryArrayIndex];
        }
    }

    // quantity purchased
    document.getElementById("Quantity" + RowNumber).value = Quantity;
    
    // retail price
    document.getElementById("Price" + RowNumber).value = RetailPrice;
    
    // format the fields and update the total price
    FormatControl(RowNumber);
}

//-->
</script>
