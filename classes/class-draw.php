<?php

class ek_pp_draw
{
	
	static function drawProjectsShortcode($atts)
	{
		$atts = shortcode_atts( 
			array(
				'id'		=> '',
				), 
			$atts
		);	

		

		$projectTypeID = (int) $atts['id'];	
		
		echo ek_pp_draw::drawProjectsPage($projectTypeID);	

		
	}
	
	
	static function drawProjectsPage($projectTypeID)
	{
		
		
		
		if(isset($_GET['projectID']) )
		{
			$projectID = $_GET['projectID'];
			$html =  ek_pp_draw::drawProject($projectID);
		}		
		else			
		{
			$html =  ek_pp_draw::drawProjectsTable($projectTypeID);
		}

		
		
		return $html;
		
		
	}
	
	
	static function drawProject($projectID)
	{

		
		$userID = get_current_user_id();
		$projectTypeID = wp_get_post_parent_id( $projectID ); 		
		$maxItems= get_post_meta( $projectTypeID, 'maxItems', true );		
		$projectLeadName = '';
		$projectLeadEmail='';

	
		// Project Leads
		$projectLeadsArray = get_post_meta($projectID, "projectLeadsArray", true);
		
		if(is_array($projectLeadsArray))
		{
			$projectLeadName = $projectLeadsArray[0]['name'];
			$projectLeadEmail = $projectLeadsArray[0]['email'];
		}

		
		// Remove the Last Comma
		//$supervisorNameStr = substr($supervisorNameStr, 0, -1);

		$projectTitle = get_the_title($projectID);
		
		$projectTableLink = get_the_permalink();
		
		// Get current Basket Items
		$args = array(
			"projectTypeID"	=> $projectTypeID,
			"userID"	=> $userID,
		);
		

		$myProjectBasket= ek_projects_queries::getUserBasket($args);
		$myItemCount = count ($myProjectBasket);		
		
		
		$html='';
		$html.= '<a href="'.$projectTableLink.'"><i class="fas fa-chevron-left"></i> Back to projects</a>';
		$html.= '<h1>'.$projectTitle.'</h1>';
		
		$html.= '<div class="ek_project_wrapper">';
		$html.= '<div class="project_info">';
		if($projectLeadName)
		{
			$html.= 'Project Lead : '.$projectLeadName.'<br/>';
		}
		
		// Get the tags
		$terms = get_the_terms( $projectID, 'ek-project-tags' );
		if(!is_array($terms) )
		{
			$html.= '-';
		}
		else
		{
			foreach ( $terms as $term ) {
				$html.= $term->name.'<br/>';
			}
		}			
		
		
		$post_content = get_post($projectID);
		$content = $post_content->post_content;
		$html.= apply_filters('the_content',$content);	


		$addButtonStyle="";
		$addButtonSuccessStyle="";


		// Is this in the basket?
		if(in_array($projectID, $myProjectBasket) )
		{		
			// Get current post ID		
			$addButtonStyle= ' ek-hidden ';
		}
		else
		{
			$addButtonSuccessStyle= ' ek-hidden ';
		}
		
		// Have they hit max items?
		if($maxItems<=$myItemCount)
		{
			$addButtonStyle= ' ek-hidden ';
		}
		
	
		$html.='<div id="basket-add-save-feedback-div" class="ek-hidden">';
		$html.= 'Saving...';
		$html.='</div>';

		
		// Only add the Add button i they have not finalise
		$myFinalisedProjects = get_user_meta($userID, 'ekFinalisedProjects', true);
		
		$finalised=false;
		
		if(is_array($myFinalisedProjects) )
		{
			if(isset($myFinalisedProjects[$projectTypeID]) )
			{
				$finalised = true;
			}
		}		

		if($finalised<>true)
		{
			$html.='<div id="basket-add-button-div" class="'.$addButtonStyle.'">';
			$html.= '<button class="basket-add-button" id="addItem_'.$projectID.'"><i class="fas fa-cart-plus"></i> Add to your basket</a>';
			$html.='</div>';
		}
		
		$html.='<div id="basket-add-success-div" class="'.$addButtonSuccessStyle.'">';				
		$html.= '<i class="fas fa-check"></i> You have selected this project';
		$html.='</div>';		
		
	
		$html.= '</div>';
		$html.= '<div class="project_side">';

		

		$html.= '<div id="imperialBasketDiv">';
		$basketStr = ek_pp_draw::drawBasketWidget($projectTypeID);
		$html.= $basketStr;
		$html.= '</div>';

		$html.= '</div>';
		$html.= '</div>';
		
		return $html;

	
	}
	
