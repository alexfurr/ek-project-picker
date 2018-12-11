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
		
		//add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueues' ));
		
		// Setup shortcodes
		add_shortcode('imperial-projects', array('ek_pp_draw','drawProjectsShortcode'));
		
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
		
		
		// Data tables
		wp_enqueue_script('ek_datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array( 'jquery' ) ); 
		wp_enqueue_style( 'ek-datatables-css-js', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' );

		
		
		

	}
	
	function frontendEnqueues ()
	{
		//Scripts
		wp_enqueue_script('jquery');

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
	
	

	
	
	

}







?>