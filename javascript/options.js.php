(function($) {
jQuery(document).ready( function(){
	$('#cdn').change( function(){ 
		
		cdnProvider = $('#cdn option:selected').val();
		$("#cdn option").each(function(i){
            var provider =  $(this).val();
    		jQuery('.'+provider+'_details').hide();
		});

		jQuery('.'+cdnProvider+'_details').show();
		
	});
	
	$('#check-details').click( function(){
		var ajaxUrl = '/wp-admin/admin.php?page=cst-main&subpage=js&js=options';
		var typeData = $('#cdn option:selected').val();
		ajaxUrl += typeData;
		if (typeData == "aws"){
			var accessCode = $('input[name="aws_access"]').val(); 
			var secretCode = $('input[name="aws_secret"]').val();
			var bucket = $('input[name="aws_bucket"]').val();
			ajaxUrl += '&access='+accessCode+'&secret='+secretCode+'&bucket='+bucket;
		}
		
		$.get(ajaxUrl, function(data) {

		  if (data.response == true){
		  	alert("Details are valid");
		  } else {
		  	alert("Details aren't valid");
		  }
		},"json");

	
	});
	var cdnSelected = $('#cdn option:selected').val();
	 $.each(["aws","cf"],function(index, value){
	 	if ( cdnSelected != value) {
        	$('.'+value+'_details').hide();
        }
      });
	
	  
}); })(jQuery);	