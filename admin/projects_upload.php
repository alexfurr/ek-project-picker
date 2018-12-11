<h1>Upload Projects CSV</h1>

<form name="csvUploadForm" action="?post_type=ek_project&page=imperial-projects-csv-upload&action=CSVUpload"  method="post" enctype="multipart/form-data">
Upload your placement list as a CSV file with the following columns:<br/>
ID, Project Name, Supervisor Name, Supervisor Email, Project Info, Department, Division<br/>
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
	
	
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
	if (wp_verify_nonce($retrieved_nonce, 'CSV_UploadNonce' ) )
	{
	
	
		$myAction = $_GET['action'];
		switch ($myAction)
		{
			case "CSVUpload":
			
				$newFilename = dirname(__FILE__).'/tempImport.csv';
				
				if(isset($_FILES['csvFile']['tmp_name']))
				{
					
					move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);
					
					// Go through the CSV stuff
					ini_set('auto_detect_line_endings',1);
					$handle = fopen($newFilename, 'r');

					while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
					{

						$projectID 			= $data[0];
						$supervisorTitle	= $data[1];
						$supervisorName		= $data[2];
						$projectName		= $data[3];
						$supervisorEmail	= $data[4];
						$projectDept		= $data[5];
						$projectSection		= $data[6];
						
													
						// Gather post data.
						$my_post = array(
							'post_title'    => $projectName,
							//'post_content'  => $projectInfo,
							'post_status'   => 'publish',
							'post_author'   => 1,
							'post_type'		=> 'ek_project'						
						);
						 
						// Insert the post into the database.
						$post_id = wp_insert_post( $my_post );

						update_post_meta( $post_id, 'supervisorTitle', $supervisorTitle );
						update_post_meta( $post_id, 'supervisorEmail', $supervisorEmail );
						update_post_meta( $post_id, 'projectDept', $projectDept );
						update_post_meta( $post_id, 'projectSection', $projectSection );
						update_post_meta( $post_id, 'supervisorName', $supervisorName );
						
						echo '<hr/>';
						
					}
					
				} // End if file type is CSV
				// Now delete the temp file
				unlink ($newFilename);	
			} // End of nonce check
		}// End if grouopsUpload case	
	} // End is action


?>