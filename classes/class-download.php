<?php


if ( isset($_GET['myAction'] ) )
{
	
	$myAction = $_GET['myAction'];
	
	switch ($myAction)
	{
		
		case "projectDataDownload":


				// Handle CSV Export for data
				add_action( 'init', array('ekProjectPickerDownloadCSV', 'downloadCSV') );
			
		break;		
		
		
	}


	
}


class ekProjectPickerDownloadCSV
{

	
	
	public static function downloadCSV()
	{
		// Check for current user privileges 
		if(!current_user_can('delete_others_pages') )
		{		
			return;
		}



		$postID = $_GET['ID'];
		
		$postTitle = get_the_title($postID);
	
		
		$CSV_array = ek_pp_draw::drawSubmissions($postID, true);
		
		$fileNameStart = preg_replace("/[^A-Za-z0-9 ]/", '', $postTitle).'-'.$postID;
		
		
		$fileName = $fileNameStart.'.csv';
		
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		ob_end_clean();		 // Remove unwanted blank spaces / line breaks
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$fileName}");
		header("Expires: 0");
		header("Pragma: public");
		
		$fh = @fopen( 'php://output', 'w' );
		
		foreach ($CSV_array as $fields) {
			fputcsv($fh, $fields);
		}				
		
		// Close the file
		fclose($fh);
		// Make sure nothing else is sent, our file is done
		die();
	}	
	
	
	


	
	
} //Close class
?>