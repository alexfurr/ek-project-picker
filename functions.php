<?php



$ekPP = new ekPP();

class ekPP
{
	var $version = '0.1';


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
		//Add Front End Jquery and CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'frontendEnqueues' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueues' ));

		// Setup shortcodes
		add_shortcode('imperial-projects', array('ek_pp_draw','drawProjectsShortcode'));

		add_action( 'admin_menu', array( $this, 'createAdminMenu' ) );

		//add_filter('the_content', array($this, 'hideTheContentFromProjects') ,11);

        // Check for actions
        add_action( 'init', array( $this, 'check_for_actions' ) );






	}

	function adminEnqueues()
	{
		wp_enqueue_script('jquery');
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-widget' );
        wp_enqueue_script( 'jquery-ui-mouse' );
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );
        wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		/* add jquery ui datepicker and theme */
		// get the jquery ui object
		global $wp_scripts;
		$queryui = $wp_scripts->query('jquery-ui-core');

		// load the jquery ui theme
		//$url = "https://ajax.googleapis.com/ajax/libs/jqueryui/".$queryui->ver."/themes/smoothness/jquery-ui.css";
		//wp_enqueue_style('jquery-ui-smoothness', $url, false, null);


		// Font Awesome CSS
//		wp_enqueue_style( 'ek-font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
		//wp_register_script( 'ek-font-awesome', '//use.fontawesome.com/releases/v5.0.4/js/all.js' );
		//wp_enqueue_script( 'ek-font-awesome' );

		add_thickbox();
        wp_enqueue_style( 'ek-pp-admin', PP_URL .'/css/admin.css' );



	}

	function frontendEnqueues ()
	{
		//Scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
		// Custom Styles
		wp_enqueue_style( 'ek-pp-css', PP_URL . '/styles.css' );

		// Font Awesome CSS
		//wp_register_script( 'ek-font-awesome', '//use.fontawesome.com/releases/v5.0.4/js/all.js' );
		//wp_enqueue_script( 'ek-font-awesome' );

		// Data tables
		wp_enqueue_script('ek_datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array( 'jquery' ) );
		wp_enqueue_style( 'ek-datatables-css-js', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' );



		// Register listeners
		wp_enqueue_script('ek_pp_js', PP_URL.'/js/picker.js', array( 'jquery' ) );

		// Font Awesome CSS
		wp_enqueue_style( 'ek-font-awesome', '//use.fontawesome.com/releases/v5.2.0/css/all.css' );



		// Register Ajax script for front end
		wp_enqueue_script('ek_pp_basket_ajax', PP_URL.'/js/basket_ajax.js', array( 'jquery' ) ); # AJAX JS for saving basket

		//Localise the JS file
		$params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'ajax_nonce' => wp_create_nonce('saveBasket_ajax_nonce'),
		);

		wp_localize_script( 'ek_pp_basket_ajax', 'submitBasket_params', $params );




	}


	function hideTheContentFromProjects($content)
	{
		$post_type = get_post_type();

		// If the query string is set to projectID then don't show the content page
		if(isset($_GET['projectID']))
		{
			$content = '';
		}
		return $content;
	}

	function createAdminMenu ()
	{
		$parentMenuSlug= '';
		/* Network Admin Users */
		$page_title="Project Data";
		$menu_title="Project Data";
		$menu_slug="ek_project_data";
		$function=  array( $this, 'drawDataDownloadPage' );
		$myCapability = "delete_others_pages";
		add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

        $parentMenuSlug= '';
        /* Network Admin Users */
        $page_title="Project Meta";
        $menu_title="Project Meta";
        $menu_slug="ek_project_meta";
        $function=  array( $this, 'drawProjectMetaAdmin' );
        $myCapability = "delete_others_pages";
        add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

        $parentMenuSlug= '';
        /* Network Admin Users */
        $page_title="Project Meta";
        $menu_title="Project Meta";
        $menu_slug="imperial-projects-csv-upload";
        $function=  array( $this, 'drawCSV_upload' );
        $myCapability = "delete_others_pages";
        add_submenu_page($parentMenuSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

	}


	function drawDataDownloadPage()
	{
		include_once( dirname(__FILE__) . '/admin/data_download.php');
	}


	function drawCSV_upload()
	{
		include_once( dirname(__FILE__) . '/admin/projects_upload.php');
	}





    function check_for_actions()
    {
        if(isset($_GET['action']) )
        {

            $action = $_GET['action'];

            switch ($action)
            {


                case "finalise-choices-confirm":

                    $item_list = $_POST['item_list'];
                    $projectTypeID = $_POST['projectTypeID'];
                    $basketArray = explode(',', $item_list);
                    // Remove blanks
                    $basketArray = array_filter($basketArray);
                    $myProjectBasket = array();
                    $myProjectBasket[$projectTypeID] = $basketArray;

                    $userID = get_current_user_id();
                    update_user_meta( $userID, "ekProjectBasket", $myProjectBasket );

                    // Also uodate the fact its finaLISED
                    $currentDate = date('Y-m-d H:i:s');
                    $myFinalisedProjects[$projectTypeID] = $currentDate;
                    update_user_meta( $userID, "ekFinalisedProjects", $myFinalisedProjects );

                    $url = "?view=finalise-confirmed";
                    wp_redirect( $url );


                    exit;
                break;


            }


        }

    }

}


?>
