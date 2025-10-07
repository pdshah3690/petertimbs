<?php
/**
 * The template for displaying single posts.
 *
 * @package Salient WordPress Theme
 * @version 10.5
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
// Post header.
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		get_template_part( 'includes/partials/single-post/recipe-header' );
	endwhile;
endif;
?>

<div class="container-wrap" data-midnight="dark">
	<div class="container main-content">
		<div id="recipe-details-page" class="row">
			<?php if ( 'std-blog-fullwidth' !== $blog_type && '1' !== $hide_sidebar ) { ?>
				<div id="sidebar" data-nectar-ss="<?php echo esc_attr( $enable_ss ); ?>" class="col span_3 col_last">
					<?php get_template_part( 'includes/partials/single-post/recipe-sidebar' ); ?>
				</div><!--/sidebar-->
			<?php } ?>
			<?php
			$recipe_details = get_post_meta(get_the_ID());
			nectar_hook_before_content();
			$blog_standard_type = ( ! empty( $nectar_options['blog_standard_type'] ) ) ? $nectar_options['blog_standard_type'] : 'classic';

			$blog_type          = $nectar_options['blog_type'];
			if ( null === $blog_type ) {
				$blog_type = 'std-blog-sidebar';
			}

			if ( 'minimal' === $blog_standard_type && 'std-blog-sidebar' === $blog_type || 'std-blog-fullwidth' === $blog_type ) {
				$std_minimal_class = 'standard-minimal';
			} else {
				$std_minimal_class = '';
			}

			if ( 'std-blog-fullwidth' === $blog_type || '1' === $hide_sidebar ) {
				// No sidebar.
				echo '<div class="post-area col ' . $std_minimal_class . ' span_12 col_last">'; // WPCS: XSS ok.
			} else {
				// Sidebar.
				echo '<div class="post-area col ' . $std_minimal_class . ' span_9">'; // WPCS: XSS ok.
			}
			// Main content loop.
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					get_template_part( 'includes/partials/single-post/recipe-content' );
				 endwhile;
			 endif;
			wp_link_pages();
			nectar_hook_after_content(); 
			// Bottom social location for default minimal post header style.
			if ( 'default_minimal' === $blog_header_type && 
			'fixed' !== $blog_social_style && 
			'post' === get_post_type() ) {
				get_template_part( 'includes/partials/single-post/default-minimal-bottom-social' );
			}
			if ( true === $fullscreen_header && get_post_type() === 'post' ) {
				// Bottom meta bar when using fullscreen post header.
				get_template_part( 'includes/partials/single-post/post-meta-bar-ascend-skin' );
			}
			if ( 'ascend' !== $theme_skin ) {
				// Original/Material Theme Skin Author Bio.
				if ( ! empty( $nectar_options['author_bio'] ) && 
					$nectar_options['author_bio'] === '1' && 
					'post' == get_post_type() ) {
					 get_template_part( 'includes/partials/single-post/author-bio' );
				}
			}
			?>
		</div><!--/post-area-->
		</div><!--/row-->
		<div class="row">
			<?php 
				// Pagination/Related Posts.
				// nectar_next_post_display();
				// nectar_related_post_display();
				// Ascend Theme Skin Author Bio.
				if ( ! empty( $nectar_options['author_bio'] ) && 
					'1' === $nectar_options['author_bio'] && 
					'ascend' === $theme_skin && 
					'post' == get_post_type() ) {
					get_template_part( 'includes/partials/single-post/author-bio-ascend-skin' );
				}
			?>
			<div class="comments-section" data-author-bio="<?php if ( ! empty( $nectar_options['author_bio'] ) && $nectar_options['author_bio'] === '1' ) { echo 'true'; } else { echo 'false'; } ?>">
				<?php comments_template(); ?>
			</div>   
		</div>
	</div><!--/container-->
</div><!--/container-wrap-->
<?php if ( 'fixed' === $blog_social_style ) {
	  // Social sharing buttons.
		if( function_exists('nectar_social_sharing_output') ) {
			nectar_social_sharing_output('fixed');
		}
}
get_footer(); ?>