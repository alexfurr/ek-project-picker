<?php
$ek_project_types_cpt = new ek_project_types_cpt();

class ek_project_types_cpt
{

	//~~~~~
	function __construct ()
	{
		$this->addWPActions();
	}


/*	---------------------------
	PRIMARY HOOKS INTO WP
	--------------------------- */
	function addWPActions ()
	{
		add_action( 'init',  array( $this, 'create_CPTs' ) );
		add_action( 'admin_menu', array( $this, 'create_AdminPages' ));

		// Remove and add columns in the project type table
		add_filter( 'manage_ek_project_type_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );
		add_action('manage_ek_project_type_posts_custom_column', array($this, 'customColumnContent'), 10, 2);


		// Add metaboxes to the projects page to save the parent ID
		add_action( 'add_meta_boxes_ek_project_type', array( $this, 'addMetaBoxes' ));

		// Save additional  meta for the custom post
		add_action( 'save_post', array($this, 'savePostMeta' ), 10 );


	}




	function create_CPTs ()
	{

		$cptName = 'Project Type';

		//Sessions
		$labels = array(
			'name'               =>  'Project Types',
			'singular_name'      =>  $cptName,
			'menu_name'          =>  'Project Picker',
			'name_admin_bar'     =>  $cptName.'s',
			'add_new'            =>  'Add New '.$cptName,
			'add_new_item'       =>  'Add New '.$cptName,
			'new_item'           =>  'New '.$cptName,
			'edit_item'          =>  'Edit '.$cptName,
			'view_item'          => 'View '.$cptName.'s',
			'all_items'          => 'All '.$cptName.'s',
			'search_items'       => 'Search '.$cptName.'s',
			'parent_item_colon'  => '',
			'not_found'          => 'No '.$cptName.'s found.',
			'not_found_in_trash' => 'No '.$cptName.'s found in Trash.'
		);

		$args = array(
			'menu_icon' => 'dashicons-lightbulb',
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_nav_menus'	 => false,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite' => array( 'slug' => 'project-types' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'revisions', 'thumbnail'  )

		);



		register_post_type( 'ek_project_type', $args );





	}

    function create_AdminPages()
	{

		/* Create Admin Pages */

		/* CSV Edit Page */
		$parentSlug = "edit.php?post_type=ek_project";
		$page_title="Upload Projects";
		$menu_title="Upload Projects";
		$menu_slug="imperial-projects-csv-upload";
		$function=  array( $this, 'drawProjectsUploadPage' );
		$myCapability = "delete_others_pages";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

		/* Project Meta Page */
		$parentSlug = "";
		$page_title="Project Meta";
		$menu_title="Project Meta";
		$menu_slug="imperial-projects-meta";
		$function=  array( $this, 'drawProjectsMetaPage' );
		$myCapability = "delete_others_pages";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);


	}


	function drawProjectsUploadPage()
	{
		include_once( PP_PATH . '/admin/projects_upload.php' );
	}

    function drawProjectsMetaPage()
    {
        include_once( PP_PATH . '/admin/projects_meta.php' );
    }


	// Remove Date Columns on projects
	function my_custom_post_columns( $columns  )
	{

	  	// Remove Date
		unset(
		$columns['date']
		);
		// Remove Checkbox
		unset(
		$columns['cb']
		);


	//	$columns['pot_name'] = 'Pot Name';
		$columns['add_project'] = '';
		$columns['project_overview'] = 'Projects';
        $columns['project_meta'] = 'Project Meta';

	  	$columns['shortcode'] = 'Shortcode';
        $columns['data'] = 'Student Choices';
		return $columns;
	}


	// Content of the custom columns for Topics Page
	function customColumnContent($column_name, $post_ID)
	{

		switch ($column_name)
		{

			case "project_overview":


				$args = array("projectTypeID" => $post_ID);
				$myProjects = ek_projects_queries::getProjectTypeProjects($args);

				$projectCount = count($myProjects);

				echo $projectCount.' projects found';




			break;

			case "add_project":
				$newProjectURL = get_admin_url().'post-new.php?post_type=ek_project&projectTypeID='.$post_ID;
				$projectListURL = get_admin_url().'edit.php?post_type=ek_project&projectTypeID='.$post_ID;

				echo '<a href="'.$projectListURL.'" class="button-secondary">View Projects</a>';
				echo ' <a href="'.$newProjectURL.'" class="button-primary">Add Project</a>';
			break;

			case "shortcode":

				echo '[imperial-projects id='.$post_ID.']';

			break;

			case "data":

				echo '<a href="edit.php?page=ek_project_data&ID='.$post_ID.'" class="button-secondary">Student Choices</a>';

			break;

            case "project_meta":

                echo '<a href="edit.php?page=ek_project_meta&ID='.$post_ID.'" class="button-secondary">Project Meta</a>';

            break;


		}
	}

	// Register the metaboxes on  CPT
	function  addMetaBoxes()
	{

		global $post;

		//Question Meta Metabox
		$id 			= 'project_type_meta';
		$title 			= 'Project Settings';
		$drawCallback 	= array( $this, 'drawProjectMetaBox' );
		$screen 		= 'ek_project_type';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();

		add_meta_box(
			$id,
			$title,
			$drawCallback,
			$screen,
			$context,
			$priority,
			$callbackArgs
		);
	}

	function drawProjectMetaBox($post,$callbackArgs)
	{

		$minItems = get_post_meta($post->ID, "minItems", true);
		if($minItems=="")
		{
			$minItems=1;
		}


		$maxItems = get_post_meta($post->ID, "maxItems", true);
		if($maxItems=="")
		{
			$maxItems=3;
		}



		echo '<b>Finalise Options</b><br/>';

		echo '<label for="minItems">Min number of basket items</label><br/>';
		echo '<input type="textbox" value="'.$minItems.'" name="minItems" size="2"><hr/>';



		echo '<label for="maxItems">Max number of basket items</label><br/>';
		echo '<input type="textbox" value="'.$maxItems.'" name="maxItems" size="2">';
		wp_nonce_field( 'save_ek_project_metabox_nonce', 'ek_project_metabox_nonce' );




	}

	// Save metabox data on edit slide
	function savePostMeta ( $postID )
	{
		global $post_type;

		if($post_type=="ek_project_type")
		{


			// Check if nonce is set.
			if ( ! isset( $_POST['ek_project_metabox_nonce'] ) ) {
				return;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['ek_project_metabox_nonce'], 'save_ek_project_metabox_nonce' ) ) {
				return;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( ! current_user_can( 'edit_post', $postID ) ) {
				return;
			}

			// check if there was a multisite switch before
			if ( is_multisite() && ms_is_switched() ) {
				return $postID;
			}


			$maxItems = $_POST['maxItems'];
			$minItems = $_POST['minItems'];
			update_post_meta( $postID, 'maxItems', $maxItems );
			update_post_meta( $postID, 'minItems', $minItems );



		}

	}









}


?>
