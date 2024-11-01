jQuery(document).ready(function() {
		jQuery("#post").validate();
		jQuery("#meta_coupon_image_code").click(function() {
				if(jQuery("#meta-image-url-chk").is(":checked")){				
				jQuery("#err_msg_image").text("Image not exist! \n Please enter valid url to insert");
				return false;
				}else{
				jQuery("#err_msg_image").text("");
				}
				window.send_to_editor = function(html) {
				 	imgurl = jQuery("img",html).attr("src");	
				 	jQuery("#upload_image").val(imgurl);
				 	jQuery("#view_coupon_img").attr("src", imgurl);
				 	jQuery("#view_coupon_img").css("display", "block");
				 tb_remove();
				}
				 tb_show("", "media-upload.php?post_id=1&type=image&TB_iframe=true");
				 return false;
				});
		jQuery("#postdiv, #postdivrich").prependTo("#custom_editor .inside");
	
	 
	    jQuery('#coupon_button').click(function(){		
			var randomnumber= Math.floor((Math.random()*10000)+1);
			jQuery('#meta_coupon_code').val(randomnumber);
			return false;
		});
		jQuery('#meta-image-url-chk').click(function () {
			if(jQuery(this).is(':checked')){
				jQuery('#image_url_span').show();
			}else{
				jQuery('#image_url_span').hide();
				jQuery("#image_url").val("");
				jQuery("#view_coupon_img").attr("src", '');
				jQuery("#view_coupon_img").css("display", "none");
				jQuery("#upload_image").val('');				
				jQuery("#err_msg_image").text("");
			}
     	});
	     if(jQuery("#meta-image-url-chk").is(':checked')){
					jQuery('#image_url_span').show();
				}else{
					jQuery('#image_url_span').hide();
					jQuery("#image_url").val("");
				 	jQuery("#view_coupon_img").css("display", "none");
				}
		 if(jQuery("#upload_image").val()!=""){
		 	jQuery("#view_coupon_img").css("display", "block");
		 }
		jQuery("#image_url").focusout(function() {
			if(jQuery("#meta-image-url-chk").is(':checked')){
				img_url = jQuery("#image_url").val();
				if(img_url==""){
					jQuery("#err_msg_image").text("Url should not blank");
					return false;
				}else{
					jQuery("#err_msg_image").text("");
				}
				jQuery("<img>", {
					src: img_url,
					error: function() { 
				      	                jQuery("#err_msg_image").text("Please enter valid url");
					                    jQuery("#view_coupon_img").attr("src", '');
									 	jQuery("#view_coupon_img").css("display", "none");
									 	jQuery("#upload_image").val("");
									 	return false; 
					},
					load: function() { 
					jQuery("#view_coupon_img").attr("src", img_url);
									 	jQuery("#view_coupon_img").css("display", "block");
									 	jQuery("#upload_image").val(img_url);
									 	jQuery("#err_msg_image").text("");
									}
							});
					
				}
			});
			
			coupon_border();
			gradian_top();
			gradian_bottom();
			content_bg_color();
			coupon_font_color();
			//setting page
			jQuery("#wps_coupons_border_color").change(function(){		
				coupon_border();		
			});
			
			//gradiant top
			jQuery("#wps_coupons_bg_color_top").change(function(){				
			gradian_top();
			});
			
			//gradiant bottom
			jQuery("#wps_coupons_bg_color_bottom").change(function(){				
			gradian_bottom();
			});
			
			//content background color
			jQuery("#wps_coupons_content_bg_color").change(function(){	
				content_bg_color();
			});
			
			//coupon font color
			jQuery("#wps_coupons_font_color").change(function(){	
				coupon_font_color();
			});
			
			//reset color setting
			jQuery("#wps_resetColor").click(function(){	
				jQuery("#wps_coupons_border_color").val(jQuery("#wps_coupons_border_color_old").val());
				jQuery("#wps_coupons_border_color").css("background-color","#"+jQuery("#wps_coupons_border_color_old").val());
				jQuery("#wps_coupons_border_color").css("color","#FFFFFF");
				
				jQuery("#wps_coupons_bg_color_top").val(jQuery("#wps_coupons_bg_color_top_old").val());
				jQuery("#wps_coupons_bg_color_top").css("background-color","#"+jQuery("#wps_coupons_bg_color_top_old").val());
				jQuery("#wps_coupons_bg_color_top").css("color","#FFFFFF");
				
				jQuery("#wps_coupons_bg_color_bottom").val(jQuery("#wps_coupons_bg_color_bottom_old").val());
				jQuery("#wps_coupons_bg_color_bottom").css("background-color","#"+jQuery("#wps_coupons_bg_color_bottom_old").val());
				jQuery("#wps_coupons_bg_color_bottom").css("color","#FFFFFF");
				
				jQuery("#wps_coupons_content_bg_color").val(jQuery("#wps_coupons_content_bg_color_old").val());
				jQuery("#wps_coupons_content_bg_color").css("background-color","#"+jQuery("#wps_coupons_content_bg_color_old").val());
				jQuery("#wps_coupons_content_bg_color").css("color","#000000");
				
				jQuery("#wps_coupons_font_color").val(jQuery("#wps_coupons_font_color_old").val());
				jQuery("#wps_coupons_font_color").css("background-color","#"+jQuery("#wps_coupons_font_color_old").val());
				jQuery("#wps_coupons_font_color").css("color","#000000");
				
				
			coupon_border();
			gradian_top();
			gradian_bottom();
			content_bg_color();
			coupon_font_color();
			
			});
	});
	
	function coupon_border(){
		jQuery(".coupon-box2").css("border","1px dashed #"+jQuery("#wps_coupons_border_color").val());
	}
	
	function gradian_top(){
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
	
	function gradian_bottom(){
		var hex2 = '#'+jQuery("#wps_coupons_bg_color_bottom").val();			
			var hex1 = '#'+jQuery("#wps_coupons_bg_color_top").val();
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