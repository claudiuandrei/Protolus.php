<?php
    ResourceBundle::$minify = false;
    
    // Load the data
    $budget = Data::query('BudgetData');
    
    // Create the group array
    $group = array();
    
    // Sort the data on years
    foreach ($budget as $data) {
    	(isset($group[$data['year']]))
			? $group[$data['year']][] = $data
			: $group[$data['year']] = array($data);
    }
    
    // Create the data array
    $budget_data = $max = array();
    
    // Create the data groups
    foreach($group as $year => $budget) {
	    $data = Budget::factory($budget);
	    
	    // Count the expenses and revenues
	    $expenses_count = Budget::count($data->expenses);
	    $revenues_count = Budget::count($data->revenues);
	    
	    // Calculate the max for the expenses
	    $max[] = max($expenses_count, $revenues_count);
	    
	    // Start creating the object to display
	    $budget_data[$year] = array(
	    	'expenses_count' => $expenses_count,
	    	'revenues_count' => $revenues_count,
	    	'difference' => $revenues_count - $expenses_count,
	    	'data' => $data
	    );
    }
    
    // Load the reference value
    // We use this to establish the hight of the visuals
    $reference = max($max);
    
    // Add the relative data to the budgets
    foreach ($budget_data as & $data) {
	    $data['expenses'] = Budget::divide($data['data']->expenses, 10, $reference, 'Minor expenses');
	    $data['revenues'] = Budget::divide($data['data']->revenues, 10, $reference, 'Minor revenues');
	    
	    unset($data['data']);
    }
    
   // Pass the data to the view
    $renderer->assign('data', $budget_data);
?>