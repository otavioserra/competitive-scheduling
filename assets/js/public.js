jQuery( document ).ready( function(){
	function confirmPublic(){
		// Show the public confirmation screen.
		jQuery( '.confirmPublic' ).show();
		
		// Start popup.
		jQuery( '.button' ).popup( { addTouchEvents:false } );
		
		// Confirmation form.
		var formSelector = '.confirmationPublicForm';
		
		jQuery( formSelector )
			.form( {
				
			} );
		
		// Confirmation button.
		jQuery( '.confirmPublicSchedulleBtn' ).on( 'mouseup tap', function(e){
			if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
			
			jQuery( formSelector ).find( 'input[name="choice"]' ).val( 'confirm' );
			jQuery( formSelector ).form( 'submit' );
		} );
		
		// Cancel button.
		jQuery( '.cancelPublicSchedulingBtn' ).on( 'mouseup tap', function(e){
			if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
			
			jQuery( formSelector ).form( 'submit' );
		} );
	}
	
	function cancelPublic(){
		// Show the public cancellation screen.
		jQuery( '.cancelPublic' ).show();
		
		// Start popup.
		jQuery( '.button' ).popup( { addTouchEvents:false } );
		
		// Confirmation form.
		var formSelector = '.cancellationPublicoForm';
		
		jQuery( formSelector )
			.form( {
				
			} );
		
		// Cancel button.
		jQuery( '.cancelPublicSchedulingBtn' ).on( 'mouseup tap', function(e){
			if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
			
			jQuery( formSelector ).form( 'submit' );
		} );
	}

	function expiredOrNotFound(){
		// Show the public expired or not found screen.
		
		jQuery( '.expiredOrNotFound' ).show();
	}
	
	function start(){
		// Handle scheduling changes.
		
		if( 'confirmPublic' in cs_manager ){ confirmPublic(); }
		if( 'cancelPublic' in cs_manager ){ cancelPublic(); }
		if( 'expiredOrNotFound' in cs_manager ){ expiredOrNotFound(); }
	}
	
	start();
	
} );