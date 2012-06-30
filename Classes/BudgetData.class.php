<?php
    
class BudgetData extends MySQLData
{
	public static $fields = array(
		'year',
		'superfund',
		'fund',
		'superfund_fund',
		'department',
		'division',
		'department_division',
		'ledger_type',
		'ledger_description',
		'ledger_type_ledger_description',
		'amount'
	);
	
	public static $name = 'budget';

	function __construct($id = null, $field = null)
	{
		// Load the database. @todo Load it from the config
		$this->database = 'appdata';
		
		// Load the table
		$this->tableName = self::$name;
		
		// Call the function from the parent
		parent::__construct($id, $field);
    }
}