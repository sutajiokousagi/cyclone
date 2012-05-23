<?php
/*
- Created by Torin
- A common utility for printing outputs
*/

function func_json_clean_param_string($params_json)
{
	return $params_json;
	//This was needed when JavaScript UI was doing JSON encoding wrongly
	//return str_replace('\\"', '"', $params_json);
}

?>