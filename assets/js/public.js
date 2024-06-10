$(document).ready(function(){
	function confirmPublic(){
		// Mostrar a tela de confirmação pública.
		
		$('.confirmPublic').show();
		
		// Iniciar popup.
		
		$('.button').popup({addTouchEvents:false});
		
		// Form da confirmacao.
		
		var formSelector = '.confirmationPublicForm';
		
		$(formSelector)
			.form({
				
			});
		
		// Botão de confirmação.
		
		$('.confirmPublicSchedulleBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			$(formSelector).find('input[name="choice"]').val('confirm');
			$(formSelector).form('submit');
		});
		
		// Botão de cancelamento.
		
		$('.cancelPublicSchedulingBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			$(formSelector).form('submit');
		});
	}
	
	function cancelPublic(){
		// Mostrar a tela de confirmação pública.
		
		$('.cancelPublic').show();
		
		// Iniciar popup.
		
		$('.button').popup({addTouchEvents:false});
		
		// Form da confirmacao.
		
		var formSelector = '.cancellationPublicoForm';
		
		$(formSelector)
			.form({
				
			});
		
		// Botão de cancelamento.
		
		$('.cancelPublicSchedulingBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			$(formSelector).form('submit');
		});
	}
	
	function start(){
		// Tratar alterações do agendamento.
		
		if('confirmPublic' in gestor){ confirmPublic(); }
		if('cancelPublic' in gestor){ cancelPublic(); }
	}
	
	start();
	
});