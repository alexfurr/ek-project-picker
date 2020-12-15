<?php
$ek_ppAjax = new ek_ppAjax();
class ek_ppAjax
{
	
	//~~~~~
	public function __construct ()
	{
		$this->addWPActions();
	}	
	
	
	function addWPActions()
	{	
	
		// Add textual feedback for clicks
		add_action( 'wp_ajax_addBasketItem', array($this, 'addBasketItem' ));
		add_action( 'wp_ajax_removeBasketItem', array($this, 'removeBasketItem' ));
		add_action( 'wp_ajax_finaliseBasket', array($this, 'finaliseBasket' ));
		
		
		
		
		
	}
	
	public function addBasketItem()
	{
		// Check the AJAX nonce				
		check_ajax_referer( 'saveBasket_ajax_nonce', 'security' );
		
		// Add this item to the user meta string
		$userID = get_current_user_id();
		
		$projectID = $_POST['projectID'];
		$projectTypeID = wp_get_post_parent_id( $projectID ); 

		$maxItems= get_post_meta( $projectTypeID, 'maxItems', true );

		
		// Get current Basket Items
		$args = array(
			"projectTypeID"	=> $projectTypeID,
			"userID"	=> $userID,
		);
		$basketArray= ek_projects_queries::getUserBasket($args);

			
		// Count the items, check not more than X amount
		$itemCount = count($basketArray);
		
		if($itemCount<$maxItems)
		{
			
			
			
			
			
			// Check item doesn't already exist //
			if(!in_array($projectID, $basketArray) )
			{		
				$basketArray[] = $projectID;	
			
				$myProjectBasket = get_user_meta($userID, 'ekProjectBasket', true);
				
				// If its not an array its never been saved create the first master array
				if(!is_array($myProjectBasket) )
				{
					$myProjectBasket = array();
				}

				$myProjectBasket[$projectTypeID] = $basketArray;
				
				update_user_meta( $userID, "ekProjectBasket", $myProjectBasket );
			}
		}

		
		
	
		$basketStr = ek_pp_draw::drawBasketWidget($projectTypeID);
		
		echo $basketStr;		
		
		die();
	}	
	
	public function removeBasketItem()
	{
		
		
		// Check the AJAX nonce				
		check_ajax_referer( 'saveBasket_ajax_nonce', 'security' );
		
		// Add this item to the user meta string
		$userID = get_current_user_id();
		
		$projectID = $_POST['projectID'];
		$projectTypeID = wp_get_post_parent_id( $projectID ); 		
		
		// Get current Basket Items
		$args = array(
			"projectTypeID"	=> $projectTypeID,
			"userID"	=> $userID,
		);
		$thisBasketArray= ek_projects_queries::getUserBasket($args);
			
			
		if (($key = array_search($projectID, $thisBasketArray)) !== false) {
			unset($thisBasketArray[$key]);
		}
		
		// Reget the master basket so we don't lose other project type saved data
		$myProjectBasket = get_user_meta($userID, 'ekProjectBasket', true);
		$myProjectBasket[$projectTypeID] = $thisBasketArray;
			
		update_user_meta( $userID, "ekProjectBasket", $myProjectBasket );
		
		$basketStr = ek_pp_draw::drawBasketWidget($projectTypeID);
		
		echo $basketStr;
		
		
		die();
	}	
	
	
	function finaliseBasket()
	{
		$str='';
		// Check the AJAX nonce				
		check_ajax_referer( 'saveBasket_ajax_nonce', 'security' );
		
		// Add this item to the user meta string
		$userID = get_current_user_id();
		
		$projectTypeID = $_POST['projectTypeID'];
		
		
		// Check they have the valid number of basket entries in case they have two tabs and deleted some
		$myCompleteProjectBasket = get_user_meta($userID, 'ekProjectBasket', true);	
		// Get current Basket Items
		$args = array(
			"projectTypeID"	=> $projectTypeID,
			"userID"	=> $userID,
		);		
		$myProjectBasket= ek_projects_queries::getUserBasket($args);		
		$itemCount = count ($myProjectBasket);

		$minItems= get_post_meta( $projectTypeID, 'minItems', true );		
		$maxItems= get_post_meta( $projectTypeID, 'maxItems', true );		

		if($itemCount >= $minItems && $itemCount <=$maxItems)
		{
			
	
		
			$myFinalisedProjects = get_user_meta($userID, 'ekFinalisedProjects', true);

			if(!is_array($myFinalisedProjects) )
			{
				$myFinalisedProjects = array();
			}
			
			$currentDate = date('Y-m-d H:i:s');
			
			$myFinalisedProjects[$projectTypeID] = $currentDate;
			
			update_user_meta( $userID, "ekFinalisedProjects", $myFinalisedProjects );
		}
		else
		{
			$str.='You have too few or too many items in your basket';
		}
		
		$str.= ek_pp_draw::drawBasketWidget($projectTypeID);
		
		echo $str;
		
		
		die();
		
	}
	
} // End Class
?>