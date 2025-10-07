<?php
/**
 * Audio Post Format Template 
 *
 * Used when "Auto Masonry: Meta Overlaid Spaced" masonry style is selected.
 *
 * @version 10.5
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
global $nectar_options;

$nectar_post_class_additions = ' masonry-blog-item';

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $nectar_post_class_additions ); ?>>  
    
  <div class="inner-wrap animated">
    
    <div class="post-content">

      <div class="content-inner">
        
        <?php get_template_part( 'includes/partials/blog/media/play-button-transparent' ); ?>
        
        <a class="entire-meta-link" href="<?php the_permalink(); ?>"></a>
        
        <?php
        
          // Featured image.
          $image_attrs = array(
            'title' => '',
            'sizes' => '(min-width: 1600px) 20vw, (min-width: 1300px) 25vw, (min-width: 1000px) 33.3vw, (min-width: 690px) 50vw, 100vw',
          );
          if( has_post_thumbnail() ) { 
            echo '<span class="post-featured-img" style="background-image: url(' . get_the_post_thumbnail_url( $post->ID, 'medium_featured', array( 'title' => '' ) ) . ');"></span>';
          } else {
            echo '<span class="post-featured-img no-img"></span>';
          }

        ?>
        
        <div class="article-content-wrap">
          
          <?php 
          
          // Output categories.
          get_template_part( 'includes/partials/blog/styles/masonry-auto-meta-overlaid-spaced/post-categories' );
          
          ?>
          
          <div class="post-header">
            <h3 class="title"><a href="<?php the_permalink(); ?>"> <?php the_title(); ?></a></h3>
          </div>
          
        </div><!--article-content-wrap-->
        
      </div><!--/content-inner-->
        
    </div><!--/post-content-->
      
  </div><!--/inner-wrap-->
    
</article>