GO.message = {
	
	alert : function(message, type) {
		
		if(!type){
			type='warning';
		}
		console.log(message);
			$('#goMessageContainer').html(
							'<div class="alert alert-'+type+'"><a class="close" data-dismiss="alert">×</a><span>'+message+'</span></div>');
	}
	
}