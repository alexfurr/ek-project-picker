<?php
$ek_projects_cpt = new ek_projects_cpt();

class ek_projects_cpt
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


		// Remove and add columns in the projects table
		add_filter( 'manage_ek_project_posts_columns', array( $this, 'my_custom_post_columns_projects' ), 10, 2 );
		add_action('manage_ek_project_posts_custom_column', array($this, 'customColumnContent_projects'), 10, 2);


		// Add metaboxes to the projects page to save the parent ID
		add_action( 'add_meta_boxes_ek_project', array( $this, 'addMetaBoxes' ));

		// Save additional  meta for the custom post
		add_action( 'save_post', array($this, 'savePostMeta' ), 10 );

		// Add back button to the projects page
		add_action( 'all_admin_notices', array($this, 'addBackButton_on_editPage' ) );

		// Modify project list main query to only show items  with correct  parent
		add_action( 'pre_get_posts', array($this, 'modify_admin_list_query' ) );


		add_action('admin_head', array ($this, 'custom_js_to_head') );


	}




	function create_CPTs ()
	{


		$cptName = 'Project';

		//Sessions
		$labels = array(
			'name'               =>  $cptName.'s',
			'singular_name'      =>  $cptName,
			'menu_name'          =>  $cptName.'s',
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
			'show_in_menu'       => false,
			'query_var'          => true,
			//'rewrite' => array( 'slug' => 'projects' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'revisions', 'thumbnail'  )

		);



		register_post_type( 'ek_project', $args );


		// Add tags to this
		$labels = array(
			'name'                       => _x( 'Project Tags', 'taxonomy general name', 'textdomain' ),
			'singular_name'              => _x( 'Tags', 'taxonomy singular name', 'textdomain' ),
			'search_items'               => __( 'Search Tags', 'textdomain' ),
			'popular_items'              => __( 'Popular Tags', 'textdomain' ),
			'all_items'                  => __( 'All Tags', 'textdomain' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tags', 'textdomain' ),
			'update_item'                => __( 'Update Tags', 'textdomain' ),
			'add_new_item'               => __( 'Add New Tags', 'textdomain' ),
			'new_item_name'              => __( 'New Tags Name', 'textdomain' ),
			'separate_items_with_commas' => __( 'Separate Tags with commas', 'textdomain' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'textdomain' ),
			'choose_from_most_used'      => __( 'Choose from the most used Tags', 'textdomain' ),
			'not_found'                  => __( 'No Tags found.', 'textdomain' ),
			'menu_name'                  => __( 'Tags', 'textdomain' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => false,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'project-tags' ),
		);

		register_taxonomy( 'ek-project-tags', 'ek_project', $args );

	}




	function drawProjectsUploadPage()
	{
		include_once( PP_PATH . '/admin/projects_upload.php' );
	}




	// Remove Date Columns on projects
	function my_custom_post_columns_projects( $columns  )
	{

	  	// Remove Date
		unset(
		$columns['date']
		);
		// Remove Checkbox



	//	$columns['pot_name'] = 'Pot Name';
		//$columns['projectTags'] = 'Project Tags';

        //	$columns['projectType'] = 'Project Type';


        $columns['project_code'] = 'Project Code';

        $columns['project_type'] = 'Project type';
		return $columns;
	}

	// Content of the custom columns for Topics Page
	function customColumnContent_projects($column_name, $post_ID)
	{

		switch ($column_name)
		{

            case "projectType":
                $project_type = get_post_meta($post_ID, "project_type", true);
                echo $project_type;
            break;

            case "project_code":
                $project_code = get_post_meta($post_ID, "project_code", true);
                echo $project_code;
            break;






			case "projectType":

				$projectTypeID = wp_get_post_parent_id( $post_ID );
				$projectTypeName = get_the_title($projectTypeID);

				echo '<a href="edit.php?post_type=ek_project&projectTypeID='.$projectTypeID.'">'.$projectTypeName.'</a>';



			break;
			case "projectTags":


				$terms = get_the_terms( $post_ID, 'ek-project-tags' );

				if(!is_array($terms) )
				{
					echo '<span class="greyText">No tags found</span>';
				}
				else
				{
					foreach ( $terms as $term ) {
						echo $term->name.'<br/>';
					}
				}



			break;





		}
	}




	// Register the metaboxes on  CPT
	function  addMetaBoxes()
	{

		global $post;


		//Question Meta Metabox
		$id 			= 'project_meta';
		$title 			= 'Project Information';
		$drawCallback 	= array( $this, 'drawProjectMetaBox' );
		$screen 		= 'ek_project';
		$context 		= 'normal';
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

        /*
        //Question Meta Metabox
        $id 			= 'project_meta_options';
        $title 			= 'Project Meta';
        $drawCallback 	= array( $this, 'drawProjectMetaOptionsBox' );
        $screen 		= 'ek_project';
        $context 		= 'normal';
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
        */
	}

	function drawProjectMetaBox($post,$callbackArgs)
    {

        $project_meta = get_post_meta($post->ID);


        // hiiden input for project type ID
        if(isset($_GET['projectTypeID']) )
        {
            $projectTypeID = $_GET['projectTypeID'];
        }
        else
        {
            $projectTypeID = wp_get_post_parent_id( $post->ID );
        }


        echo '$projectTypeID = '.$projectTypeID;

        echo '<input type="hidden" value="'.$projectTypeID.'" name="projectTypeID">';


		wp_nonce_field( 'save_ek_project_metabox_nonce', 'ek_project_metabox_nonce' );


	}

    function drawProjectMetaBox_old($post,$callbackArgs)
    {
        $projectLeadName = '';
        $projectLeadEmail='';

        $project_meta = get_post_meta($post->ID);


        printArray($project_meta);

        /*
        // Project Leads
        $projectLeadsArray = get_post_meta($post->ID, "projectLeadsArray", true);

        if(is_array($projectLeadsArray))
        {
            $projectLeadName = $projectLeadsArray[0]['name'];
            $projectLeadEmail = $projectLeadsArray[0]['email'];
        }

        echo '<label for="projectLeadName">Project Lead</label><br/>';
        echo '<input type="text" value="'.$projectLeadName.'" name="projectLeadName">';
        echo '<hr/>';
        echo '<label for="projectLeadName">Project Lead Email</label><br/>';
        echo '<input type="text" value="'.$projectLeadEmail.'" name="projectLeadEmail">';
        */








        wp_nonce_field( 'save_ek_project_metabox_nonce', 'ek_project_metabox_nonce' );




    }


    public static function drawProjectMetaOptionsBox($post,$callbackArgs)
    {

        // hiiden input for project type ID
		if(isset($_GET['projectTypeID']) )
		{
			$projectTypeID = $_GET['projectTypeID'];
		}
		else
		{
			$projectTypeID = wp_get_post_parent_id( $post->ID );
		}

        $meta_options = get_post_meta($projectTypeID, 'pp_custom_meta', true);

        if(!is_array($meta_options) )
        {

            $meta_options = array();
        }

        foreach ($meta_options as $this_id => $these_options)
        {
            $meta_name = $these_options['meta_name'];

            if(!$meta_name)
            {
                continue;

            }

            echo '<div class="project_meta_item">';

            $meta_type = $these_options['meta_type'];
            $meta_options = $these_options['meta_options'];
            $this_value = trim(get_post_meta($post->ID, 'pp_meta_value_'.$this_id, true));


            echo '<label for="pp_meta_value_'.$this_id.'">'.$meta_name.'</label><br/>';

            switch ($meta_type)
            {

                case "text";
                    echo '<textarea name="pp_meta_value_'.$this_id.'" id="pp_meta_value_'.$this_id.'">'.$this_value.'</textarea>';
                break;

                case "dropdown";
                    echo '<select name="pp_meta_value_'.$this_id.'">';
                    foreach ($meta_options as $this_option)
                    {
                        $this_option = trim($this_option);
                        echo '<option value="'.$this_option.'"';
                        if($this_value==$this_option){echo ' selected ';}
                        echo '>';
                        echo $this_option;
                        echo '</option>';
                    }

                    echo '</select>';
                break;


            }
            echo '</div>';
        }



    }

	// Save metabox data on edit slide
	function savePostMeta ( $postID )
	{
		global $post_type;

		if($post_type=="ek_project")
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
			// Gett the pot ID (parent) and set it as the parent ID
			$projectTypeID = $_POST['projectTypeID'];



			//If calling wp_update_post, unhook this function so it doesn't loop infinitely
			remove_action('save_post', array($this, 'savePostMeta') );


			wp_update_post(
				array(
					'ID' => $postID,
					'post_parent' => $projectTypeID
				)
			);

			add_action('save_post', array($this, 'savePostMeta') );

            /*
			// Update the post meta
			$projectLeadName = $_POST['projectLeadName'];
			$projectLeadEmail=$_POST['projectLeadEmail'];

			$projectLeadsArray = array();

			$projectLeadsArray[0]['name'] = $projectLeadName;
			$projectLeadsArray[0]['email'] = $projectLeadEmail;

			update_post_meta( $postID, 'projectLeadsArray', $projectLeadsArray );

            */


            // Update any custom options

            /*
            $i=1;
            while ($i<=5)
            {
                $check_name = 'pp_meta_value_'.$i;
                if(isset($_POST[$check_name]) )
                {
                    $check_value = $_POST[$check_name];
                }
                update_post_meta( $postID, $check_name, $check_value );
                $i++;
            }
            */

		}

	}


	function addBackButton_on_editPage()
	{

		global $post_type, $pagenow, $post;
		$projectTypeID = '';

		// Get the Parnet ID
		// Get the Parnet ID
		if(isset($_GET['projectTypeID']))
		{
			$projectTypeID = $_GET['projectTypeID'];
		}


		if(($pagenow == "post.php" || $pagenow=="post-new.php") && $post_type=="ek_project")
		{

			if($projectTypeID=="")
			{
				$projectID = $post->ID;
				$projectTypeID = wp_get_post_parent_id( $projectID );

				$href = get_admin_url().'edit.php?post_type=ek_project&projectTypeID='.$projectTypeID;
				echo '<br/><br/> <a href="'.$href.'" class="button-secondary"><i class="fas fa-chevron-left"></i> Back to projects</a>';


			}

		}
	}


	// Add new Button to add the stuff
	function custom_js_to_head()
	{

		global $post_type, $pagenow, $post;

		if($post_type=="ek_project" && ($pagenow=="edit.php" || $pagenow=="post.php") )
		{


			if(isset($_GET['projectTypeID']) )
			{
				$projectTypeID=$_GET['projectTypeID'];
			}
			else
			{
				$projectTypeID = wp_get_post_parent_id( $post->ID );
			}


			?>
			<script>
			jQuery(function(){
				jQuery("body.post-type-ek_project .wrap h1").append('<a href="post-new.php?post_type=ek_project&projectTypeID=<?php echo $projectTypeID;?>" class="page-title-action">Add new project</a>');
			});
			</script>


			<style>
			.wrap a:nth-child(2) {
				display:none;

				}

			</style>
			<?php
		}
	}



	// Modify the query for admin list items to only show items with correct parent
	function modify_admin_list_query( $query )
	{
		// Check if on frontend and main query is modified
		if(  is_admin() && $query->is_main_query() && $query->query_vars['post_type'] == 'ek_project' )
		{

			$projectTypeID = '';
			if(isset($_GET['projectTypeID']) )
			{
				$projectTypeID = $_GET['projectTypeID'];
			}
			$query->set('post_parent', $projectTypeID);
			return $query;
		}

	}



}


?>
