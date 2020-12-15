<?php

class ek_pp_actions
{

    public static function update_project_meta_options()
    {

        $post_id = $_GET['ID'];
        $meta_array = array();

        foreach ($_POST as $KEY => $VALUE)
        {

            if (strpos($KEY, 'meta_') !== false)
            {

                $temp_array = explode('_', $KEY);

                // This ID
                $this_id = array_values(array_slice($temp_array, -1))[0];

                // Remove the last element of the array
                array_pop($temp_array);

                $this_var_name = implode("_", $temp_array);

                if($this_var_name=="meta_options")
                {
                    $VALUE = explode(PHP_EOL, $VALUE);
                    array_filter($VALUE); // Remove exmpty values
                }


                $meta_array[$this_id][$this_var_name] = $VALUE;
            }

        }


        // Add the missing array elements if not present
        foreach ($meta_array as $this_id => $temp_array)
        {
            if(!isset($temp_array['meta_show_in_table']) )
            {
                $meta_array[$this_id]['meta_show_in_table'] = false;
            }

        }

        // Update the meta

        update_post_meta($post_id, "pp_custom_meta", $meta_array);



    }
}


?>
