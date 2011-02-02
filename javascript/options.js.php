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
	 $('#minify').change( function(){ 
		
		currentEngine = $('#minify option:selected').val();
		$("#minify option").each(function(i){
            var engine =  $(this).val();
    		jQuery('.'+engine+'_minify').hide();
		});

		jQuery('.'+currentEngine+'_minify').show();
		
	});
	
	$('#check-details').click( function(){
		var ajaxUrl = '/wp-admin/admin.php?page=cst-main&subpage=js&type=';
		var typeData = $('#cdn option:selected').val();
		ajaxUrl += typeData;
		if ( typeData == "aws" ){
			var accessCode = $('input[name="aws_access"]').val(); 
			var secretCode = encodeURIComponent($('input[name="aws_secret"]').val());
			var bucket = $('input[name="aws_bucket"]').val();
			ajaxUrl += '&access='+accessCode+'&secret='+secretCode+'&bucket_name='+bucket;
		} else if ( typeData == "cf" ){			
			var username = $('input[name="cf_username"]').val(); 
			var apiKey = $('input[name="cf_apikey"]').val();
			var container = $('input[name="cf_container"]').val();
			ajaxUrl += '&username='+username+'&apikey='+apiKey+'&container='+container;
		}
		
		$.get(ajaxUrl, function(data) {

		  if (data.valid == true){
		  	alert("Details are valid");
		  } else {
		  	alert("Details aren't valid");
		  }
		},"json");

	
	});
	var cdnSelected = $('#cdn option:selected').val();
	 $.each(["aws","cf","ftp"],function(index, value){
	 	if ( cdnSelected != value) {
        	$('.'+value+'_details').hide();
        }
      });
	var minifySelected = $('#minify option:selected').val();
	$.each(['google'], function(index,value){
		if ( minifySelected != value) {
			$('.'+value+'_minify').hide();
		}
	}); 
}); })(jQuery);	