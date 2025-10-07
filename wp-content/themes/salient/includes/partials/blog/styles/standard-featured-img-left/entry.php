<?php
/**
* Default Post Format Template 
*
* Used when "Featured Image Left" standard style is selected.
*
* @version 10.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

global $post;
global $nectar_options;

$excerpt_length = ( ! empty( $nectar_options['blog_excerpt_length'] ) ) ? intval( $nectar_options['blog_excerpt_length'] ) : 15;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>  
  
  <div class="inner-wrap animated">
    
    <div class="post-content">
      
      <div class="article-content-wrap">
        
        <div class="post-featured-img-wrap">
          
          <?php
          
          // Featured image.
          if( has_post_thumbnail() ) { 
            echo '<a href="' . esc_url( get_permalink() ) . '"><span class="post-featured-img" style="background-image: url(' . get_the_post_thumbnail_url( $post->ID, 'wide_photography', array( 'title' => '' ) ) . ');"></span></a>';
          }
          
          ?>
        </div><!--post-featured-img-wrap-->
        
        <div class="post-content-wrap">
          
          <a class="entire-meta-link" href="<?php the_permalink(); ?>"></a>
          
          <?php 
          
          // Output categories.
          get_template_part( 'includes/partials/blog/styles/standard-featured-img-left/post-categories' );
          
          ?>
          
          <div class="post-header">
            <h3 class="title"><a href="<?php the_permalink(); ?>"> <?php the_title(); ?></a></h3>
          </div>
          
          <?php 
          
          // Excerpt.
          echo '<div class="excerpt">';
          echo nectar_excerpt( $excerpt_length );
          echo '</div>';
          
          // Bottom author link & date.
          get_template_part( 'includes/partials/blog/styles/standard-featured-img-left/post-bottom-meta' );
          
          ?>
          
        </div><!--post-content-wrap-->
        
      </div><!--/article-content-wrap-->
      
    </div><!--/post-content-->
    
  </div><!--/inner-wrap-->
  
</article>