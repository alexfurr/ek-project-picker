<?php
/*
Plugin Name: Edukit Project Picker
Description: Create Projects for student choice
Version: 0.1
Author: Alex Furr
License: GPL
*/
define( 'PP_URL', plugins_url('ek-project-picker' , dirname( __FILE__ )) );
define( 'PP_PATH', plugin_dir_path(__FILE__) );

include_once( PP_PATH . 'functions.php' );
include_once( PP_PATH . 'classes/class-project-types_cpt.php' );
include_once( PP_PATH . 'classes/class-projects_cpt.php' );
include_once( PP_PATH . 'classes/class-draw.php' );
include_once( PP_PATH . 'classes/class-queries.php' );
include_once( PP_PATH . 'classes/class-ajax.php' );

//include_once( PP_PATH . 'classes/class_database.php' );

?>