	static function drawProjectsTable($projectTypeID)
	{
		$html = '';
		
		// Get the list of projects
		$args  = array("projectTypeID" => $projectTypeID);
		$projectList = ek_projects_queries::getProjectTypeProjects($args);
		
		$userID = get_current_user_id();
		$args  = array(
			"projectTypeID" => $projectTypeID,
			"userID"	=> $userID,
		);
		$myProjectBasket= ek_projects_queries::getUserBasket($args);

		
		$html.='<div id="tableLoader">Loading projects...</div>';
		$html.= '<table id="projectsTable" class="ek-hidden">';
		$html.= '<thead><tr><th>Project Name</th><th>Supervisor</th><th>Tags</th></tr></thead>';
		
		foreach($projectList as $projectInfo)
		{
			$html.='<tr>';
			$projectID = $projectInfo->ID;
			$projectName = $projectInfo->post_title;
			//$projectInfo = $projectInfo->post_content;

			$projectLeads = get_post_meta( $projectID, 'projectLeadsArray', true );

			$leadName = "";
			$leadEmail = "";
			if(is_array($projectLeads) )
			{
				$leadName = $projectLeads[0]['name'];
				$leadEmail = $projectLeads[0]['email'];
			}

			
			
			// Get the current page URL
			$projectURL = get_the_permalink();
			$html.= '<td><a href="'.$projectURL.'?projectID='.$projectID.'">'.$projectName.'</a>';
			if(in_array($projectID, $myProjectBasket) )
			{
				$html.= '<br/><span class="projectTableChosenItem">This is in your basket</span>';
			}
			$html.='</td>';
			$html.='<td>';
			
			if($leadName<>"")
			{
				$html.=$leadName;
			}
			
			$html.='</td>';
			$html.='<td>';
			
				
			$terms = get_the_terms( $projectID, 'ek-project-tags' );		

			if(!is_array($terms) )
			{
				$html.= '-';
			}
			else
			{
				foreach ( $terms as $term ) {
					$html.= $term->name.'<br/>';
				}
			}			
			
			
			
			$html.='</td>';

			$html.'</tr>';
		}
		$html.='</table>';
		
		?>
			<script>
			jQuery(document).ready(function(){	
				if (jQuery('#projectsTable').length>0)
				{
					jQuery('#projectsTable').dataTable({
						"bAutoWidth": true,
						"bJQueryUI": true,
						"sPaginationType": "full_numbers",
						"iDisplayLength": 50, // How many numbers by default per page
					});
				}
				

				
	
				// Also show the data table
				jQuery('#tableLoader').hide();				
				jQuery('#projectsTable').show();	
				

	
				
				
			});
		</script>	
		<?php

		return $html;		
		
	}
	
	static function drawBasketWidget($projectTypeID)
	{
		$minItems= get_post_meta( $projectTypeID, 'minItems', true );		
		$maxItems= get_post_meta( $projectTypeID, 'maxItems', true );		
		$userID = get_current_user_id();
		$myFinalisedProjects = get_user_meta($userID, 'ekFinalisedProjects', true);
		
		$finalised=false;
		
		if(is_array($myFinalisedProjects) )
		{
			if(isset($myFinalisedProjects[$projectTypeID]) )
			{
				$finalised = true;
			}
		}	

				
		// Get current Basket Items
		$args = array(
			"projectTypeID"	=> $projectTypeID,
			"userID"	=> $userID,
		);

				
		$myCompleteProjectBasket = get_user_meta($userID, 'ekProjectBasket', true);		
		$myProjectBasket= ek_projects_queries::getUserBasket($args);		
		$itemCount = count ($myProjectBasket);	

		
		$html='';
		
		$html.='<div class="basketWrap">';		
		$html.='<h1><i class="fas fa-shopping-cart"></i> My Basket</h1>';

		
		
		if($finalised==true)
		{
			$html.='<div class="finalisedMessage">Your choices are now finalised. Thank you!</div>';
		}
		
		//$html.='Pick up to '.$maxItems.' projects.<br/>';
		if($itemCount==0)
		{
			$html.= 'You have no saved projects<br/>';
		}
		else
		{
			$html.= 'You have <b>'.$itemCount.'</b> project(s) in your basket.';

			if($itemCount>=$minItems && $finalised==false)
			{
				$html.='<div class="finaliseProjectsDiv">';
				$html.='<button class="finaliseProjectsButton" id="finaliseProjectsButton">Finalise my choices</button>';
				$html.='<div id="finaliseConfirmDiv" class="alertText" style="display:none">';
				$html.='Are you sure you want to finalise these choices?<br/>';
				$html.='This cannot be undone!<br/>';
				$html.='<button class="finaliseProjectsConfirm" id="finaliseProjectsConfirm_'.$projectTypeID.'">Yes, finalise my choices</button>';
				$html.='</div>';
				$html.='</div>';
				
			}			
			
			
			
			
			foreach($myProjectBasket as $thisItemID)
			{
				
				$html.='<div class="projectBasketItem">';
				$projectTitle = get_the_title($thisItemID);
				$projectURL = '?projectID='.$thisItemID;
				$html.= '<div class="projectBasketItemTitle">';
				$html.='<a href="'.$projectURL.'">'.$projectTitle.'</a></div>';
				
				
				
				if($finalised==false)
				{
					$html.='<div class="projectBasketItemMeta">';
					$html.='<button id="remove_'.$thisItemID.'" class="ek-basket-remove"><i class="fas fa-trash-alt"></i> Remove</button>';
					
					// Delete Confirm Button
					$html.='<div class="project-delete-confirm" id="project-delete-confirm_'.$thisItemID.'" style="display:none;">';
					$html.='Are you sure you want to remove this item?<br/>';
					$html.='<button id="remove-confirm_'.$thisItemID.'" class="ek-basket-remove-confirm">Yes, Remove</button>';
					$html.='</div>';
					
					$html.='</div>';
				
				}
				$html.='</div>';// end of project item div
			}
		}
		

		
		$html.='</div>';		
		
		return $html;
	}
}
	
	
?>