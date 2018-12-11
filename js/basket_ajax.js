// This handles the single question submission
function saveBasketItem(projectID)
{
	
	jQuery('#basket-add-save-feedback-div').removeClass( "ek-hidden" );
	jQuery('#basket-add-button-div').addClass( "ek-hidden" );


	console.log("Add this projectID = "+projectID);
	
	
	jQuery.ajax({
		type: 'POST',
		url: submitBasket_params.ajaxurl,
		data: {			
			"action"		: "addBasketItem",			
			"projectID"		: projectID,
			"security"		: submitBasket_params.ajax_nonce
		},
		success: function(data){
			
			jQuery('#basket-add-save-feedback-div').addClass( "ek-hidden" );
			document.getElementById("imperialBasketDiv").innerHTML = data;	
			jQuery('#basket-add-success-div').removeClass( "ek-hidden" );
			return;

			
			
		}
			
	});
	
	
	
	
}

// This handles the single question submission
function removeBasketItem(projectID)
{
	
	var thisPageProjectID = getUrlParameter('projectID');		

	
	jQuery.ajax({
		type: 'POST',
		url: submitBasket_params.ajaxurl,
		data: {			
			"action"		: "removeBasketItem",			
			"projectID"		: projectID,
			"security"		: submitBasket_params.ajax_nonce
		},
		success: function(data){
			
			
			document.getElementById("imperialBasketDiv").innerHTML = data;
			
			// Only hide this button if its the same ID as the that was removed			
			if(thisPageProjectID==projectID)
			{			
				jQuery('#basket-add-button-div').removeClass( "ek-hidden" );
				jQuery('#basket-add-success-div').addClass( "ek-hidden" );				
			}

			
					
		}
			
	});
	
	
	
	
}

// Finalise the basket
function finaliseBasket(projectTypeID)
{

	
	jQuery.ajax({
		type: 'POST',
		url: submitBasket_params.ajaxurl,
		data: {			
			"action"		: "finaliseBasket",			
			"projectTypeID"		: projectTypeID,
			"security"		: submitBasket_params.ajax_nonce
		},
		success: function(data){
			
			
			document.getElementById("imperialBasketDiv").innerHTML = data;
					
						
		}
			
	});

	
	
	
}



