<?php

class ek_projects_utils
{

	static function getUKdate($inputDate)
	{
		$tz = new DateTimeZone('Europe/London');
		$date = new DateTime($inputDate);
		$date->setTimezone($tz);
		$UKdate = $date->format('Y-m-d H:i:s');
		
		
		return $UKdate;
	}

}
	
	
?>