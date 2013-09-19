$(document).ready(function() {
	$('#searchtext').keypress(function(e){
	if(e.which == 13){ //enter key pressed
		$('#submitsearch').click();
	}
	});
	
});

