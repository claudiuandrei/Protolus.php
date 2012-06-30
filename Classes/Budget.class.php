<?php
    
class Budget
{
	// Create the revenues and expenses arrays
	public $expenses = array();
	public $revenues = array();
	
	// Raw data container
	private $data;
	
	/**
	 * Use the factory design pattern
	 */
	public function factory($budget)
	{
		return new Budget($budget);
	}
	
	/**
	 * Create the Budget object
	 */
	public function __construct($budget)
	{
		// Load the data
		$this->data = $budget;
		
		// Parse the data into expenses and revenues
		foreach ($this->data as $data) {
			
			// Negative revenue account as expenses and viceversa
			if ($data['amount'] < 0) {
				$data['amount'] = -$data['anount']; 
				$data['ledger_type'] = ($data['ledger_type'] === 'Expense') ? 'Revenue' : 'Expense';
			}
			
			// Check wheter this is an expense or a revenue
			($data['ledger_type'] === 'Expense')
				? static::add($this->expenses, $data)
				: static::add($this->revenues, $data);
		}
	}
	
	/**
	 * Split the expenses into categories and parse only the important data
	 * For the purpose of this exercise I will be focusing only on important groups
	 */
	public static function divide($data, $divisions = 100, $count = null, $bucket = '*')
	{
		// Parsed data
		$parsed = array();
		
		// Add the expenses
		if ($count === null) {
			$count = static::count($data);
		}
		
		// Load the categories
		foreach ($data as $name => $group) {
			
			// The expenses that take less than 1 division will be added to others
			if (($group_count = static::count($group)) < $count / $divisions) {
				$other += $group_count;
				
				// Skip the rest
				continue;
			}
			
			// Add the expenses to the group
			$parsed[$name] = array('divisions' => round($group_count / $count * $divisions, 2), 'amount' => $group_count);
		}
		
		// Sort the budget by ledger description
		ksort($parsed);
		
		// Put everything else in the bucket
		$parsed[$bucket] =  array('divisions' => round($other / $count * $divisions, 2), 'amount' => $other);
		
		// Return the parsed data for display
		return $parsed;
	}
	
	/**
	 * Split the expenses into categories and parse only the important data
	 * For the purpose of this exercise I will be focusing only on important groups
	 */
	public static function parse($data)
	{
		// Parsed data
		$parsed = array();
		
		// Load the categories
		foreach ($data as $name => $group) {
			
			// Add the expenses to the group
			$parsed[$name] = static::count($group);
		}
		
		// Return the parsed data for display
		return $parsed;
	}
	
	/**
	 * Add all the expenses/revenues from a data group
	 */
	public static function count($group)
	{
		$sum = 0;
		foreach ($group as $data) {
			$sum += (isset($data['amount'])) ? $data['amount'] : static::count($data);
		}
		
		return $sum;
	}
	
	/**
	 * Helper function to add data to a container
	 */
	public static function add( & $group, $data)
	{
		(isset($group[$data['ledger_description']]))
			? $group[$data['ledger_description']][] = $data
			: $group[$data['ledger_description']] = array($data);
	}
	
	// Get the categories
	// Get the total sum
	
	// Load the data
	// Parse the data into expenses and revenues, also select the data field you want to sort on
	// Load the expenses, based on the filtered data
	
}