var project_picker_js = 
{
	add_listeners: function () {
		
		// Add listenres to the basket
		jQuery('#imperialBasketDiv').on( 'click', function ( e ) {
			
			// Get the element that was clicked			
			var elementID = event.target.id;			
			
			console.log(elementID);
			
			// Toggle the Delete Check button
			if(elementID.includes("remove_")==true)
			{
				// Get the ID of the button, turn to array with _ as split and second element is the quiz IDa
				var projectID = elementID.split("_")[1];		
				var toggleID = "#project-delete-confirm_"+projectID;				
				jQuery( toggleID ).toggle( 100 );

				return;				
			}
			

			// Delete an item
			if(elementID.includes("remove-confirm")==true)
			{
				// Get the ID of the button, turn to array with _ as split and second element is the quiz IDa
				var projectID = elementID.split("_")[1];
				console.log("Remove The Item "+projectID);
				removeBasketItem(projectID);

				return;				
			}	

			// Toggle the Finalise confirm  button
			if(elementID=="finaliseProjectsButton")
			{
				jQuery('#finaliseConfirmDiv').toggle( "fast" );
				return;				
			}
			
			if(elementID.includes("finaliseProjectsConfirm_")==true)
			{
				var projectTypeID = elementID.split("_")[1];
				finaliseBasket(projectTypeID);	

				return;	
			}
			
			
		});	
		
		
		
		
		
		// Add listeners to the add to basket button wrap
		jQuery('#basket-add-button-div').on( 'click', function ( e )
		{
			var elementID = event.target.id;	
			
			// Add the project to the basket
			if(elementID.includes("addItem_")==true)
			{
				// Get the ID of the button, turn to array with _ as split and second element is the quiz IDa
				var projectID = elementID.split("_")[1];
				console.log("Add The Item "+projectID);
				saveBasketItem(projectID);
				return;				
			}				

		});
		
		

		
		
	},
	

	// Setup the listeners
	init: function () {		
		project_picker_js.add_listeners();
	}
	
};



jQuery( document ).ready( function ()
{
	// Initialise the responses	
	project_picker_js.init();
	

	
	
});



// Get querystring value by name if it exsits
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};