<?php
/**
* Default Post Format Template 
*
* Used when "Meta overlaid" masonry style is selected.
*
* @version 10.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

global $post;
global $nectar_options;

$masonry_size_pm             = get_post_meta( $post->ID, '_post_item_masonry_sizing', true );
$masonry_item_sizing         = ( ! empty( $masonry_size_pm ) ) ? $masonry_size_pm : 'regular';
$nectar_post_class_additions = $masonry_item_sizing . ' masonry-blog-item';

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $nectar_post_class_additions ); ?>>  
  
  <div class="inner-wrap animated">
    
    <div class="post-content">
      
      <div class="content-inner">
        
        <?php
        
        // Featured image.
        if( has_post_thumbnail() ) {
          
          $img_size = ( ! empty( $masonry_item_sizing ) ) ? $masonry_item_sizing : 'portfolio-thumb';
          
          if ( $img_size === 'regular' ) {
            $image_attrs = array(
              'title' => '',
              'sizes' => '(min-width: 1000px) 25vw, (min-width: 690px) 100vw, 100vw',
            );
          } elseif ( $img_size === 'wide_tall' ) {
            $image_attrs = array(
              'title' => '',
              'sizes' => '(min-width: 1000px) 50vw, (min-width: 690px) 100vw, 100vw',
            );
          } elseif ( $img_size === 'large_featured' ) {
            $image_attrs = array(
              'title' => '',
              'sizes' => '(min-width: 690px) 100vw, 100vw',
            );
          } else {
            $image_attrs = array(
              'title' => '',
              'sizes' => '(min-width: 1600px) 20vw, (min-width: 1300px) 25vw, (min-width: 1000px) 33.3vw, (min-width: 690px) 50vw, 100vw',
            );
          }
          
          echo '<a href="' . esc_url( get_permalink() ) . '"><span class="post-featured-img">' . get_the_post_thumbnail( $post->ID, $img_size, $image_attrs ) . '</span></a>';
          
        } else {
          // No image added.
          $img_size = ( ! empty( $masonry_item_sizing ) ) ? $masonry_item_sizing : 'portfolio-thumb';
          switch ( $img_size ) {
            case 'large_featured':
              $no_image_size = 'no-blog-item-large-featured.jpg';
            break;
            case 'wide_tall':
              $no_image_size = 'no-portfolio-item-tiny.jpg';
            break;
            default:
              $no_image_size = 'no-portfolio-item-tiny.jpg';
            break;
          }
          echo '<a href="' . esc_url( get_permalink() ) . '"><span class="post-featured-img"><img src="' . get_template_directory_uri() . '/img/' . $no_image_size . '" alt="no image added yet." /></span></a>';
        }
        
        ?>
        
        <div class="article-content-wrap">
          
          <div class="post-header">
            
            <h3 class="title"><a href="<?php the_permalink(); ?>"> <?php the_title(); ?></a></h3>
            
            <div class="post-meta">
              
              <div class="date">
                <?php echo get_the_date(); ?>
              </div>
              
            </div>
            
            <span class="meta-author"><span><?php echo esc_html__( 'By', 'salient' ); ?></span> <?php the_author_posts_link(); ?></span>
            
          </div><!--/post-header-->
          
        </div><!--article-content-wrap-->
        
      </div><!--/content-inner-->
      
    </div><!--/post-content-->
    
  </div><!--/inner-wrap-->
  
</article>