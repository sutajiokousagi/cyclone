<?php
/*
- Created by Torin
- This is a simple HelloFilter module that contains simple string & number processing
*/

require_once("baseclass_filter.php");

class filter_hellofilter extends baseclass_filter
{
	/*
	 * Override superclass to return my own version number
	 */
	public function getModuleVersion()
	{
		return "1.0";
	}
	
	/*
	 * Filters implementation
	 */
	 
	protected function filter_contains($event_param, $filter_param)
	{
		$this->filter_result = strpos($event_param, $filter_param) !== false;
		return $this->filter_result;
	}
	
	protected function filter_starts_with($event_param, $filter_param)
	{
		$this->filter_result = strpos($event_param, $filter_param) == 0;
		return $this->filter_result;
	}
	
	protected function filter_ends_with($event_param, $filter_param)
	{
		$this->filter_result = substr( $event_param, -strlen( $filter_param ) ) == $filter_param;
		return $this->filter_result;
	}
	
	protected function filter_equals($event_param, $filter_param)
	{
		$this->filter_result = ($event_param == $filter_param);
		return $this->filter_result;
	}
	
	protected function filter_greater($event_param, $filter_param)
	{
		$this->filter_result = intval($event_param) > intval($filter_param);
		return $this->filter_result;
	}
	
	protected function filter_greater_equals($event_param, $filter_param)
	{
		$this->filter_result = intval($event_param) >= intval($filter_param);
		return $this->filter_result;
	}
	
	protected function filter_less($event_param, $filter_param)
	{
		$this->filter_result = intval($event_param) < intval($filter_param);
		return $this->filter_result;
	}
	
	protected function filter_less_equal($event_param, $filter_param)
	{
		$this->filter_result = intval($event_param) <= intval($filter_param);
		return $this->filter_result;
	}
	
	protected function filter_within($event_param, $filter_param)
	{
		$minmax = explode($filter_param, ",");
		if (count($minmax) < 2) {
			$this->filter_result = true;
			return true;
		}
		$this->filter_result = intval($minmax[0]) <= intval($event_param) && intval($event_param) <= intval($minmax[1]);
		return $this->filter_result;
	}
	
}

?>