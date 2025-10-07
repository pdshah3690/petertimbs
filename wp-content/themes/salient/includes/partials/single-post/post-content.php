<?php

/**

* Single Post Content

*

* @version 10.5

*/



// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {

  exit;

}



global $nectar_options;



$nectar_post_format            = get_post_format();

$hide_featrued_image           = ( ! empty( $nectar_options['blog_hide_featured_image'] ) ) ? $nectar_options['blog_hide_featured_image'] : '0';

$single_post_header_inherit_fi = ( ! empty( $nectar_options['blog_post_header_inherit_featured_image'] ) ) ? $nectar_options['blog_post_header_inherit_featured_image'] : '0';



?>



<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

  

  <div class="inner-wrap">



		<div class="post-content" data-hide-featured-media="<?php echo esc_attr( $hide_featrued_image ); ?>">

      

        <?php

        

        if( '1' !== $hide_featrued_image ) {

          

          // Featured Image.

          if( null === $nectar_post_format || false === $nectar_post_format || 'image' === $nectar_post_format) {

            if ( has_post_thumbnail() && '1' !== $single_post_header_inherit_fi ) {

              echo '<span class="post-featured-img">' . get_the_post_thumbnail( $post->ID, 'full', array( 'title' => '' ) ) . '</span>';

            }

          } 

          

          // Video.

          else if( 'video' === $nectar_post_format ) {

            get_template_part( 'includes/partials/blog/media/video-player' );

          }

          // Audio.

          else if( 'audio' === $nectar_post_format ) {

            get_template_part( 'includes/partials/blog/media/audio-player' );

          }

          

        }

        

        // Quote.

        if( 'quote' === $nectar_post_format ) {

          get_template_part( 'includes/partials/blog/media/quote' );

        }

        

        // Link.

        else if( 'link' === $nectar_post_format ) {

          get_template_part( 'includes/partials/blog/media/link' );

        }

          



        echo '<div class="content-inner">';

          

          // Post content.

          if( 'link' !== $nectar_post_format ) {

            the_content( '<span class="continue-reading">' . esc_html__( 'Read More', 'salient' ) . '</span>' );

          }

          

          // Tags.

          if ( '1' === $nectar_options['display_tags'] && has_tag() ) {

            echo '<div class="post-tags"><h4>' . esc_html__( 'Tags:', 'salient' ) . '</h4>';

            the_tags( '', '', '' );

            echo '<div class="clear"></div></div> ';

          }



        echo '</div>';

          



        

        ?>

        

      </div><!--/post-content-->

      

    </div><!--/inner-wrap-->

    

</article>