<?php

class ek_projects_queries
{
	static function getAllProjects()
	{
		$args = array(
			'posts_per_page'   => -1,
			'order'            => 'ASC',
			'post_type'        => 'ek_project',			
			'post_status'      => 'publish',
		);
		$posts_array = get_posts( $args );		
		
		return  $posts_array;
	}
	
	
	
	static function getProjectTypeProjects($args)
	{

		$projectTypeID = $args['projectTypeID'];

			
		
		$args = array(
			'posts_per_page'   => -1,
			'order'            => 'ASC',
			'post_type'        => 'ek_project',
			'post_parent'		=> $projectTypeID,
			'post_status'      => 'publish'
		);
		
		

		$posts_array = get_posts( $args );		

		return  $posts_array;
		
		
	}	
	
	static function getUserBasket($args)
	{
	
		$projectTypeID = $args['projectTypeID'];
		$userID = $args['userID'];

		$myProjectBasket = get_user_meta($userID, 'ekProjectBasket', true);

		if(is_array($myProjectBasket))
		{
			

			// If the array key for this projectID is NOT set then they have no basket items
			if(!isset($myProjectBasket[$projectTypeID]))
			{
				$myProjectBasket = array();
			}
			else
			{
				$myProjectBasket =	$myProjectBasket[$projectTypeID];		
			}
		}
		else
		{

			$myProjectBasket = array();
		}
		
		return $myProjectBasket;
		
	}
	

}
	
	
?>