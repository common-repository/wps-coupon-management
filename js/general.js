jQuery(document).ready(function() { 	
	    	
			if(jQuery("#is_single").val()==null){
			 var listname = jQuery("#listname").val();
			 var link = jQuery("#link").val();
		 	 jQuery(".coupon.abstractview").addClass("postedCoupon_"+listname+"");	
			 jQuery("#couponCode_"+listname+"").append( "<div id='last_"+listname+"'></div>");
			 eval(" doMouseWheel_"+listname+"=1;");
			 jQuery(window).scroll(function() {
			 var doMouseWheel = eval("doMouseWheel_"+listname);
			 
			 if (!doMouseWheel)  {
				return ;
			 } ;
			 var distanceTop = jQuery("#last_"+listname).offset().top - jQuery(window).height();	
			 
				if  (jQuery(window).scrollTop() > distanceTop){
			 		    eval( 'doMouseWheel_' + listname + "= 0;");
						jQuery("div#loadMoreCoupons_"+listname).show();
					 	jQuery.ajax({
								dataType : "html" ,
								url: link+"&lastCoupon="+ jQuery(".postedCoupon_"+listname+":last").attr('id') ,								success: function(html) {
								 eval( 'doMouseWheel_' + listname + "= 1;");
								if(html){										
									jQuery("#couponCode_"+listname).append(html);
									jQuery("#last_"+listname).remove();
									jQuery("#couponCode_"+listname).append( "<div id='last_"+listname+"'></div>" );
									jQuery("div#loadMoreCoupons_"+listname).hide();
								}else{		
									 eval( 'doMouseWheel_' + listname + "= 0;");
								}
						}
						});
				}
				
		});		
			}
		
		coupon_border();
		content_bg_color();
		coupon_font_color();
		gradian();
});


jQuery(function() {    
    jQuery(window).resize(function() {    
			coupon_border();
			gradian(); 	
			content_bg_color();
			coupon_font_color()
    });    
});

function coupon_border(){
		var meta = jQuery(".coupon-box");
		meta.css("border","1px dashed #"+jQuery("#wps_coupons_border_color").val());
	}
function gradian(){
			var hex1 = '#'+jQuery("#wps_coupons_bg_color_top").val();				
			var hex2 = '#'+jQuery("#wps_coupons_bg_color_bottom").val();
			var meta = jQuery(".gradcolor");
			meta.css("filter", "progid:DXImageTransform.Microsoft.gradient(GradientType=0, startColorstr='"+hex1+"', endColorstr='"+hex2+"')"); /* IE6-8 */				
			meta.css("background-image","-ms-linear-gradient(top, "+hex1+" 1%, "+hex2+" 100%)"); /* IE10+ */					
			meta.css("background-image", "-webkit-gradient(linear, left top, left bottom, color-stop(1%, "+hex1+"), color-stop(100%, "+hex2+"))"); /* Chrome,Safari4+ */
			meta.css("background-image", "-webkit-linear-gradient(top, "+hex1+" 1%,"+hex2+" 100%)"); /* Chrome10+,Safari5.1+ */
			meta.css("background-image", "-moz-linear-gradient(top, "+hex1+" 1%,"+hex2+" 100%)"); /* FF3.6+ */			
			meta.css("background-image", "-o-linear-gradient(top, "+hex1+" 1%,"+hex2+" 100%)"); /* Opera 11.10+ */			
			meta.css("background-image", "linear-gradient(to bottom, "+hex1+" 1%,"+hex2+" 100%)");	/* W3C */	 
			meta.css("background-image", ""+hex1+""); /* Old browsers */
	}
function content_bg_color(){
		var meta = jQuery(".expire-info2");
		meta.css("background-color", ""+'#'+jQuery("#wps_coupons_content_bg_color").val()+"");
	}
	
function coupon_font_color(){
			var meta1 = jQuery(".image-box2 .title2 a");
			var meta2 = jQuery(".getcode-box2 a");				
			var meta3 = jQuery(".offer-dis2");				
			meta1.css("color", ""+'#'+jQuery("#wps_coupons_font_color").val()+"");
			meta2.css("color", ""+'#'+jQuery("#wps_coupons_font_color").val()+"");
			meta3.css("color", ""+'#'+jQuery("#wps_coupons_font_color").val()+"");
}	