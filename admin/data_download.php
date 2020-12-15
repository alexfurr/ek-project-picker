<?php
if ( ! defined( 'ABSPATH' ) ) 
{
	die();	// Exit if accessed directly
}

// Only let them view if admin		
if(!current_user_can('delete_others_pages'))
{
	die();
}	

$projectTypeID = $_GET['ID'];
$projectTypeName = get_the_title($projectTypeID);


echo '<h1>'.$projectTypeName.'</h1>';
echo '<form method="post" action="?page=ek_project_data&ID='.$projectTypeID.'&myAction=unfinalise">';
echo '<a href="edit.php?page=ek_project_data&ID='.$projectTypeID.'&myAction=projectDataDownload" class="button-primary">Download this data</a>';

echo '<input type="submit" value="Unfinalise selected" class="button-secondary">';

if(isset($_REQUEST['unfinalise']) )
{
	// Get the checkboxes that have been checked
	
	foreach ($_POST as $KEY=> $userID)
	{
		
		if($KEY=="unfinalise")
		{
		
			$myFinalisedProjects = get_user_meta($userID, 'ekFinalisedProjects', true);	
			unset($myFinalisedProjects[$projectTypeID]);						
			update_user_meta( $userID, "ekFinalisedProjects", $myFinalisedProjects );
			
		}
	}
	
	
	
}


echo ek_pp_draw::drawSubmissions($projectTypeID);


echo '</form>';





?>