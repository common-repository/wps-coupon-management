<?php get_header(); ?>
<div id="primary">
<div id="content" role="main">
<!-- Cycle through all posts -->
<?php 
$counterId =0;
while (have_posts()):
    the_post(); ?>
<?php $meta = get_post_meta(get_the_ID()); 

			++$counterId;
            $couponId = get_the_ID();
           
             /* code for load template for coupon  */
            $wps_coupon_management = new WPS_Coupon(); 
            $custom_flag_array = $wps_coupon_management->wps_template_custom_flag($couponId); 
            $custom_flag =  key($custom_flag_array); 
            $stylemeta_content = $custom_flag_array[$custom_flag];
         
            
           

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<header class="entry-header custom_css_coupon">
<!-- Display featured image in right-aligned floating div -->
<div class="single_coupon_output" >
<?php $formated_date = date(get_option('date_format'), strtotime($meta['meta_expiry'][0])); ?>
</div>

<?php
if($custom_flag==1)
{
			 	$wps_coupon_management->set_coupon_style($couponId,$stylemeta_content,$counterId);
}
if($custom_flag==0)
{          
?>

<div class="<?php echo 'custom_style'.$couponId; ?> coupon abstractview postedCoupon singlecoupon" id="1">

	  	<div class="inner">
		    <div class="subject">
		    		<a rel="nofollow" title="<?php echo the_title(); ?>" <?php echo ($meta['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>  href="<?php echo $meta['meta_coupon_code_link'][0]; ?>" class="thumb">
		    			<?php if ($meta['upload_image'][0] != "") { ?>
		    			<img width="162px" alt="<?php echo the_title(); ?>" src="<?php echo $meta['upload_image'][0]; ?>">
		    			<?php } else { ?>
		    			<img width="162px" alt="<?php echo the_title(); ?>" src="<?php echo
plugins_url('/images/no_image_flat.png', __file__); ?>">
		    			<?php } ?>
		    			
		    		</a>
		     </div>
	    <div class="detail">
	        <div class="codeview" title="<?php echo __('Coupon Code', 'coupontext'); ?>"><a <?php echo ($meta['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>   href="<?php echo $data['meta_coupon_code_link'][0];?>"><strong class="testing"><?php echo
$meta['meta_coupon_code'][0]; ?></a></strong></div>
	       <div class="box_detail" ><h5 id="box_detail_coupontitle" class="couponTitle"><a <?php echo ($meta['meta_coupon_code_link'][0]!='') ? 'target=_blank' : ''; ?>   href="<?php echo $data['meta_coupon_code_link'][0];?>"><?php echo
the_title(); ?></a></h5> <p class="exp_date"><?php echo
__('Expire On: ', 'coupontext') . '  <b>' . $formated_date; ?></b></p>
	      <p class="merchantLink"><a href="#view_details_1" class="inline_content_1"><?php echo
__('View Details', 'coupontext'); ?></a></p>

              
		       <?php
		      echo $wps_coupon_management->wps_get_tags(get_the_ID());
		       ?>
		      

	      </div>
	      
	    </div>
	    <div class="break"></div>
	  </div>
	</div>

	<?php
	 }
	?>
	
	<script>
		jQuery(document).ready(function() { 
			jQuery(".coupon.abstractview").addClass("singlecoupon");	
			jQuery(".inline_content_1").click(function(){
			jQuery(".inline_content_1").colorbox({inline:true, width:"30%"});
		});
	});
	</script>
		<div class="div_coupon_popup" ><div id="view_details_1" class="coupon_detail_popup"  >
		<div><strong><?php echo __('Coupon Details', 'coupontext'); ?></strong></div>
		<div class="list_coupon_popup"><?php echo the_title(); ?></div>
		<div class="list_coupon_popup"><?php echo __('Expire On: ', 'coupontext') .' <b>' . $formated_date . '</b>'; ?></div>
		<div class="list_coupon_popup"><?php echo trim($meta['meta-editor-coupon'][0]); ?></div>
		
		</div></div> 
</header>

</article>
<?php 
 
endwhile; ?>
</div>
</div>
<?php get_footer(); ?>