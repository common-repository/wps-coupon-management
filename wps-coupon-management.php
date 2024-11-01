<?php 
/*
Plugin Name: WPS Coupons Management             
Plugin URI: http://www.indianic.com/
Description: Indianic WPS discount coupon management includes custom shortcode,with AJAX base auto loading coupon view.
Version: 2.0.3
Author: sandip.chhaya@indianic.com
Author URI: http://www.indianic.com/
License: GPLv2 or later
*/

class WPS_Coupon {
  
  var $pluginPath;
  var $pluginUrl;
  var $rootPath;
  var $wpdb;
  
  
  function __construct() {
   	global $wpdb;
    $this->wpdb = $wpdb;
    $this->ds = DIRECTORY_SEPARATOR;
    $this->pluginPath = dirname(__FILE__) . $this->ds;
    $this->rootPath = dirname(dirname(dirname(dirname(__FILE__))));
    $this->pluginUrl = WP_PLUGIN_URL ."/".trim(dirname(plugin_basename(__FILE__)));
  	
    // Admin side action and filters
    add_action('admin_menu', array($this, 'wps_coupon_register_menu'));
    add_action('admin_init', array($this,'wps_coupon_add_admin_JS_CSS'));
    add_filter('post_row_actions', array($this,'wps_post_row_actions'), 10, 2);
    add_action('add_meta_boxes', array($this,'wps_coupon_meta_box_add' ));  
    add_action('admin_footer', array($this,'wps_modify_form'));
    add_action('save_post', array($this,'wps_coupon_updated_custom_meta') );
    add_filter('post_updated_messages', array($this,'codex_coupon_updated_messages'));
    add_action('admin_head', array($this,'wps_plugin_header'));
    
    
    // Front side action and filters
    add_action('wp_enqueue_scripts', array($this,'wps_front_JS_CSS'));
	add_shortcode('wps-all-coupons-view', array($this, 'wps_coupon_view_mode_listing')); //Listing page shortcode
    add_action('wp_ajax_nopriv_list_coupons', array($this,'list_coupons') );
    add_action('wp_head',array($this,'wps_hidden_variables') );
	add_action('wp_ajax_list_coupons', array($this,'list_coupons'));
	add_shortcode( 'wps-signle-coupon-view', array($this,'wps_coupon_shortcode') );
	add_filter( 'template_include',array($this,'wps_coupon_template_include') );
	
   }  
  	/** To store option hidden variables **/
  	function wps_hidden_variables(){
  		$wps_colors = maybe_unserialize(get_option('wps_copons_color'));	
 	?>
 	<input type="hidden" id="wps_coupons_border_color" value="<?php echo $wps_colors['wps_coupons_border_color']; ?>"> 	 	
 	<input type="hidden" id="wps_coupons_bg_color_top" value="<?php echo $wps_colors['wps_coupons_bg_color_top']; ?>"> 	
 	<input type="hidden" id="wps_coupons_bg_color_bottom" value="<?php echo $wps_colors['wps_coupons_bg_color_bottom']; ?>"> <input type="hidden" id="wps_coupons_content_bg_color" value="<?php echo $wps_colors['wps_coupons_content_bg_color']; ?>"> 	
 	<input type="hidden" id="wps_coupons_font_color" value="<?php echo $wps_colors['wps_coupons_font_color']; ?>"> 	
 	<input type="hidden" id="plugin_url" value="<?php echo $this->pluginUrl?>"> 	 	
 	<?php
  	}
	/** Add plugin admin JS and CSS  **/ 
	function wps_coupon_add_admin_JS_CSS(){
		wp_enqueue_style('my-admin-theme', plugins_url('/css/style_admin.css', __FILE__));
		wp_enqueue_script('jquery-ui-datepicker');
	    wp_enqueue_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css'); 
	    wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
		wp_enqueue_style( 'custom-css', plugins_url('/css/custom-coupon.css', __FILE__));
		wp_register_script( 'custom-js-image-resize', plugins_url('/js/jquery.ae.image.resize.js',__FILE__ ));
		wp_enqueue_script('custom-js-image-resize');
		wp_register_script( 'general-admin', plugins_url('/js/general-admin.js',__FILE__ ));
		wp_register_script( 'jscolor-admin', plugins_url('/js/jscolor.js',__FILE__ ));
		wp_register_script( 'jquery-validate', plugins_url('/js/jquery.validate.min.js',__FILE__ ));
		wp_enqueue_script('jscolor-admin');
		
	}
	/** Start custom post type coding for backend coupon listing **/
	function wps_coupon_custom_init(){
	  $labels = array(
	    'name' => __("WPS Coupon Mangement", "coupontext"),
	    'singular_name' => __("Coupon", "coupontext"),
	    'add_new' => __("Add Coupon", "coupontext"),
	    'add_new_item' => __("Add New Coupon", "coupontext"),
	    'edit_item' => __("Edit Coupon", "coupontext"),
	    'new_item' => __("New Coupon", "coupontext"),
	    'all_items' => __("All Coupons", "coupontext"),
	    'view_item' => __("View Coupon", "coupontext"),
	    'search_items' => __("Search Coupon", "coupontext"),
	    'not_found' =>  __("No Coupon found", "coupontext"),
	    'not_found_in_trash' => __("No Coupon found in Trash", "coupontext"), 
	    'parent_item_colon' => '',
	    'menu_name' => __("WPS Coupons", "coupontext")
	   
	  );
	  $coupon_args = array(
	    'labels' => $labels,
	    'public' => true,
	    'publicly_queryable' => true,
	    'show_ui' => true, 
	    'show_in_menu' => true, 
	    'query_var' => true,
		'show_in_admin_bar' => true,
	    'rewrite' => array( 'slug' => 'coupon', 'with_front' => true ),	
	    'capability_type' => 'page',
	    'has_archive' => true, 
	    'hierarchical' => false,	    
	    'menu_icon' => $this->pluginUrl . "/images/icon.png", // 16px16
	    'supports' => array( 'title'),
	  ); 
      $coupon_tags_labels = array(
		'name'              => _x( 'Coupon Tags', 'coupontext' ),
		'singular_name'     => _x( 'Tag', 'coupontext' ),
		'search_items'      => __( 'Search Tag','coupontext' ),
		'all_items'         => __( 'All Tag' ,'coupontext'),
		'parent_item'       => __( 'Parent Tag' ,'coupontext'),
		'parent_item_colon' => __( 'Parent Tag:' ,'coupontext'),
		'edit_item'         => __( 'Edit Tag' ,'coupontext'),
		'update_item'       => __( 'Update Tag' ,'coupontext'),
		'add_new_item'      => __( 'Add New Tag' ,'coupontext'),
		'new_item_name'     => __( 'New Tag Name' ,'coupontext'),
		'menu_name'         => __( 'Tags' ,'coupontext'),
		);
      $taxonomy_args = array(
        'hierarchical'          => false,
        'labels'                => $coupon_tags_labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'coupon_post_tag' ),
        );
        register_post_type( 'coupon', $coupon_args );
        register_taxonomy('coupon_post_tag', 'coupon',$taxonomy_args);
	}
	/** function is used for showing expiry date and hide quick edit **/
	function wps_post_row_actions($actions, $post){
		if($post->post_type == 'coupon'){
		    $meta_value_coupon_code = get_post_meta($post->ID, 'meta_coupon_code', true);
		    if($meta_value_coupon_code == '') $meta_value_coupon_code = __( "--", 'coupontext' );
		    $meta_value_expiry = get_post_meta($post->ID, 'meta_expiry', true);
		    $formated_date = date(get_option('date_format'),strtotime($meta_value_expiry));		    
		    $ex_date = $formated_date;
		    $single_short_code = '[wps-signle-coupon-view id="'.$post->ID.'"]';
		    $formated_date =($meta_value_expiry == '')? __( "--", 'coupontext' ):'';
		    echo __( 'Generated coupon code: ', 'coupontext' )."<span class='view_code_text'>".$meta_value_coupon_code."</span>"; 
		    echo " | ";
		    echo __( 'Coupon code expiry date: ', 'coupontext' )."<span class='view_code_text'>".$ex_date."</span>";
		    echo " | ";
		    echo __( 'Shortcode: ', 'coupontext' )."<span class='view_code_text'>".$single_short_code."</span>";
		    unset( $actions['inline hide-if-no-js'] ); 
		    return $actions; 
		}  
		return $actions ;
	}
	/** Adding metaboxes **/
	function wps_coupon_meta_box_add(){  
	   add_meta_box( 'meta-coupon', __('Coupon Information', 'coupontext'),  array( __CLASS__, 'wps_coupon_info_meta_box' ), 'coupon', 'normal', 'default' );  
	   add_meta_box('custom_editor', 'Coupon Description', array( __CLASS__, 'wps_custom_editor' ), 'coupon', 'advanced', 'high');
	} 
	/** Adding custom editor **/
	function wps_custom_editor(){
		global $post;
		$content = get_post_meta( $post->ID, 'meta-editor-coupon', false );
		wp_editor( $content[0],'meta-editor-coupon');
	}
	/** Adding coupon information **/
	function wps_coupon_info_meta_box(){
		wp_enqueue_script('general-admin');
		wp_enqueue_script('jquery-validate');
		global $post;
		$values = get_post_meta($post->ID);
		
		$rand = rand(1,10000);
		$meta_expiry = isset( $values['meta_expiry'] ) ? esc_attr( $values['meta_expiry'][0] ) :  '' ; 
		$meta_coupon_code = isset( $values['meta_coupon_code'] ) ? esc_attr( $values['meta_coupon_code'][0] ) :  $rand ; 
		$meta_coupon_image_code = isset( $values['upload_image'] ) ? esc_attr( $values['upload_image'][0] ) :  '' ; 
		$meta_coupon_code_link = isset( $values['meta_coupon_code_link'] ) ? esc_attr( $values['meta_coupon_code_link'][0] ) :  '' ; 
		$meta_image_url_chk = isset($values['meta-image-url-chk']) ? esc_attr( $values['meta-image-url-chk'][0] ) :  '' ; 	
	?>   
	<script>
		jQuery(document).ready(function() {
			//var set_date_obj =  jQuery('.meta_expiry').datepicker({dateFormat: 'mm/dd/yy',minDate: '0'});
			var set_date_obj =  jQuery('.meta_expiry').datepicker({dateFormat: 'yy-mm-dd',minDate: '0'});
		    <?php if($meta_expiry == '' )  { ?>  set_date_obj.datepicker('setDate', new Date());  <?php } else {?> jQuery('#ui-datepicker-div').css("display", "none");<?php }?>
		});
	</script>
	<table width="510">
        <tr>
            <td><label for="meta_coupon_code"> <?php echo __('Generate Coupon Code', 'coupontext'); ?></label>  </td>
            <td><input type="text" name="meta_coupon_code" class="meta_coupon_code required" id="meta_coupon_code" value="<?php echo $meta_coupon_code; ?>" />
            <input type="button" name="coupon_button" id="coupon_button" value=" <?php echo __('Generate Random Coupon', 'coupontext'); ?>" class="button button-primary button-large"/></td>
        </tr>
    	<tr>
            <td><label for="meta_price"> <?php echo __('Coupon Expiry Date', 'coupontext'); ?></label>  </td>
            <td><input type="text" name="meta_expiry" class="meta_expiry" id="meta_expiry" value="<?php echo $meta_expiry; ?>" />  </td>
        </tr>
        
        <tr>
            <td><label for="meta_price"> <?php echo __('Coupon External Site Link', 'coupontext'); ?></label>  </td>
            <td><input type="text" name="meta_coupon_code_link" class="meta_coupon_code_link" id="meta_coupon_code_link" value="<?php echo $meta_coupon_code_link; ?>"  size="50px;"/>  </td>
        </tr>
        <tr>
            <td><label for="meta_coupon_image_code"> <?php echo __('Upload coupon image', 'coupontext'); ?></label>  </td>
            <td>
            <input type="hidden" name="upload_image" class="upload_image" id="upload_image" value="<?php echo $meta_coupon_image_code; ?>" />
            <input type="button" name="meta_coupon_image_code" id="meta_coupon_image_code" value="Add Media File" class="button button-primary button-large"/><span> (180px X 66px)</span> </span>
            </td>
	    </tr>
        <tr><td>&nbsp;</td>
        <td><span><?php echo __('(OR)','coupontext'); ?></span></td>
        </tr>
        <tr><td>&nbsp;</td>
            <td> <span><input type="checkbox" id="meta-image-url-chk" name="meta-image-url-chk" class="meta-image-url-chk" value="1" <?php if($meta_image_url_chk=="1") { print "checked=checked"; } else { print ''; }?>><span class="coupon_meta_box_http_url" ><?php echo __('Http URL','coupontext'); ?></span><span class="image_url_span"  id="image_url_span"><input type="text" name="image_url" id="image_url" value="<?php print $meta_coupon_image_code;?>" size="33px;" placeholder="http://www.example.com/image.jpg"><span id="err_msg_image" class="err_msg_image" ></span><label class="label_jpg_png_gif"  ><?php echo __('(jpg | png | gif)','coupontext'); ?></label></span></td>
        </tr>
        <?php if($meta_coupon_image_code){?>
        <tr>
        	<td></td>
        	<td><img src="<?php echo $meta_coupon_image_code; ?>" id="view_coupon_img" width="100" height="100" /></td>
        </tr>
    	<?php } else {?>
    	<tr>
        	<td></td>
        	<td><img src="<?php echo $meta_coupon_image_code; ?>" id="view_coupon_img" width="100" height="100" class="view_coupon_image" /></td>
        </tr>
    	<?php } ?>
        <input type="hidden" name="site_name" id="site_name" value="<?php echo site_url(); ?>"  />
    </table>
    <?php
	}
	/** Updating coupon information **/
	function wps_coupon_updated_custom_meta() {
		global $post;
		$coupon_vars = $_POST;
		foreach ($coupon_vars as $coupon_key=>$coupon_val){ 
			switch ($coupon_key){ 
			  	case "meta_expiry":
			  	case "meta_coupon_code":
			  	case "meta_coupon_code_link":
			  	case "meta-editor-coupon":
					  						if ( isset( $coupon_vars[$coupon_key] ) & $coupon_vars[$coupon_key] != '' ) 
					  		                update_post_meta($post->ID,$coupon_key,trim($coupon_vars[$coupon_key]));
					  		                break; 	  
  		     	case "meta-image-url-chk":
					  						if ( (isset( $coupon_vars[$coupon_key] ) & $coupon_vars[$coupon_key] != '' ) && (isset( $coupon_vars['image_url'] ) && $coupon_vars['image_url'] != '') ) 
											update_post_meta($post->ID,'upload_image',trim($coupon_vars['image_url']));
											update_post_meta($post->ID,'meta-image-url-chk',trim($coupon_vars['meta-image-url-chk']));
					  					    break; 	    
				case "upload_image":
					  						if ( (isset( $coupon_vars[$coupon_key] ) & $coupon_vars[$coupon_key] != '' ) ) 
											update_post_meta($post->ID,'upload_image',trim($coupon_vars['upload_image']));
											update_post_meta($post->ID,'meta-image-url-chk','');
					  						break; 	      					             	                 	                             
			  }
		}
	}
	/** Updating coustom messages **/
	function codex_coupon_updated_messages() {
	  global $post, $post_ID;
	  $messages['coupon'] = array(
	    0 => '', 
	    1 => sprintf( __('Coupon published. <a href="%s">View Coupons</a>'), esc_url( get_permalink($post_ID) ) )
	  );
	  return $messages;
	}
	/** View uploaded images **/
	 function wps_modify_form(){
	   // javascript code added to general-admin.js 	
	 }
	/** Register plugin menus **/
    function wps_coupon_register_menu() {
    add_submenu_page( 
	          'edit.php?post_type=coupon'
        , __('All Coupons','all-coupons')
	        , 'Settings'
        , 'manage_options'
        , 'wps_coupon_options'
        , array($this, 'wps_coupon_options')
    );
   }
   /** Sort code setting function **/ 
    function wps_coupon_options()	{
		wp_enqueue_script('general-admin');		//include general-admin js file.
		wp_enqueue_script('jquery-validate');		//include validation script.
    	if (!current_user_can('manage_options')){
			wp_die( __("You do not have sufficient permissions to access this page.","coupontext") );
		} 
		$wps_colors = maybe_unserialize(get_option('wps_copons_color'));
		?>
		<input type="hidden" id="wps_coupons_border_color_old" value="<?php echo $wps_colors['wps_coupons_border_color']; ?>"> 	 	
 	<input type="hidden" id="wps_coupons_bg_color_top_old" value="<?php echo $wps_colors['wps_coupons_bg_color_top']; ?>"> 	
 	<input type="hidden" id="wps_coupons_bg_color_bottom_old" value="<?php echo $wps_colors['wps_coupons_bg_color_bottom']; ?>"> <input type="hidden" id="wps_coupons_content_bg_color_old" value="<?php echo $wps_colors['wps_coupons_content_bg_color']; ?>"> 	
 	<input type="hidden" id="wps_coupons_font_color_old" value="<?php echo $wps_colors['wps_coupons_font_color']; ?>"> 	 	
<?php
		echo '<div class="wps_discountcoupon">';
		echo '<div class="wrap">';
		screen_icon();
	    echo '<h2>'.__("Discount Coupon Management Setting","coupontext").'</h2>';
		echo '</div>';
		$wps_coupons_scroll_amount = get_option('wps_coupons_scroll_amount');		
		$options = get_option('wps_copons_color');
		 if($wps_coupons_scroll_amount==""){
		 	$wps_coupons_scroll_amount = "06";
		 }
		 
		$wps_coupon_management = new WPS_Coupon(); 
		$default_style_library_css = htmlspecialchars(stripcslashes(get_option('default_style_library_css')));
	    $template_result = $wps_coupon_management->get_template_name();
		 echo '<table width="30%" class="form-table"><tr><td>';
		
		 echo '<div class="all_coupons_listing_shortcode" >';
		echo  '<h3 class="title">'.__("Short Codes:","coupontext").'</h3>';
		echo '<p class="use_shortcode"> '.__("All Coupons listing(Post/Pages): ","coupontext").' <span> <strong>[wps-all-coupons-view couponviewname="Use Unique Name"]<strong> </span></p>';
		echo '<p class="description"><b>Notes:</b></p>';
		echo '<p class="description"><ul>
		<li class="description">For eg.</li>
		<li class="description">[wps-all-coupons-view couponviewname="couponview1"]</li>
		<li class="description">[wps-all-coupons-view couponviewname="couponview2"]</li>
		<li class="description">...</li>
		<li class="description">[wps-all-coupons-view couponviewname="couponviewN"]</li>
		</ul></p>';
		echo '<p class="use_shortcode">'.__("Single Coupon:  ","coupontext").' <span class="single_coupon_url"><a href="'.site_url().'/wp-admin/edit.php?post_type=coupon"><strong>'.__("Click Here  ","coupontext").'<strong></a> </span></p>';
		echo '</div>';
		echo '<p>&nbsp;</p>';
		
		echo '<div class="wrap" id="scroll_activation" >';
		echo '<table cellpadding="10" class="form-table form-setting-scroll">';
		echo '<tr><td style="padding:0px;">';
		echo '<form method="post" name="options" action="options.php">
		<span class="default_coupon_template_title" >'.__("SCROLL ACTIVATION SETTINGS","coupontext").'</span>' . wp_nonce_field('update-options');
		echo '<table class="form-table"><tr valign="top"><th scope="row"><label>'.__("Load More Activation After","coupontext").'</label></th>
		<td scope="row"><input type="text" name="wps_coupons_scroll_amount" value = "'.$wps_coupons_scroll_amount.'" /> coupons.</td></tr>';
	    echo '<tr><td><input type="hidden" name="action" value="update" /><input type="hidden" name="page_options" value="wps_coupons_scroll_amount" /><input type="submit"  class="button button-primary" name="Submit" value="Update Settings" /></td></tr>		
	    </table>	    
	    </form>
	    </td></tr>
	    </table></div>';
	    	    	    
		echo '<div class="wrap" id="default_coupon_template" style="display:none;">';
		echo '<form method="post" name="options" action="options.php">
			  <h3 class="default_coupon_template_title" >'.__("Default Coupon Template","coupontext").'</h3>' . wp_nonce_field('update-options') . '
			  <table cellpadding="10" class="form-table">
			  <tr valign="top">
			  <th scope="row"><label>'.__("Coupon Templates","coupontext").'</label></th>
			  <td scope="row">';
		echo '<select id="default_style_library_css" name="default_style_library_css">
			  '.$wps_coupon_management->wps_coupon_template_selector($template_result,$default_style_library_css ).'</select></td></tr></table><input type="hidden" name="action" value="update" />  
		      <input type="hidden" name="page_options" value="default_style_library_css" /> '.
		      '<input type="submit" name="submit" class="button-primary" value="'.__("Update Settings","coupontext").'" /></form></div></td></tr></table>';
		      
		      
	echo '<div class="wrap" id="scroll_activation">';					
			  echo '<table class="form-table">
			  <tr valign="top">
			  <td width="55%">
				  	<table cellpadding="10" class="form-table form-setting">
				  	<form method="post" name="options">
				  	<span class="default_coupon_template_title" >'.__("COUPON COLORS SETTING","coupontext").'</span>' . wp_nonce_field('update-options').'				  	
				  	
					  <tr>
					  	<th scope="row"><label>'.__("Coupon border color","coupontext").'</label></th>			  				  	<td scope="row">				
					   <input type="text" class="color" value="'.$options['wps_coupons_border_color'].'" name="wps_copons_color[wps_coupons_border_color]" id="wps_coupons_border_color">
					    </td>					  
					   </tr>
					 <tr>
					<tr valign="top">
						<th scope="row"><label>'.__("Coupon Background Color","coupontext").'</label></th>
						<td scope="row">
						Gradient Top<br>
						<input type="text" class="color" value="'.$options['wps_coupons_bg_color_top'].'" name="wps_copons_color[wps_coupons_bg_color_top]" id="wps_coupons_bg_color_top">
						</td>
					</tr>
					<tr valign="top">												
						<th scope="row"></th>
						<td scope="row">
						Gradient Bottom<br>
						<input type="text" class="color" value="'.$options['wps_coupons_bg_color_bottom'].'" name="wps_copons_color[wps_coupons_bg_color_bottom]" id="wps_coupons_bg_color_bottom">
						</td>
					</tr>
										
					<tr valign="top">
						<th scope="row"><label>'.__("Coupon Content Background Color","coupontext").'</label></th>
						<td scope="row">
						<input type="text" class="color" value="'.$options['wps_coupons_content_bg_color'].'" name="wps_copons_color[wps_coupons_content_bg_color]" id="wps_coupons_content_bg_color">
						</td>
					</tr>					
					<tr valign="top">
						<th scope="row"><label>'.__("Coupon Font Color","coupontext").'</label></th>
						<td scope="row">
						<input type="text" class="color" value="'.$options['wps_coupons_font_color'].'" name="wps_copons_color[wps_coupons_font_color]" id="wps_coupons_font_color">
						</td>
					</tr>
					
					<tr>
						<td>
						<input type="hidden" name="hdn_var" value="1" />				
					    <input type="submit" class="button-primary" value="'.__("Update Settings","coupontext").'"/>
					    </td>
					    <td>
						<input type="hidden" name="hdn_var" value="1" />				
					    <input id="wps_resetColor" type="button" value="'.__("Reset Color Settings","coupontext").'"/>
					    </td>
					</tr>
		    		</form>		
					</table>		    
				</td>				
			    <td width="45%">												
						<div class="coupon-box2">
							    <div class="custom_style60 coupon abstractview template_one  postedCoupon_Unknown" id="1"> 
							        <div class="image-box2">
							            <div class="title2 gradcolor"><a href="#">Coupon code title  ...</a></div>
							            <a class="thumb" href="#" title="test" rel="nofollow"><img src="'.$this->pluginUrl .'/images/explore.jpg" alt="preview"></a>
							            
							        </div>
							        
							        <div class="getcode-box2 gradcolor"><a href="http://www.google.com"><strong>7466</strong></a></div>
							        
							        <div class="expire-info2">
							                <div class="expire2">Expires: <span>November 30, 2014 </span></div>
							                <div class="tag2"><span class="c_tags"> Tags :  <a title="View all coupons in tag1" href="#">tag1</a>
							                <a title="View all coupons in tag2" href="#">tag2</a></span></div>
							         </div>
							         
							         <div class="offer-box2 gradcolor">
							            <div class="offer-dis2">10% Off</div>
							         </div>
							         <p class="merchantLink2"><a class="inline_content_1" href="#view_details_1">'.__( 'View Details').'</a></p>
							    
							    </div>
							</div>
						
				</td>
			   </tr>	
			   </table>';			   
		echo '</div>';		

    }
    
    
	/** Plugin icon replacement **/ 
	function wps_plugin_header() {
		    global $post_type;
	    ?>
	    <?php if (($_GET['post_type'] == 'coupon') || ($post_type == 'coupon')) : ?>
	    <script>
		jQuery(document).ready(function() {
			jQuery("#icon-edit").attr("style","background:transparent url('<?php echo $this->pluginUrl . "/images/nic_icon_50x50.jpg";?>') no-repeat; width:50px;height:50px;");
			var preview_them = jQuery("#wps_coupons_bg_color").val();				
			if(preview_them != null){				
			var preview_them = jQuery("#wps_coupons_bg_color").val();
			preview_them = preview_them.replace('#','');			
			
			jQuery("#wps_coupons_bg_img").attr('src', '<?php echo $this->pluginUrl . "/images/them_preview_"?>'+preview_them+'<?php echo ".png";?>');
			jQuery("#wps_coupons_bg_color").change(function() {
				preview_them = jQuery("#wps_coupons_bg_color").val();				
				preview_them = preview_them.replace('#','');			
				 jQuery("#wps_coupons_bg_img").attr('src', '<?php echo $this->pluginUrl . "/images/them_preview_"?>'+preview_them+'<?php echo ".png";?>');
			});
			}
		});
		</script>
		<?php endif; ?>
	    <?php
	}
	//*********** Front end coupon listing start ********************//
	/** List all coupons at front **/
	function list_coupons() {
		$tags=$_GET['tags'];
		global $wpdb;
	    $tag = sanitize_text_field( $tags );
	  	$filtered = filter_input(INPUT_GET, "lastCoupon", FILTER_SANITIZE_URL);
		$limit = trim(get_option('wps_coupons_scroll_amount'));
	  	if($limit==""){
	  		$limit = "06";
	  	}
		$args =array(
			'offset'      => $filtered,
			'post_status' => 'publish',
			'post_type'   => 'coupon',
			'meta_key'	  => 'meta_expiry',
			'orderby'     => 'meta_value',
	    	'meta_query' => array(		     					
        							array(	 'key' => 'meta_expiry',
         									 'value' => date("Y-m-d"),
           									 'compare' => '>=',            									 
           									   
       									 )
   									),		
									'posts_per_page' => $limit    							
		);
		if ( $tag!='Unknown' ) {
			$args['tax_query'][] = array(
						'taxonomy' => 'coupon_post_tag',
						'field' => 'slug',
						'terms' => $tag
					 );
		}
 		$totalRec  = get_posts($args);
	 	$totalRecCount = count($totalRec);		
 	$wps_coupon_management = new WPS_Coupon();
 	$custom_flag =0;
 	if($totalRecCount > 0) {
	 	$counterId = $filtered;
	 	for($coupon=0;$coupon<$totalRecCount;$coupon++) {
	 		$counterId++;
	 		$data = get_post_meta($totalRec[$coupon]->ID, '', $totalRec[$coupon]->ID);
            $formated_date = date(get_option('date_format'),strtotime($data['meta_expiry'][0]));
            $couponId = $totalRec[$coupon]->ID;
            $custom_flag_array = $wps_coupon_management->wps_template_custom_flag($couponId); 
            $custom_flag =  key($custom_flag_array); 
            $stylemeta_content = $custom_flag_array[$custom_flag];
            if($custom_flag==1){
			 	$wps_coupon_management->set_coupon_style($couponId,$stylemeta_content,$counterId);
			} 
            if($custom_flag==0){             
          ?>
	 		<div class=" <?php echo 'custom_style'.$couponId; ?> coupon abstractview postedCoupon_<?php echo $_GET['listname']; ?>" id="<?php echo $counterId ?>">
	  	    <div class="inner">
		    <div class="subject">
		    		<a rel="nofollow" title="<?php echo $totalRec[$coupon]->post_title; ?>" <?php echo ($data['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>  href="<?php echo $data['meta_coupon_code_link'][0];?>" class="thumb">
		    		<?php
		    		if($data['upload_image'][0]!=""){ 
		    		?>
		    			<img width="162px"  alt="<?php echo $totalRec[$coupon]->post_title ?>" src="<?php echo $data['upload_image'][0]; ?>">
		    			<?php } else {?>
		    			<img width="162px" height="65px" alt="<?php echo $totalRec[$coupon]->post_title ?>" src="<?php echo plugins_url('/images/no_image_flat.png', __FILE__); ?>">
		    			<?php }?>
		    		</a>
		     </div>
	   	    <div class="detail">
	        <div class="codeview" title="<?php echo __( 'Coupon Code', 'coupontext' );?>"><a <?php echo ($data['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>  href="<?php echo $data['meta_coupon_code_link'][0];?>"><strong><?php echo $data['meta_coupon_code'][0]; ?></strong></a></div>
	        <div class="box_detail" ><h5 id="box_detail_coupontitle" class="couponTitle"><a <?php echo ($data['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>  href="<?php echo $data['meta_coupon_code_link'][0];?>" ><?php echo $this->wps_coupon_title_trim($totalRec[$coupon]->post_title); ?></a></h5> <p class="exp_date"><?php echo __( 'Expire On: ', 'coupontext' ).'  <b>'.$formated_date;?></b></p>
	        <p class="merchantLink"><a href="#view_details_<?php echo $counterId;?>" class="inline_content_<?php echo $counterId; ?>"><?php echo __( 'View Details', 'coupontext' );?></a></p>
	         <?php
		       echo $wps_coupon_management->wps_get_tags($totalRec[$coupon]->ID);
		       ?>
		   </div>
	       </div>
	       <div class="break"></div>
	       </div>
	  <?php
         }
	  ?>
	</div> 
	<script>
		jQuery(document).ready(function() { 
			coupon_border();
			gradian();
			content_bg_color();
			coupon_font_color()
			jQuery(".inline_content_<?php echo $counterId;?>").click(function(){
			jQuery(".inline_content_<?php echo $counterId;?>").colorbox({inline:true, width:"30%"});
		});
	});
	</script>
		<div class="div_coupon_popup" ><div id="view_details_<?php echo $counterId; ?>" class="coupon_detail_popup"  ><div><strong><?php echo __( 'Coupon Details', 'coupontext' );?></strong></div>
		<div class="list_coupon_popup" ><?php echo $totalRec[$coupon]->post_title;?></div>
		<div class="list_coupon_popup" ><?php echo __( 'Expire On: ', 'coupontext' ).'  <b>'.$formated_date.'</b>'; ?></div>
		<div class="list_coupon_popup" ><?php echo trim($data['meta-editor-coupon'][0]);?></div></div></div>
	 	<?php		
	  	}
 	  }  
  		exit;
   }
	/** Adding CSS and JS at front side **/
	function wps_front_JS_CSS(){
	    wp_enqueue_script('jquery');
		wp_enqueue_style( 'custom-css', plugins_url('/css/custom-coupon.css', __FILE__));
		wp_enqueue_script('colorbox-js', plugins_url('/js/jquery.colorbox-min.js', __FILE__));
		wp_enqueue_style('colorbox-css', plugins_url('css/colorbox.css', __FILE__));
	}
	/** Default coupon listing page **/
	function wps_coupon_view_mode_listing($atts){
		ob_start();
		wp_enqueue_script('general', plugins_url('/js/general.js', __FILE__));
		
		global $wpdb;
		extract(shortcode_atts(array(
		"couponviewname" => 'Unknown',
		"tags" => 'Unknown',
		), $atts));
		$listname = str_replace(" ","_",trim($couponviewname));
		$nonce = wp_create_nonce("my_user_coupon_nonce");
	    $link = admin_url('admin-ajax.php?action=list_coupons&nonce='.$nonce.'&listname='.$listname.'&tags='.$tags);
		?>
		<input type="hidden" id="bg_theme_color" value="<?php echo trim(get_option('wps_coupons_bg_color')) ?>">
		<input type="hidden" id="coupon_color" value="<?php echo trim(get_option('wps_coupons_code_offer_color')) ?>">
 	<input type="hidden" id="plugin_url" value="<?php echo $this->pluginUrl?>"> 	
		<input type="hidden" name="link" id="link" value="<?php echo $link; ?>" />
	  	<input type="hidden" name="listname" id="listname" value="<?php echo $listname; ?>" />
	  	<div class="custom_css_coupon"  id="couponCode_<?php echo $listname; ?>">
	  	<?php
	  	$tag = sanitize_text_field( $tags );
	  	$limit = trim(get_option('wps_coupons_scroll_amount'));
	  	if($limit==""){
	  		$limit = "06";
	  	}	 
	  	$args =array(
	  		'posts_per_page'   => $limit,
	  		'post_status' => 'publish',
			'post_type'   => 'coupon',
			'meta_key'	  => 'meta_expiry',
			'orderby'     => 'meta_value',
		    'meta_query' => array(		     					
        							array(	 'key' => 'meta_expiry',
         									 'value' => date("Y-m-d"),
           									 'compare' => '>=',            									 
           									   
       									 )
   									),		    							
     	);
     		
     	
     	if ( $tag!='Unknown' ) {
   			$args['tax_query'][] = array(
     					'taxonomy' => 'coupon_post_tag',
     					'field' => 'slug',
     					'terms' => $tag
   					 );
  		}
     	$totalRec  = get_posts($args);
		$totalRecCount = count($totalRec);
		$counterId = 0;
		$custom_flag = 0; 
		$wps_coupon_management = new WPS_Coupon();
		for($coupon=0;$coupon<$totalRecCount;$coupon++) {
			ob_start();
			$counterId++;
	 		$data = get_post_meta($totalRec[$coupon]->ID, '', $totalRec[$coupon]->ID);
            $formated_date = date(get_option('date_format'),strtotime($data['meta_expiry'][0]));
            $couponId = $totalRec[$coupon]->ID;
            $custom_flag_array = $wps_coupon_management->wps_template_custom_flag($couponId); 
            $custom_flag =  key($custom_flag_array); 
            $stylemeta_content = $custom_flag_array[$custom_flag];
            if($custom_flag==1){
			 	$wps_coupon_management->set_coupon_style($couponId,$stylemeta_content,$counterId);
			}
			if($custom_flag==0){          
           ?>
         <div class=" <?php echo 'custom_style'.$couponId; ?> coupon abstractview  postedCoupon_<?php echo $listname; ?>" id="<?php echo $counterId ?>">
		  	<div class="inner">
			    <div class="subject">
			    		<a rel="nofollow" title="<?php echo $totalRec[$coupon]->post_title; ?>"  <?php echo $target_str = ($data['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>  href="<?php echo $data['meta_coupon_code_link'][0];?>" class="thumb">
			    		<?php if($data['upload_image'][0]!=""){ ?>
			    			<img width="162px" alt="<?php echo $totalRec[$coupon]->post_title ?>" src="<?php echo $data['upload_image'][0]; ?>">
			    			<?php } else{ ?>
			    			<img width="162px" alt="<?php echo $totalRec[$coupon]->post_title ?>" src="<?php echo plugins_url('/images/no_image_flat.png', __FILE__); ?>">
			    			<?php }?>
			    		</a>
			     </div>
		    <div class="detail">
		        <div class="codeview" title="<?php echo __( 'Coupon Code', 'coupontext' );?>"><a <?php echo $target_str = ($data['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>   href="<?php echo $data['meta_coupon_code_link'][0];?>"><strong><?php echo $data['meta_coupon_code'][0]; ?></strong></a></div>
		       <div class="box_detail" ><h5 id="box_detail_coupontitle" class="couponTitle"><a  <?php echo $target_str = ($data['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>   href="<?php echo $data['meta_coupon_code_link'][0];?>" ><?php echo $this->wps_coupon_title_trim($totalRec[$coupon]->post_title); ?></a></h5> <p class="exp_date"><?php echo __( 'Expire On: ', 'coupontext' ).'  <b>'.$formated_date.'</b>'?></p>
		      <p class="merchantLink"><a href="#view_details_<?php echo $counterId;?>" class="inline_content_<?php echo $counterId; ?>"><?php echo __( 'View Details', 'coupontext' );?></a></p>
		       <?php
		       echo $wps_coupon_management->wps_get_tags($totalRec[$coupon]->ID);
		       ?>
		      </div>
		    </div>
		    <div class="break"></div>
		  </div>
		  <?php
  		}
		?>
		 
		<script>
			jQuery(document).ready(function() { 
			jQuery(".inline_content_<?php echo $counterId;?>").click(function(){
			jQuery(".inline_content_<?php echo $counterId;?>").colorbox({inline:true, width:"80%"});
			});
		});
		</script>
			<div class="div_coupon_popup" ><div id="view_details_<?php echo $counterId; ?>" class="coupon_detail_popup" >
			<div><strong><?php echo __( 'Coupon Details', 'coupontext' );?></strong></div>
			<div class="list_coupon_popup"><?php echo $totalRec[$coupon]->post_title;?></div>
			<div class="list_coupon_popup"><?php echo __( 'Expire On: ', 'coupontext' ).'  <b>'.$formated_date.'</b>'; ?></div>
			<div class="list_coupon_popup"><?php echo trim($data['meta-editor-coupon'][0]);?></div>
			</div></div>
		  		<?php
	  	}
	  	?>
	  	<div id="loadMoreCoupons" class="div_loadMoreCoupons" ></div>
	  	</div>
		<?php
	}
//*********** Front end coupon listing start ********************//
	function wps_coupon_shortcode($atts)
	{
		global $post;
		$id = trim($atts['id']);
		if(get_post_type($id)=='coupon'){
			$output = $this->get_single_wps_coupon($id);
		}else{
			$output = '';
		}
	    return $output;
	}
	
	function get_single_wps_coupon($id){
			ob_start();	
			wp_enqueue_script('general', plugins_url('/js/general.js', __FILE__));		
			$wps_coupon_management = new WPS_Coupon(); 
			$meta = get_post_meta($id);
			$formated_date = date(get_option('date_format'),strtotime($meta['meta_expiry'][0]));
			$output = "";
			if (strtotime($meta['meta_expiry'][0])>= mktime(0, 0, 0)) {
			$a_target = ($meta['meta_coupon_code_link'][0]!='') ? ' target=_blank ' : '';
			$couponId = $id;
			$default_style_library_css = trim(get_option('default_style_library_css'));
			$custom_flag_array = $wps_coupon_management->wps_template_custom_flag($couponId); 
		    $custom_flag =  key($custom_flag_array); 
            $stylemeta_content = $custom_flag_array[$custom_flag];
            $counterId = 1;
            
         	if($custom_flag==1){
			 	$wps_coupon_management->set_coupon_style($couponId,$stylemeta_content,$counterId);
			}
			if($custom_flag==0){    
			$output .='<div class="coupon abstractview postedCoupon" id="'.$id.'">
			  	       <div class="inner">
				       <div class="subject">
				       <a rel="nofollow" title="'.get_the_title($id).'"'.$a_target.' href="'.$meta['meta_coupon_code_link'][0].'" class="thumb">';
		    ?>
			<?php if($meta['upload_image'][0]!=""){
			$output .='<img width="162px" alt="'.get_the_title($id).'" src="'.$meta['upload_image'][0].'">';
			} else{ 
			$output .='<img width="162px" height="65px" alt="'.get_the_title($id).'" src="'.plugins_url('/images/no_image_flat.png', __FILE__).'">';
			} 
			$output .='</a>
				     </div>
			         <div class="detail">
			         <div class="codeview" title="'.__( 'Coupon Code', 'coupontext' ).'"><a '.$a_target.' href="'.$meta['meta_coupon_code_link'][0].'"  ><strong>'.$meta['meta_coupon_code'][0].'</strong></a></div>
			         <div class="box_detail" ><h5 id="box_detail_coupontitle" class="couponTitle"><a '.$a_target.'  href="'.$meta['meta_coupon_code_link'][0].'" >'.$this->wps_coupon_title_trim(get_the_title($id)).'</a></h5> <p class="exp_date">'.__( 'Expire On: ', 'coupontext' ).'  <b>'.$formated_date.'</b></p>
			         <p class="merchantLink"><a href="#view_details_'.$counterId.'" class="inline_content_'.$counterId.'">'.__( 'View Details', 'coupontext' ).'</a></p>
			         '.$wps_coupon_management->wps_get_tags($id).'
			         </div>
			         
			         </div>
			         <div class="break"></div>
			         </div>
			         </div>';
			}
			$output .='<script>
				jQuery(document).ready(function() { 
					jQuery(".inline_content_'.$counterId.'").click(function(){
					jQuery(".inline_content_'.$counterId.'").colorbox({inline:true, width:"30%"});
				});
			});
			</script>
			
				<div class="div_coupon_popup" ><div id="view_details_'.$counterId.'" class="coupon_detail_popup"  >
				<div><strong>'. __( 'Coupon Details', 'coupontext' ).'</strong></div>
				<div class="list_coupon_popup" >'.get_the_title($id).'</div>
				<div class="list_coupon_popup" >'.__( 'Expire On: ', 'coupontext' ).'  <b>'.$formated_date.'</b></div>
				<div class="list_coupon_popup" >'.trim($meta['meta-editor-coupon'][0]).'</div>
				</div></div>';
				}else{
					$output ='';
				}
				$output .='<input type="hidden" id="is_single" value="1">';
			    return $output;
	}
	function wps_coupon_template_include( $template_path ) {
			if ( get_post_type() == 'coupon' ) {
				if(is_archive()) {
					$template_path = plugin_dir_path( __FILE__ ) .'/coupons-by-tag.php';
				}	
				if ( is_single() ) {
					if ( $theme_file = locate_template( array
					( 'single-coupon.php' ) ) ) {
						$template_path = $theme_file;
					} else {
						$template_path = plugin_dir_path( __FILE__ ) .'/single-coupon.php';
					}
				}
			}
		 	return $template_path;
	}
	function wps_coupon_title_trim($str){
			if(strlen($str)<18){
				return $str;
			}else{
				$str = substr($str,0,18)." ...";
				return $str;
			}
	}
	
	/** function for check template from coupon or settings  **/
	function wps_template_custom_flag($couponId,$custom_flag=0){
			$flag_content_array = array();
			$default_style_library_css = trim(get_option('default_style_library_css'));
			$array_stylemeta = get_post_meta($couponId,'_wps_coupon_styling_screen',false);
          	if(count( $array_stylemeta ) > 0 && !in_array('none',$array_stylemeta) ) { $custom_flag =1; }
	           if($default_style_library_css!='' &&  $default_style_library_css != 'none' ){
	  	             $default_coupon_css = 1;
	  	             $custom_flag =1;
	  	             $stylemeta_default = $default_style_library_css ;
	            }
			$wps_coupon_management = new WPS_Coupon(); 
			$template_result = $wps_coupon_management->get_template_name($wps_coupon_management->pluginPath);
			$stylemeta_content ='';
			if($custom_flag==1){
			$stylemeta = get_post_meta($couponId,'_wps_coupon_styling_screen',true);
			if( $stylemeta == 'none' || $stylemeta =='' ) { $stylemeta = $stylemeta_default;  } 
			foreach ($template_result as $childKey => $childArray) {
				    $childArrayx = array_values($childArray);
				    if ($childArrayx[0] == $stylemeta) {
				    $template = array_keys($childArray);
				    $template_file = $template[0];
				    $template_contents = file_get_contents($template_file);
				    $filter_template_content = preg_replace("/(\s+)\/\*([^\/]*)\*\/(\s+)/s","",$template_contents);
				    $stylemeta_content = $filter_template_content;
				   }
				  }
			}
			$flag_content_array = array($custom_flag => $stylemeta_content); 
			return $flag_content_array;	                
	}
/** function for get all tags of coupon  **/	
	function wps_get_tags($post_id){
				$output = '';
	            $terms = wp_get_post_terms( $post_id, 'coupon_post_tag'); 
		        $total = count($terms);
		        $count = 0;
				if(!empty($terms)){
				 	foreach($terms as $term){
				 		if($count==0) { $output .= ' <span class="c_tags"> '.__('Tags : ', 'coupontext');  }
						$count++;
						$output .= ' <a href="' . esc_attr(get_term_link($term, 'category')) . '" title="' . sprintf( __( "View all coupons in %s" ), $term->name ) . '" ' . '>' . $term->name.'</a>   ';
						 if($count < $total ) $output .= ' , ';
						 if($count == $total) { $output .= '</span>'; }
					}
				 
				 }
				return $output;			 
	}
/** function for choose template  **/		
	function wps_coupon_template_selector($results,$selected='' ) {
			$output = '';
			if (count($results)) {
				foreach ($results as $key=>$result) {
						$value_array = array_values($result);
					    $value = $value_array[0];
						$checked = ( $selected == $value )?' selected="selected"':'';	
					    $output.= '<option value="'.$value.'"'.$checked.'>'. ($value) .'</option>'."\n";
				}
			} 
		  return $output;	
	}
	
/** function for choose color  **/		
	function wps_coupon_template_color($selected='' ) {
			$output = '';
			
			
				//$results = array("White","Gray","Yellow");
				$results = array("#FE2E2E"=>"Red","#FFFF00"=>"Yellow","#0000FF"=>"Blue");	
				
				foreach ($results as $key=>$result) {						
					    					    
						$checked = ( $selected == $key )?' selected="selected"':'';	
					    $output.= '<option value="'.$key.'"'.$checked.'>'. ($result) .'</option>'."\n";
				}
			 
		  return $output;	
	}
	
/** function for get all templates files   **/	
	function get_template_name($pluginPath='') {
			if($pluginPath == ''){
			  $back_trace = debug_backtrace();
			  $pluginPath = $back_trace[1]['object']->pluginPath;
			}
			
			$template_array = array();
			$template_called_file = $pluginPath.'templates/';
			$files = array_diff(scandir($template_called_file), array('..', '.'));
			foreach ( $files as $file ) {
			  	$template_file = $template_called_file.$file;
				$template_contents = file_get_contents($template_file) ;
				preg_match_all("(Template Name:(.*)\n)siU",$template_contents,$template_name);
				$template_name = trim($template_name[1][0]);
				$template_array[] = array($template_file=>$template_name);
			}
	    	return $template_array;
	}

/** function for all coupon codes added in template  **/	
	function shortcode_coupon($coupon,$content,$counterId){
				$wps_coupon_management = new WPS_Coupon(); 
				$coupon_id = $coupon->ID;
				$meta = get_post_meta($coupon_id);
				
				
				
				$themeTemplate =  get_option('default_style_library_css');
				//$themeTemplate =  'Default Theme';
				
		 if($themeTemplate == 'Default Theme')
				{
					wp_enqueue_style('defaultcolorbox', plugins_url('/css/defaultcolorbox.css', __FILE__));
					wp_enqueue_style('theme4', plugins_url('/css/style4.css', __FILE__));
					$coupon_image ='<a rel="nofollow" title="'.get_the_title($id).'"'.$a_target.' href="'.$meta['meta_coupon_code_link'][0].'" class="thumb">';
					if($meta['upload_image'][0]!="")
					{
						$coupon_image .='<img width="452" height="226" alt="'.get_the_title($id).'" src="'.$meta['upload_image'][0].'">';
					} 
					else
					{ 
						$coupon_image .='<img width="452" height="226" alt="'.get_the_title($id).'" src="'.plugins_url('/images/no_image_flat.png', __FILE__).'">';
					} 
					$coupon_image .='</a>';
					$formated_date = date(get_option('date_format'),strtotime($meta['meta_expiry'][0]));
					$coupon_code =  '<a '.$a_target.' href="'.$meta['meta_coupon_code_link'][0].'"  ><strong>'.$meta['meta_coupon_code'][0].'</strong></a>';
					
					$formatted_date = 'Expires: <span> '.$formated_date.' </span>';
					$coupon_tags = $wps_coupon_management->wps_get_tags($coupon_id);
					$coupon_title = '<a '.$a_target.' href="'.$meta['meta_coupon_code_link'][0].'" >'.$wps_coupon_management->wps_coupon_title_trim(get_the_title($coupon_id)).'</a>';
					// $formatted_date = '<p class="exp_date">'.__( 'Expire On: ', 'coupontext' ).'  <b>'.$formated_date.'</b></p>';
					$Coupon_code_image = '';
					$coupon_offer = $meta['wps_coupon_offer'][0];
					$coupon_offer = '<div class="offer-dis">'.$coupon_offer.'</div>';
					
					$Description = $meta['meta-editor-coupon'][0];
					if(strlen($Description) > 350) 
					{ $ShortDesc =  'Description : <span class="grey-clr">'.substr($Description,0,350).'...</span>'; }
					else { $ShortDesc = 'Description : <span class="grey-clr">'.$Description.'</span>'; }
					
					$lastname = $_GET['listname'];
					
				}
				
				$a_target = ($meta['meta_coupon_code_link'][0]!='') ? ' target=_blank ' : '';
								
				$patterns = array();
				$patterns[0] = '~\[coupon_id\]~';
				$patterns[1] = '~\[coupon_image\]~';
				$patterns[2] = '~\[coupon_code\]~';
				$patterns[3] = '~\[coupon_title\]~';
				$patterns[4] = '~\[coupon_expired\]~';
				$patterns[5] = '~\[coupon_tags\]~';
				$patterns[6] = '~\[coupon_counter\]~';
				$patterns[7] = '~\[coupon_code_image\]~';
				$patterns[8] = '~\[coupon_offer\]~';
				$patterns[9] = '~\[coupon_short_desc\]~';
				$patterns[10] = '~\[lastname\]~';
				
				$replacements = array();
				$replacements[0] = $coupon_id;
				$replacements[1] = $coupon_image;
				$replacements[2] = $coupon_code;
				$replacements[3] = $coupon_title;
				$replacements[4] = $formatted_date;
				$replacements[5] = $coupon_tags;
				$replacements[6] = $counterId;
				$replacements[7] = $Coupon_code_image;
				$replacements[8] = $coupon_offer;
				$replacements[9] = $ShortDesc;
				$replacements[10] = $lastname;
				
			    return preg_replace($patterns, $replacements, $content);	
	}
/** function for set coupon template  **/		
	function set_coupon_style($couponId,$default_coupon_css,$counterId=1){
				$coupon= get_post($couponId);
				$content = stripslashes($default_coupon_css);
				$wps_coupon_management = new WPS_Coupon();
			    echo $wps_coupon_management->shortcode_coupon($coupon,$content,$counterId);
	}
	
 	
} // end of Class

/** Initialized first action **/
	add_action("init", "register_wps_coupon_management_plugin");
	function register_wps_coupon_management_plugin() {
		   global $wps_coupon_management,$post;
		   $wps_coupon_management = new WPS_Coupon();
		   $wps_coupon_management->wps_coupon_custom_init();
	}
	register_activation_hook(__FILE__, 'wps_coupon_install');
	global $jal_db_version;
	$jal_db_version = "1.1";

/** Basic installation **/
	function wps_coupon_install() {
		  global $wpdb;
		  global $jal_db_version;
		  $data = array(
        'wps_coupons_border_color' => 'F24141',
        'wps_coupons_bg_color_top' => 'F24141',
        'wps_coupons_bg_color_bottom' => 'CC0000',
        'wps_coupons_content_bg_color' => 'F2F2F2',        
        'wps_coupons_font_color' => 'FFFFFF'        
    );
		  add_option('wps_coupons_scroll_amount','06');
		  update_option('default_style_library_css','Default Theme');
		  add_option('wps_copons_color', $data);
	}  
	$installed_ver = get_option("jal_db_version");
	if ($installed_ver != $jal_db_version) {
  		wps_coupon_install();
	}
/** function for displaying custom template box for coupon  **/		
	function wps_add_post_styling_inner_box() {
			global $post;
			$post_id = $post;
			$wps_coupon_management = new WPS_Coupon(); 
			if (is_object($post_id)) {
				$post_id = $post_id->ID;
			} else {
				$post_id = $post_id;
			}
			$wps_coupon_styling_screen = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_wps_coupon_styling_screen', true)));
			$wps_box_size = 6;
			$wps_coupon_management = new WPS_Coupon(); 
		    $template_result = $wps_coupon_management->get_template_name($wps_coupon_management->pluginPath);
		    ?>
			<p>
			<label for="wps_post_styling_screen"><?php _e('Custom Template For This Coupon', 'coupontext'); ?></label>
		  	<br />
			<select id="wps_coupon_styling_screen" name="wps_coupon_styling_screen">
			<option value="none"><?php _e( 'Select template', 'coupontext' ); ?></option>
			<?php echo $wps_coupon_management->wps_coupon_template_selector($template_result,$wps_coupon_styling_screen); ?>
			</select>			
		  	</p>
<?php
	}
	function wps_add_post_styling_outer_box() {
			//add_meta_box( 'poststyling_div','Custom Template For This Coupon', 'wps_add_post_styling_inner_box', 'coupon', 'advanced' );
			add_meta_box( 'postoffer_div','Enter Offer of this coupon <span class="error">*</span>', 'wps_add_post_offer_box', 'coupon', 'advanced' );
			
	}
	add_action('admin_menu','wps_add_post_styling_outer_box');
	
	function wps_add_post_offer_box()
	{
		global $post;
		$PostId = $post->ID;
		$PostOffer = get_post_meta($PostId,'wps_coupon_offer',true);
		if(!isset($PostOffer) || $PostOffer == '')  { $PostOffer = ''; }
		?>
		<input type="text" maxlength="9" name="wps_coupon_offer" id="wps_coupon_offer" class="required" value="<?php echo $PostOffer; ?>" /> ex.: 20% Off
		<?php
	}
	
/** function for update custom template for coupon  **/	
	function set_wps_coupon_styling( $id ) {
			$screen = $_POST[ 'wps_coupon_styling_screen' ];
			if(isset($screen) && $screen != '')
			{
				update_post_meta( $id, '_wps_coupon_styling_screen', $screen );
			}
			$Offer = $_POST[ 'wps_coupon_offer' ];
			if(isset($Offer) && $Offer != '')
			{
				update_post_meta( $id, 'wps_coupon_offer', $Offer );
			}
			
	}
	add_action( 'save_post', 'set_wps_coupon_styling' );
	
/** update color settings option values **/
 if(isset($_POST['hdn_var']) && $_POST['hdn_var'] == '1')
{
  $data = array(
        'wps_coupons_border_color' =>  $_POST['wps_copons_color']['wps_coupons_border_color'],
        'wps_coupons_bg_color_top' => $_POST['wps_copons_color']['wps_coupons_bg_color_top'],
        'wps_coupons_bg_color_bottom' =>  $_POST['wps_copons_color']['wps_coupons_bg_color_bottom'],
        'wps_coupons_content_bg_color' => $_POST['wps_copons_color']['wps_coupons_content_bg_color'],
        'wps_coupons_font_color' =>  $_POST['wps_copons_color']['wps_coupons_font_color']
        
    );
update_option('wps_copons_color', $data);
}