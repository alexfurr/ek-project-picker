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

		return ek_pp_draw::drawProjectsPage($projectTypeID);


	}


	static function drawProjectsPage($projectTypeID)
	{

        $view = isset($_GET['view']) ? $_GET['view'] : '';

        switch ($view)
        {

            case "project":
                $project_id = $_GET['project-id'];
                $html =  ek_pp_draw::drawProject($project_id);
            break;

            case "finalise-check":
            $html =  ek_pp_draw::finalise_choices($projectTypeID);
            break;

            case "finalise-confirmed":
            $html =  ek_pp_draw::finalise_choices_confirm($projectTypeID);
            break;


            default:
                $html =  ek_pp_draw::drawProjectsTable($projectTypeID);
            break;
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

        $this_post_meta = get_post_meta($projectID);
        $project_type = $this_post_meta['project_type'][0];
        $wet_dry = $this_post_meta['wet_dry'][0];
        $supervisor_name = $this_post_meta['supervisorName'][0];
        $supervisor_email = $this_post_meta['supervisorEmail'][0];
        $keywords = unserialize($this_post_meta['keywords'][0]);

        $other_meta = unserialize($this_post_meta['project_meta'][0]);


        $html.= '<h3>Supervisor</h3>';
        $html.= '<a href="mailto:'.$supervisor_email.'">'.$supervisor_name.'</a>';
        $html.= '<h3>Project Type</h3>';
        $html.= $project_type.' ('.$wet_dry.')';


        foreach ($other_meta as $this_meta)
        {
            foreach($this_meta as $meta_title => $meta_value)
            {
                $html.= '<h3>'.$meta_title.'</h3>'.$meta_value;
            }

        }




        $html.='<hr/>';




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

        // Get the custmo meta in this table
        $project_custom_meta = get_post_meta($projectTypeID, 'pp_custom_meta', true);
        // get the custom meta for this post



		$html.='<div id="tableLoader">Loading projects...</div>';
        $html.= '<table id="projectsTable" class="ek-hidden">';
        //$html.= '<table id="projectsTable" >';
		$html.= '<thead><tr><th>Project Name</th><th>Project Type</th><th>Supervisor</th><th>Keywords</th></tr></thead>';

		foreach($projectList as $projectInfo)
		{
			$projectID = $projectInfo->ID;
			$projectName = $projectInfo->post_title;
			//$projectInfo = $projectInfo->post_content;

            $this_post_meta = get_post_meta($projectID);



            $project_type = $this_post_meta['project_type'][0];
            $wet_dry = $this_post_meta['wet_dry'][0];
            $supervisor_name = $this_post_meta['supervisorName'][0];
            $supervisor_email = $this_post_meta['supervisorEmail'][0];
            $keywords = unserialize($this_post_meta['keywords'][0]);


			// Get the current page URL
			$projectURL = get_the_permalink();


            $html.='<tr>';
			$html.= '<td><a href="'.$projectURL.'?view=project&project-id='.$projectID.'">'.$projectName.'</a>';
			if(in_array($projectID, $myProjectBasket) )
			{
				$html.= '<br/><span class="projectTableChosenItem">This is in your basket</span>';
			}
			$html.='</td>';

            $html.='<td>'.$project_type.'<br/>'.$wet_dry.'</td>';

			$html.='<td><a href="mailto:'.$supervisor_email.'">'.$supervisor_name.'</a></td>';
            $html.='<td class="smallText">';
            foreach ($keywords as $this_keyword)
            {
                $html.=$this_keyword.'<br/>';
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
        $allow_ordering= get_post_meta( $projectTypeID, 'allow_ordering', true );

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

			if($finalised==false && $itemCount<$minItems)
			{
				if($minItems)
				{
					$html.= '<br/><span class="smallText">You need at least <strong>'.$minItems.' items</strong> in your basket before you can confirm these choices.</span>';
				}
			}

			if($itemCount>=$minItems && $itemCount<=$maxItems &&  $finalised==false)
			{

				$html.='<div class="finaliseProjectsDiv">';


                if($allow_ordering==true)
                {
                    $html.='<a href="?view=finalise-check" class="imperial-button"" id="finaliseProjectsButton">Finalise my choices</a>';
                }
                else
                {
                    $html.='<button class="finaliseProjectsButton" id="finaliseProjectsButton">Finalise my choices</button>';
                    $html.='<div id="finaliseConfirmDiv" class="alertText" style="display:none">';
                    $html.='Are you sure you want to finalise these choices?<br/>';
                    $html.='This cannot be undone!<br/>';
                    $html.='<button class="finaliseProjectsConfirm" id="finaliseProjectsConfirm_'.$projectTypeID.'">Yes, finalise my choices</button>';
                    $html.='</div>';
                }


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

	static function drawSubmissions($projectTypeID, $CSV=false)
	{

		$html='';
		$csvArray=array();

		// Create lookup array of the titles for the projects
		$args = array(
			"projectTypeID"	=> $projectTypeID,
		);
		$theseProjects = ek_projects_queries::getProjectTypeProjects($args);


		$myProjectLookupArray = array();
		foreach ($theseProjects as $projectInfo)
		{
			$projectID = $projectInfo->ID;
			$projectName = $projectInfo->post_name;
			$myProjectLookupArray[$projectID] = $projectName;
		}



		$maxItems = get_post_meta($projectTypeID, "maxItems", true);

		$userArray = ek_projects_queries::getBlogUsers();
		$html.= '<table class="imperial-table" width="90%">';

		$html.= '<tr><th><input type="checkbox" onClick="toggle(this)" />Name</th><th>Username</th><th>Role</th><th>Finalised Date</th>';
		$csvArrayHeaderArray = array("Name", "Username", "Role", "Finalised Date");

		$i=1;
		while($i<=$maxItems)
		{
			$html.= '<th>'.$i.'</th>';
			$csvArrayHeaderArray[] = $i;
			$i++;
		}
		$html.= '</tr>';
		$csvArray[] = $csvArrayHeaderArray;


		// now go through all users and add to table, along with how many times they've done the question etc
		foreach ( $userArray as $userID => $userInfo )
		{

			$fullname = $userInfo['fullname'];
			$firstName = $userInfo['firstName'];
			$surname = $userInfo['surname'];
			$username = $userInfo['username'];
			$role = $userInfo['role'];


			$myFinalisedProjects = get_user_meta($userID, 'ekFinalisedProjects', true);


			$UKdate = '-';


			$isFinalised=false;
			if(is_array($myFinalisedProjects) )
			{
				if(isset($myFinalisedProjects[$projectTypeID]) )
				{
					if(isset($myFinalisedProjects[$projectTypeID]))
					{
						$isFinalised = true;
						$finalisedDate = $myFinalisedProjects[$projectTypeID];
						$UKdate = ek_projects_utils::getUKdate($finalisedDate);
					}

				}
			}



			$html.= '<tr>';
			$html.= '<td>';
			if($isFinalised==true)
			{
				$html.='<label for="for_'.$username.'">';
				$html.='<input type="checkbox" name="unfinalise" id="for_'.$username.'" value="'.$userID.'">';
			}
			$html.=$fullname;
			if($isFinalised==true)
			{
				$html.='</label>';
			}

			$html.= '</td>';
			$html.= '<td>'.$username.'</td>';
			$html.= '<td>'.$role.'</td>';
			$html.= '<td>'.$UKdate.'</td>';

			$tempCSVarray = array ($fullname, $username, $role, $UKdate);




			// Get current Basket Items
			$args = array(
				"projectTypeID"	=> $projectTypeID,
				"userID"	=> $userID,
			);


			$myProjectBasket= ek_projects_queries::getUserBasket($args);



			// Re KEY the values
			$myProjectBasket = array_values($myProjectBasket);



			$i=1;
			while ($i<=$maxItems)
			{


				$html.='<td>';
				$projectTitle = '-';
				if(isset($myProjectBasket[($i-1)]) )
				{
					$thisItemID = $myProjectBasket[($i-1)];
					$projectTitle = $myProjectLookupArray[$thisItemID];
				}


				$html.=$projectTitle;
				$html.='</td>';
				$tempCSVarray[] = $projectTitle;
				$i++;
			}


			$html.= '</tr>';
			$csvArray[] = $tempCSVarray;

		}

		$html.= '</table>';

		$html.="<script>
			function toggle(source) {
			checkboxes = document.getElementsByName('unfinalise');
			for(var i=0, n=checkboxes.length;i<n;i++) {
			checkboxes[i].checked = source.checked;
			}
			}

		</script>";


		if($CSV==true)
		{
			return $csvArray;
		}
		else
		{
			return $html;
		}



	}


    public static function finalise_choices($projectTypeID)
    {
        $html = '';

		$userID = get_current_user_id();

		// Get current Basket Items
		$args = array(
			"projectTypeID"	=> $projectTypeID,
			"userID"	=> $userID,
		);

        $html.='<a href="?">Back to project list</a><hr/>';


		$myProjectBasket= ek_projects_queries::getUserBasket($args);

        $html.= 'Here are your choices. Please order them below.';


        $i=1;

        $html.='<form action="?action=finalise-choices-confirm" method="post">';
        $html.='<div id="finalise-basket">';

        $hidden_string = '';
		foreach($myProjectBasket as $thisItemID)
		{

			$html.='<div class="projectBasketItem">';
			$projectTitle = get_the_title($thisItemID);
			$projectURL = '?projectID='.$thisItemID;
            $html.='<div class="project_select_number" data-id="'.$thisItemID.'">'.$i.'. </div>';
			$html.= '<div class="projectBasketItemTitle">';
			$html.='<a href="'.$projectURL.'">'.$projectTitle.'</a></div>';

			$html.='</div>';// end of project item div
            $i++;

            $hidden_string.=$thisItemID.',';
		}
        $html.='<div>';


        $html.='<input type="submit" class="imperial-button" value="Finalise these choices">';


        $html.='<input type="hidden" value="'.$hidden_string.'" name="item_list" id="item_list">';
        $html.='<input type="hidden" value="'.$projectTypeID.'" name="projectTypeID" id="projectTypeID">';
        $html.='</form>';

        $html.='<script>


         jQuery(function () {
             jQuery("#finalise-basket").sortable({
                 update: function () {
                     var item_string = "";
                     jQuery("#finalise-basket .project_select_number").each(function (i) {
                         var this_id = jQuery(this).attr("data-id");
                         var humanNum = i + 1;
                         jQuery(this).html(humanNum + ".");
                         item_string = item_string +this_id + ",";
                     });
                     console.log("FINAL = "+item_string);

                     jQuery("#item_list").val(item_string);

                 }
             });
             jQuery( "#finalise-basket" ).disableSelection();

         });



        </script>';


        return $html;


    }

    public static function finalise_choices_confirm($projectTypeID)
    {
        $html = '';

        $userID = get_current_user_id();

        // Get current Basket Items
        $args = array(
            "projectTypeID"	=> $projectTypeID,
            "userID"	=> $userID,
        );

        $html.='<a href="?">Back to project list</a><hr/>';


        $myProjectBasket= ek_projects_queries::getUserBasket($args);

        $html.= 'Thank you! Your choices have been finalised.';


        $i=1;

        $html.='<div id="finalise-basket">';
        foreach($myProjectBasket as $thisItemID)
        {
            $html.='<div class="projectBasketItem bassortable">';
            $projectTitle = get_the_title($thisItemID);
            $projectURL = '?projectID='.$thisItemID;
            $html.='<div class="project_select_number">'.$i.'. </div>';
            $html.= '<div class="projectBasketItemTitle">';
            $html.='<a href="'.$projectURL.'">'.$projectTitle.'</a></div>';

            $html.='</div>';// end of project item div
            $i++;
        }
        $html.='<div>';




        return $html;


    }





}


?>
