<?php
get_header(); ?>
<div id="primary">
  <div id="content" role="main">
    <article <?php post_class(); ?> >
	      <header class="entry-header">
	        <h1 class="entry-title"><?php $tag_name =$wp_query->query_vars; echo $tags = $tag_name['coupon_post_tag'];
	        ?></h1>
		 </header><!-- .entry-header -->
		 
		 
		 <div class="custom_css_coupon site-content" id="couponCode_all_coupons" style="width:100%;">
		 
	        <div class="entry-content">        			
	        <?php
	        do_shortcode("[wps-all-coupons-view tags=".$tags."]"); 
	        ?> 	        
			</div> 
			<div style="clear:both;"></div>
		 </div>
		 	
	</article>           
           
		</div><!-- #content -->
	</div><!-- #primary --> 
<?php get_footer(); ?>