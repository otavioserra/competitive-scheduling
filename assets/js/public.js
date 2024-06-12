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
			
			loading( 'open' );
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

			loading( 'open' );
			jQuery( formSelector ).form( 'submit' );
		} );
	}

	function expiredOrNotFound(){
		// Show the public expired or not found.
		
		jQuery( '.expiredOrNotFound' ).show();
	}

	function errorInfo(){
		// Show the public error info.
		
		jQuery( '.errorInfo' ).show();
	}

	function successInfo(){
		// Show the public success info.
		
		jQuery( '.successInfo' ).show();
	}
	
	function start(){
		// Handle scheduling changes.
		
		if( 'confirm' in cs_manager ){ confirmPublic(); }
		if( 'cancel' in cs_manager ){ cancelPublic(); }
		if( 'expiredOrNotFound' in cs_manager ){ expiredOrNotFound(); }
		if( 'errorInfo' in cs_manager ){ errorInfo(); }
		if( 'successInfo' in cs_manager ){ successInfo(); }
	}
	
	start();

	function loading( option ){
		switch( option ){
			case 'open':
				if( ! ('loading' in cs_manager ) ){
					jQuery('.page.dimmer').dimmer({
						closable: false,
						onVisible: function(){
							jQuery('.page.dimmer').removeClass( 'transition' );
						}
					});
					
					cs_manager.loading = true;
				}
				
				jQuery('.page.dimmer').dimmer('show');
				jQuery('.page.dimmer').removeClass( 'transition' );
			break;
			case 'close':
				jQuery('.page.dimmer').dimmer('hide');
			break;
		}
	}
	
} );