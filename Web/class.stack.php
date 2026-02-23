<?php
////////////////////////////////////////////////////
// STACK - PHP stack class
//
//
// Provide the stack functions.
//
// Author: Jim Covington
//
////////////////////////////////////////////////////

class Stack
{
    /**
     *  max stack size
     *  @var int
     */
    var $StackSize = 100;

    /**
     *  declare the stack
     *  @var array
     */
    var $StackData= array();

    /**
     *  declare the stack
     *  @var int
     */
    var $NumberOfItems = 0;
    
    //********************************************************************
    // Initialise()
    //
    // Purpose:      Initialize the stack
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
    function Initialize()
    {
        // Set to no items in stack
        $this->NumberOfItems = 0;
    }
    
    //********************************************************************
    // Push(Item As String) As Boolean
    //
    // Purpose: Push an item onto the stack
    //
    // Inputs:
    //   Item - the item to push
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   True if the item was stored
    //*********************************************************************
    function Push($Item)
    {
        // Check if there is space
        If ($this->NumberOfItems < $this->StackSize)
        {
            // Store the item
            $this->StackData[$this->NumberOfItems] = $Item;
            $this->NumberOfItems = $this->NumberOfItems + 1;
            
            // item was stored, return true
            $PushResult = 1;
        }
        Else
        {
            // If not enough space return false
            $PushResult = 0;
        }
        
        // return the results
        return $PushResult;
    }
    
    //********************************************************************
    // Pop(Item As String) As String
    //
    // Purpose: Pop an item off the stack
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //
    // Returns:
    //   The item poped
    //*********************************************************************
    function Pop()
    {
        // Check if there is an item
        If (!$this->StackIsEmpty())
        {
            // Take off the item
            $this->NumberOfItems = $this->NumberOfItems - 1;
            $PopResult = $this->StackData[$this->NumberOfItems];
        }
        Else
        {
            // no data available, return null
            $PopResult = "";
        }
        
        // return the results
        return $PopResult;
    }
    
    //********************************************************************
    // Function StackIsEmpty() As Boolean
    //
    // Purpose: Return true if the stack is empty
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   True if the stack is empty
    //*********************************************************************
    function StackIsEmpty()
    {
        // if there are no items available, return true
        If ($this->NumberOfItems == 0)
            $StackIsEmptyResult = 1;
        Else
            $StackIsEmptyResult = 0;
            
        // return the results
        return $StackIsEmptyResult;
    }
    
    //----Top of stack
    //********************************************************************
    // TopOfStack(Item As String) As Boolean
    //
    // Purpose: Get the top item of the stack without removing it.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   Item - returned if a value is found
    //
    // Returns:
    //   True if an item is on the top of the stack.
    //*********************************************************************
    function TopOfStack(&$Item)
    {
        // If can pop one, then push it
        $Item = $this->Pop();
        If (strlen($Item) > 0)
        {
            $this->Push($Item);
            $TopOfStackResult = 1;
        }
        Else
        {
            $TopOfStackResult = 0;
        }
        
        // return the result
        return $TopOfStackResult;
    }
}


 ?>
