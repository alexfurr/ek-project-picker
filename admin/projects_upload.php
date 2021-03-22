<h1>Upload Projects CSV</h1>
<?php

$projectTypeID = $_GET['ID'];

?>
<form name="csvUploadForm" action="?post_type=ek_project&page=imperial-projects-csv-upload&ID=<?php echo $projectTypeID;?>&action=CSVUpload"  method="post" enctype="multipart/form-data">
<input type="file" name="csvFile" size="20"/><br/>
<input type="submit" value="Upload" name="submit" class="button-primary" />
<?php
// Add nonce
wp_nonce_field('CSV_UploadNonce');
?>

</form>


<?php
// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['action'] ) )
{



    $insert_count = 0;
    $update_count = 0;

	// Check the nonce before proceeding;
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
	if (wp_verify_nonce($retrieved_nonce, 'CSV_UploadNonce' ) )
	{


		$myAction = $_GET['action'];
		switch ($myAction)
		{
			case "CSVUpload":


                $temp_path = imperialNetworkUtils::getTempUploadDir();

				$newFilename = $temp_path.'/temp_pp_import.csv';

                $insert_count = 0;
                $update_count = 0;

				if(isset($_FILES['csvFile']['tmp_name']))
				{

					move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);

                    $data_array = imperialNetworkUtils::getCSVdataAsArray($newFilename);




                    $line_no = 1;

                    // Which columns have keywords
                    $key_word_cols = array();
                    $col_name_lookup = array();

                    foreach ($data_array as $this_row)
					{


                        if($line_no==1)
                        {
                            $col_name_lookup = $this_row;
                            $col_num = 0;
                            foreach ($this_row as $col_name)
                            {
                                if (strpos(strtolower($col_name), 'keyword') !== false) {
                                    $key_word_cols[] = $col_num;
                                }
                                $col_num++;
                            }
                        }
                        else
                        {


    						$project_code 	= $projectTypeID.'_'.$this_row[0];
    						$project_title	= $this_row[1];
                            $project_summary = $this_row[2];
                            $supervisorName	 = $this_row[3];
                            $supervisorEmail  = $this_row[4];
                            $department		  = $this_row[5];
                            $projectType	= $this_row[6];
                            $wet_dry		= $this_row[7];


                            $project_title = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $project_title);
                            $project_summary = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $project_summary);

                            // Create blank array for the keywords
                            $this_keywords = array();

                            // Create array of everytthing else
                            $other_meta = array();

                            // Now go through the other data columns
                            // Add the keywords to a single one and add the rest to a single array

                            $this_col_number =0;
                            foreach ($this_row as $col_data)
                            {
                                if($this_col_number>7) // All the other meta data
                                {
                                    // check to see if its a keyword
                                    if (in_array($this_col_number, $key_word_cols) )
                                    {
                                        $this_keywords[] = $col_data;
                                    }
                                    else
                                    {
                                        $other_meta[][$col_name_lookup[$this_col_number]] = $col_data;
                                    }
                                }
                                $this_col_number++;
                            }

                            // See if the project already exists based on the project code
                            $args = array(
                                'post_type'    => 'ek_project',
                                'meta_key'     => 'project_code',
                                'meta_value'   => $project_code, // change to how "event date" is stored
                                'meta_compare' => '=',
                            );
                            $check_query = new WP_Query($args);


                            if ( $check_query->have_posts() )
                            {
                                while ( $check_query->have_posts() )
                                {
                                    $check_query->the_post();
                                    $post_id = get_the_ID();
                                }

                                // UPDATE THE POST META AND TITLE
                                $my_post = array(
                                  'ID'           => $post_id,
                                  'post_title'   => $project_title,
                              );

                            // Update the post into the database
                              wp_update_post( $my_post );
                              $update_count++;

                            } else {
                                // no posts found
                                // INSERT
        						$my_post = array(
        							'post_title'    => $project_title,
        							'post_content'  => $project_summary,
        							'post_status'   => 'publish',
        							'post_author'   => 1,
        							'post_type'		=> 'ek_project',
        						);

        						// Insert the post into the database.
        						$post_id = wp_insert_post( $my_post );
                                $insert_count++;

                            }


                            update_post_meta( $post_id, 'project_code', $project_code );
    						update_post_meta( $post_id, 'supervisorEmail', $supervisorEmail );
    						update_post_meta( $post_id, 'projectDept', $department );
                            update_post_meta( $post_id, 'supervisorName', $supervisorName );
                            update_post_meta( $post_id, 'project_type', $projectType );
                            update_post_meta( $post_id, 'wet_dry', $wet_dry );



                            // Add the key words and the project meta
                            update_post_meta( $post_id, 'keywords', $this_keywords );
                            update_post_meta( $post_id, 'project_meta', $other_meta );

                            // Add the project parent
                            wp_update_post(
                                array(
                                    'ID' => $post_id,
                                    'post_parent' => $projectTypeID
                                )
                            );


                        }
                        $line_no++;



					}

				} // End if file type is CSV
				// Now delete the temp file
				unlink ($newFilename);
			} // End of nonce check
		}//

        echo $insert_count.' projects created.<br/>';
        echo $update_count.' projects updated.<br/>';

        echo '<hr/>';
        echo '<a href="edit.php?post_type=ek_project&projectTypeID='.$projectTypeID.'">View projects</a>';

	} // End is action


?>
