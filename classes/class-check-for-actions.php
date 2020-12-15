<?php


$ekPP_check_for_actions = new ekPP_check_for_actions();

class ekPP_check_for_actions
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
		//Add Front End Jquery and CSS
		add_action( 'init', array( $this, 'check_for_actions' ) );
	}


    function check_for_actions()
    {

        if(isset($_GET['action']) )
        {

            $action = $_GET['action'];
            $site_url = get_site_url();


            switch ($action)
            {
                case "update_project_meta_options":

                    ek_pp_actions::update_project_meta_options();
                    $id = $_GET['ID'];

                    wp_redirect( $site_url.'/wp-admin/edit.php?page=ek_project_meta&ID='.$id);
                    exit;

                break;


            }

        }


    }

}
