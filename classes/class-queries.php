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
	
	
	// Get an array of blog users and their role
	static function getBlogUsers()
	{
		
		$userArray = array();
		$blogusers = get_users();
		
		// Array of WP_User objects.
		foreach ( $blogusers as $userInfo )
		{
			$userID = $userInfo->ID;
			$fullname = esc_html( $userInfo->display_name );
			$firstName= esc_html( $userInfo->first_name );
			$surname= esc_html( $userInfo->last_name );		
			$username = $userInfo->user_login;
			$roles = $userInfo->roles;
			if($roles)
			{
				$userlevel = $roles[0];
			}
			else
			{
				$userlevel = "";	
			}
			
			$userArray[$userID] = array
			(
				"fullname"	=> esc_html( $userInfo->first_name ).' '.esc_html( $userInfo->last_name ),
				"firstName"	=> esc_html( $userInfo->first_name ),
				"surname"	=> esc_html( $userInfo->last_name ),
				"username"	=> $userInfo->user_login,
				"role"		=> $userlevel,
			);
		}	

		return $userArray;
		
	}	
	

}
	
	
?>