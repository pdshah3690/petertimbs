<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Nectar Sticky Media Sections Element Styles
 *
 * @since 18.0
 */
class Nectar_Sticky_Media_Sections extends Nectar_Element_Base {

  /**
   * Whether the page is using full screen rows
   *
   * @var bool
   */
  private $using_page_full_screen_rows = false;

  /**
   * Generate styles for sticky media sections
   *
   * @return void
   */
  public function generate_styles() {

    // Generate class names based on attributes
    $this->generate_class_names();

    // Generate CSS rules based on attributes
    $this->generate_css_rules();
  }

  /**
   * Generate CSS class names based on attributes
   *
   * @return void
   */
  private function generate_class_names() {

    // Media width class
    if ( $this->has_attr( 'media_width' ) ) {
      $this->add_class( 'media-width-' . $this->generate_unit_class( 'media_width', '' ) );
    }

    // Media height class
    if ( $this->has_attr( 'media_height' ) ) {
      $this->add_class( 'media-height-' . $this->generate_unit_class( 'media_height', '' ) );
    }

    // Content spacing class
    if ( $this->has_attr( 'content_spacing' ) ) {
      $this->add_class( 'content-spacing-' . $this->generate_unit_class( 'content_spacing', '' ) );
    }

    // Content position class
    if ( $this->has_attr( 'content_position' ) ) {
      $this->add_class( 'content-position-' . $this->get_attr_esc( 'content_position' ) );
    }

    // Mobile aspect ratio class
    if ( $this->has_attr( 'mobile_aspect_ratio' ) ) {
      $this->add_class( 'mobile-aspect-ratio-' . $this->get_attr_esc( 'mobile_aspect_ratio' ) );
    }

    // Border radius class
    if ( $this->has_attr( 'border_radius' ) && $this->get_attr( 'border_radius' ) !== 'none' ) {
      $this->add_class( 'media-border-radius-' . $this->get_attr_esc( 'border_radius' ) );
    }

    // Type class
    if ( $this->has_attr( 'type' ) && $this->get_attr( 'type' ) !== 'default' ) {
      $this->add_class( 'type--' . $this->get_attr_esc( 'type' ) );
    }

    // Page full screen rows
    $this->using_page_full_screen_rows = function_exists( 'nectar_using_page_full_screen_rows' ) && nectar_using_page_full_screen_rows();

    // Scroll pinned options
    if ( $this->has_attr( 'type', 'scroll-pinned-sections' ) ) {

      // Content alignment
      if ( $this->has_attr( 'content_alignment' ) && $this->get_attr( 'content_alignment' ) !== 'default' ) {
        $this->add_class( 'content-alignment-' . $this->get_attr_esc( 'content_alignment' ) );
      }

      // Section Height
      if ( $this->has_attr( 'section_height' ) && ! empty( $this->get_attr( 'section_height' ) ) ) {
        $this->add_class( 'section-height-' . $this->get_attr_esc( 'section_height' ) );
      }

      // Section Max Height
      if ( $this->has_attr( 'section_max_height_desktop' ) && ! empty( $this->get_attr( 'section_max_height_desktop' ) ) ) {
        $this->add_class( 'section-max-height-desktop-' . $this->generate_unit_class( 'section_max_height_desktop', '' ) );
      }

      if ( $this->has_attr( 'section_max_height_tablet' ) && ! empty( $this->get_attr( 'section_max_height_tablet' ) ) ) {
        $this->add_class( 'section-max-height-tablet-' . $this->generate_unit_class( 'section_max_height_tablet', '' ) );
      }

      if ( $this->has_attr( 'section_max_height_phone' ) && ! empty( $this->get_attr( 'section_max_height_phone' ) ) ) {
        $this->add_class( 'section-max-height-phone-' . $this->generate_unit_class( 'section_max_height_phone', '' ) );
      }

      // Substract nav height
      if ( $this->has_attr( 'subtract_nav_height', 'yes' ) ) {
        $this->add_class( 'subtract-nav-height' );
      }

      if ( $this->has_attr( 'navigation', 'yes' ) ) {
        $this->add_class( 'has-navigation' );
      }

      // navigation color.
      if ( $this->has_attr( 'navigation_color' ) && ! empty( $this->get_attr( 'navigation_color' ) ) ) {
        $color = ltrim( $this->get_attr( 'navigation_color' ), '#' );
        $this->add_class( 'navigation-color-' . esc_attr( $color ) );
      }

      // Effect
      if ( $this->has_attr( 'effect' ) ) {
        $this->add_class( 'effect-' . $this->get_attr_esc( 'effect' ) );
      }

      // Overlap Amount
      if ( $this->has_attr( 'effect', 'overlapping' ) ) {
        if ( $this->has_attr( 'overlapping_overlap_amount_desktop' ) && ! empty( $this->get_attr( 'overlapping_overlap_amount_desktop' ) ) ) {
          $this->add_class( 'overlapping-overlap-amount-desktop-' . $this->generate_unit_class( 'overlapping_overlap_amount_desktop', '' ) );
        }
        if ( $this->has_attr( 'overlapping_overlap_amount_tablet' ) && ! empty( $this->get_attr( 'overlapping_overlap_amount_tablet' ) ) ) {
          $this->add_class( 'overlapping-overlap-amount-tablet-' . $this->generate_unit_class( 'overlapping_overlap_amount_tablet', '' ) );
        }
        if ( $this->has_attr( 'overlapping_overlap_amount_phone' ) && ! empty( $this->get_attr( 'overlapping_overlap_amount_phone' ) ) ) {
          $this->add_class( 'overlapping-overlap-amount-phone-' . $this->generate_unit_class( 'overlapping_overlap_amount_phone', '' ) );
        }
      }

      // Stacking
      if ( $this->has_attr( 'stacking_effect', 'yes' ) ) {
        $this->add_class( 'stacking-effect' );
      }
    }

    // Horizontal scrolling
    if ( $this->has_attr( 'type', 'horizontal-scrolling' ) ) {

       // Effect
      if ( $this->has_attr( 'horizontal_effect' ) ) {
        $this->add_class( 'horizontal-scrolling-effect--' . $this->get_attr_esc( 'horizontal_effect' ) );
      }

      // Substract nav height
      if ( $this->has_attr( 'subtract_nav_height', 'yes' ) ) {
        $this->add_class( 'subtract-nav-height' );
      }

      // Content alignment
      if ( $this->has_attr( 'content_alignment' ) && $this->get_attr( 'content_alignment' ) !== 'default' ) {
        $this->add_class( 'content-alignment-' . $this->get_attr_esc( 'content_alignment' ) );
      }

      // horizontal_section_width
      $this->add_class( 'horizontal-section-width-' . $this->get_attr_esc( 'horizontal_section_width', '75' ) );


      // Section Height
      if ( $this->has_attr( 'section_height' ) && ! empty( $this->get_attr( 'section_height' ) ) ) {
        $this->add_class( 'section-height-' . $this->get_attr_esc( 'section_height' ) );
      }

      // Section Gap
      if ( $this->has_attr( 'horizontal_section_gap_desktop' ) ) {
        $this->add_class( 'section-gap-desktop-' . $this->generate_unit_class( 'horizontal_section_gap_desktop', '', '20px' ) );
      }

      // Max Sections for nth-child generation
      if ( $this->has_attr( 'max_sections' ) && ! empty( $this->get_attr( 'max_sections' ) ) ) {
        $this->add_class( 'max-sections-' . $this->get_attr_esc( 'max_sections' ) );
      }
    }

    // Effect Enabled - responsive classes for scroll-pinned-sections and layered-card-reveal
    if ( $this->has_attr( 'type', 'scroll-pinned-sections' ) || $this->has_attr( 'type', 'layered-card-reveal' ) ) {

      $devices = array( 'desktop', 'tablet', 'phone' );

      foreach ( $devices as $device ) {
        $param = 'effect_enabled_' . $device;
        if ( $this->has_attr( $param, 'disabled' ) ) {
          $this->add_class( 'disable-effect--' . $device );
        }
      }
    }

    // Layered card reveal
    if ( $this->has_attr( 'type', 'layered-card-reveal' ) ) {

       // Effect
      $this->add_class( 'layered-card-reveal-effect--' . $this->get_attr_esc( 'layered_card_reveal_effect', 'stack' ) );

      // Section Max Height
      if ( $this->has_attr( 'layered_card_reveal_width_desktop' ) && ! empty( $this->get_attr( 'layered_card_reveal_width_desktop' ) ) ) {
        $this->add_class( 'layered-card-reveal-width-desktop-' . $this->generate_unit_class( 'layered_card_reveal_width_desktop', '' ) );
      }

      if ( $this->has_attr( 'layered_card_reveal_width_tablet' ) && ! empty( $this->get_attr( 'layered_card_reveal_width_tablet' ) ) ) {
        $this->add_class( 'layered-card-reveal-width-tablet-' . $this->generate_unit_class( 'layered_card_reveal_width_tablet', '' ) );
      }

      if ( $this->has_attr( 'layered_card_reveal_width_phone' ) && ! empty( $this->get_attr( 'layered_card_reveal_width_phone' ) ) ) {
        $this->add_class( 'layered-card-reveal-width-phone-' . $this->generate_unit_class( 'layered_card_reveal_width_phone', '' ) );
      }

       // Content alignment
       if ( $this->has_attr( 'content_alignment' ) && $this->get_attr( 'content_alignment' ) !== 'default' ) {
        $this->add_class( 'content-alignment-' . $this->get_attr_esc( 'content_alignment' ) );
      }

      // Card Aspect Ratio
      if ( $this->has_attr( 'layered_card_reveal_aspect_ratio' ) ) {
        $this->add_class( 'layered-card-reveal-aspect-ratio-' . $this->get_attr_esc( 'layered_card_reveal_aspect_ratio' ) );
      }

      // Substract nav height
      if ( $this->has_attr( 'subtract_nav_height', 'yes' ) ) {
        $this->add_class( 'subtract-nav-height' );
      }
    }


  }

  /**
   * Generate CSS rules based on attributes
   *
   * @return void
   */
  private function generate_css_rules() {

    // Get attribute values with defaults
    $type = $this->get_attr( 'type', 'default' );
    $media_width = $this->get_attr( 'media_width', '50%' );
    $media_height = $this->get_attr( 'media_height', '50%' );
    $content_spacing = $this->get_attr( 'content_spacing', '20vh' );
    $content_position = $this->get_attr( 'content_position', 'right' );
    $mobile_aspect_ratio = $this->get_attr( 'mobile_aspect_ratio', '16-9' );
    $media_border_radius = $this->get_attr( 'border_radius' ) !== 'none' ? $this->get_attr( 'border_radius' ) : false;

    // Core CSS
    $this->add_css( '
      .nectar-sticky-media-section__content-section {
        position: relative;
      }

      @media only screen and (min-width: 1000px) {

        #boxed #ajax-content-wrap {
          overflow: visible!important;
          contain: paint;
        }

        html body,
        html body.compensate-for-scrollbar {
          overflow: visible;
        }

        :root {
          --scroll-bar-w: 0px;
        }

        .nectar-sticky-media-sections {
          display: flex;
          gap: 6%;
          flex-wrap: nowrap;
        }

        .nectar-sticky-media-sections.content-position-left {
          flex-direction: row-reverse;
        }

        .nectar-sticky-media-section__featured-media {
          position: sticky;
          top: var(--nectar-sticky-media-sections-vert-y);
          width: 50%;
        }

        .nectar-sticky-media-content__media-wrap {
          display: none;
        }
      }
    ' );

    // Default sticky media display
    if ( $type !== 'scroll-pinned-sections' && $type !== 'horizontal-scrolling' ) {
      $this->generate_default_sticky_media_css( $mobile_aspect_ratio, $content_spacing, $media_border_radius, $media_width, $media_height );
    }

    // Scroll pinned sticky media layout
    if ( $type === 'scroll-pinned-sections' ) {
      $this->generate_scroll_pinned_css( $media_border_radius );
    }

    // Horizontal scrolling
    if ( $type === 'horizontal-scrolling' ) {
      $this->generate_horizontal_scrolling_css( $media_border_radius);
    }

    if ( $this->has_attr( 'type', 'layered-card-reveal' ) ) {
      $this->generate_layered_card_reveal_css();
    }

    // Effect disabled CSS - counters the effect on specific viewports
    if ( $this->has_attr( 'type', 'scroll-pinned-sections' ) || $this->has_attr( 'type', 'layered-card-reveal' ) ) {
      $this->generate_effect_disabled_css();
    }


  }

  /**
   * Generate CSS for default sticky media display
   *
   * @param string $mobile_aspect_ratio
   * @param string $content_spacing
   * @param string|false $media_border_radius
   * @param string $media_width
   * @param string $media_height
   * @return void
   */
  private function generate_default_sticky_media_css( $mobile_aspect_ratio, $content_spacing, $media_border_radius, $media_width, $media_height ) {

    // Mobile aspect ratio
    $this->add_css( '.nectar-sticky-media-sections.mobile-aspect-ratio-' . $mobile_aspect_ratio . ' .nectar-sticky-media-content__media-wrap {
      padding-bottom: ' . $this->image_aspect_ratio( $mobile_aspect_ratio ) . ';
    }' );

    // Mobile responsive CSS
    $this->add_css( '@media only screen and (max-width: 999px) {
      .nectar-sticky-media-section__featured-media {
        display: none;
      }
      .nectar-sticky-media-content__media-wrap {
        display: block;
      }

      .nectar-sticky-media-content__media-wrap {
        position: relative;
        margin-bottom: 45px;
      }
      .nectar-sticky-media-sections .nectar-sticky-media-section__content-section:not(:last-child) {
        margin-bottom: 100px;
      }
      .nectar-sticky-media-sections .nectar-fancy-ul ul {
        margin-bottom: 0;
      }
    }

    .nectar-sticky-media-section__media-wrap {
      transition: opacity 0.2s ease 0.1s;
    }

    .nectar-sticky-media-section__media-wrap.active {
      transition: opacity 0.2s ease;
      z-index: 100;
    }

    .nectar-sticky-media-section__media-wrap:not(.active) {
      opacity: 0;
    }

    .nectar-sticky-media-section__media,
    .nectar-sticky-media-section__media-wrap {
      background-size: cover;
      background-position: center;
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
    }

    .nectar-sticky-media-section__media video {
      object-fit: cover;
      width: 100%;
      height: 100%;
    }

    .nectar-sticky-media-section__media video.nectar-lazy-video {
      transition: opacity 0.55s ease 0.25s;
      opacity: 0;
    }
    .nectar-sticky-media-section__media video.nectar-lazy-video.loaded {
      opacity: 1;
    }

    .nectar-sticky-media-section__media video.fit-contain {
      object-fit: contain;
    }

    .nectar-sticky-media-section__content {
      flex: 1;
    }' );

    // Spacing
    $this->add_css( '@media only screen and (min-width: 1000px) {
      .nectar-sticky-media-sections.content-spacing-' . esc_attr( $content_spacing ) . ' .nectar-sticky-media-section__content-section:not(:last-child) {
        margin-bottom: ' . esc_attr( $content_spacing ) . ';
      }
    }' );

    // Border Radius
    if ( $media_border_radius ) {
      $this->add_css( '.nectar-sticky-media-sections.media-border-radius-' . $media_border_radius . ' .nectar-sticky-media-section__media-wrap,
      .nectar-sticky-media-sections.media-border-radius-' . $media_border_radius . ' .nectar-sticky-media-content__media-wrap {
        border-radius: ' . $media_border_radius . ';
        overflow: hidden;
      }' );
    }

    // Width
    $this->add_css( '.nectar-sticky-media-sections.media-width-' . $this->percent_unit_type_class( esc_attr( $media_width ) ) . ' .nectar-sticky-media-section__featured-media {
      width: calc(' . esc_attr( $media_width ) . ' - 3%);
    }' );

    // Height
    $this->add_css( '.nectar-sticky-media-sections.media-height-' . $this->percent_unit_type_class( esc_attr( $media_height ) ) . ' .nectar-sticky-media-section__featured-media {
      height: ' . esc_attr( $media_height ) . ';
    }' );
  }

  /**
   * Generate CSS for scroll-pinned-sections type
   *
   * @param string|false $media_border_radius
   * @return void
   */
  private function generate_scroll_pinned_css( $media_border_radius ) {

    $content_alignment = $this->get_attr( 'content_alignment', 'default' );
    $section_height = $this->get_attr( 'section_height', '50vh' );
    $subtract_nav_height = $this->has_attr( 'subtract_nav_height', 'yes' );
    $using_navigation = $this->has_attr( 'navigation', 'yes' );

    // Core CSS
    $this->add_css( '
      @media only screen and (min-width: 1000px) {
        html body,
        html body.compensate-for-scrollbar {
          overflow: visible;
        }
      }
      @media only screen and (max-width: 999px) {
        html body,
        html body.compensate-for-scrollbar {
          overflow-x: clip;
          overflow-y: visible;
        }
      }

      :root {
        --scroll-bar-w: 0px;
      }

      .type--scroll-pinned-sections .nectar-sticky-media-content__media-wrap {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--nectar-bg-color);
      }
      .type--scroll-pinned-sections .nectar-sticky-media-content__media-wrap > * {
        background-size: cover;
        height: 100%;
        width: 100%;
      }
      .type--scroll-pinned-sections .nectar-sticky-media-content__media-wrap video {
        object-fit: cover;
        object-position: center;
        width: 100%;
        height: 100%;
      }

      .nectar-sticky-media-sections.type--scroll-pinned-sections {
        display: block;
        gap: 0;
        position: relative;
      }

      .nectar-sticky-media-sections.type--scroll-pinned-sections .nectar-sticky-media-section__content-section__wrap {
        transition: transform 0.25s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
      }

      .nectar-sticky-media-sections.type--scroll-pinned-sections .nectar-sticky-media-section__content-section {
        position: sticky;
      }

      .nectar-sticky-media-sections.type--scroll-pinned-sections .nectar-sticky-media-section__content-section-inner {
        z-index: 10;
        position: relative;
        display: flex;
        flex-direction: column;
      }

      #ajax-content-wrap .type--scroll-pinned-sections .nectar-sticky-media-section__content-section-inner > .vc_row.inner_row:last-child {
        margin-bottom: 0;
      }

      .stacking-effect.effect-scale_blur .nectar-sticky-media-section__content-section,
      .stacking-effect.effect-scale_fade .nectar-sticky-media-section__content-section,
      .stacking-effect.effect-scale .nectar-sticky-media-section__content-section {
        transform-origin: center top;
      }
    ' );

    if ( $this->has_attr( 'effect', 'scale_fade' ) ) {
      $this->add_css( '.stacking-effect.effect-scale_fade .nectar-sticky-media-section__content-section__wrap:after {
        position: absolute;
        pointer-events: none;
        content: "";
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        background-color: var(--scale-fade-color, rgba(0, 0, 0, 0.5));
        opacity: var(--progress, 0);
        border-radius: inherit;
        overflow: hidden;
      }' );
    }

    if ( $this->has_attr( 'effect', 'overlapping' ) ) {

      // default overlapping overlap amount
      $this->add_css('.stacking-effect.effect-overlapping {
        --overlapping-overlap-amount: 50px;
      }');
      // add css for overlapping overlap amount in css variables in each viewport
      $devices = array(
        'desktop' => ', print',
        'tablet' => 'and (max-width: 999px)',
        'phone' => 'and (max-width: 690px)'
      );

      foreach ( $devices as $device => $media_query_size ) {
        $param = 'overlapping_overlap_amount_' . $device;
        if ( $this->has_attr( $param ) && ! empty( $this->get_attr( $param ) ) ) {
          $this->add_css( '@media only screen ' . $media_query_size . ' {
            .stacking-effect.effect-overlapping.overlapping-overlap-amount-' . $device . '-' . $this->generate_unit_class( $param, '' ) . ' {
              --overlapping-overlap-amount: ' . $this->generate_unit_class( $param, '' ) . ';
            }
          }' );
        }
      }
    }

    // Navigation CSS
    if ( $using_navigation ) {
      $this->generate_navigation_css( $section_height, $subtract_nav_height );
    }

    // Border radius
    if ( $media_border_radius ) {
      $this->add_css( '.nectar-sticky-media-sections.type--scroll-pinned-sections.media-border-radius-' . $media_border_radius . ' .nectar-sticky-media-section__content-section__wrap,
      .nectar-sticky-media-sections.type--scroll-pinned-sections.media-border-radius-' . $media_border_radius . ' .nectar-sticky-media-content__media-wrap {
        border-radius: ' . $media_border_radius . ';
        overflow: hidden;
      }' );
    }

    // Section Max Height for different devices
    $this->generate_section_max_height_css();

    // Content alignment
    $this->generate_content_alignment_css( $content_alignment );

    // Section height
    if ( $section_height ) {
      $this->generate_section_height_css( $section_height, $subtract_nav_height );
    }
  }

  /**
   * Generate navigation CSS
   *
   * @param string $section_height
   * @param bool $subtract_nav_height
   * @return void
   */
  private function generate_navigation_css( $section_height, $subtract_nav_height ) {

    $navigation_negative_margin = 'calc(-1 * (var(--section-height) + var(--section-gap) ) )';
    $navigation_height = 'calc(var(--section-height) + var(--section-gap));';
    $navigation_color = $this->get_attr( 'navigation_color', '#000' );
    $navigation_color_trimmed = ltrim( $navigation_color, '#' );

    if ( $subtract_nav_height ) {
      $navigation_negative_margin = 'calc(-1 * (var(--section-height) - var(--header-nav-height) + var(--section-gap) ) )';
      $navigation_height = 'calc(var(--section-height) - var(--header-nav-height) + var(--section-gap));';
    }

    $this->add_css( '
      .nectar-sticky-media-sections.type--scroll-pinned-sections.has-navigation .nectar-sticky-media-section__content__wrap {
        margin-top: ' . $navigation_negative_margin . ';
      }

      .type--scroll-pinned-sections .nectar-sticky-media-section__navigation {
        position: sticky;
        top: var(--section-offset);
        left: calc(100%);
        width: 60px;
        z-index: 100;
        border-bottom: 0!important;
        height: ' . $navigation_height . ';
        display: flex;
        flex-direction: column;
        justify-content: center;
      }
      .type--scroll-pinned-sections .nectar-sticky-media-section__navigation__inner {
        padding-right: 30px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 7px;
        margin-top: calc(-1 * var(--section-gap) );
      }
      .type--scroll-pinned-sections .nectar-sticky-media-section__navigation__inner button {
        pointer-events: all;
        background-color: transparent;
        box-shadow: none;
        width: 25px;
        height: 25px;
        line-height: 23px;
        font-size: 11px;
        text-align: center;
        position: relative;
        border-radius: 100px!important;
        border: none;
        transition: background-color 0.25s ease, color 0.25s ease, opacity 0.25s ease;
      }
      .type--scroll-pinned-sections .nectar-sticky-media-section__navigation__inner button:after {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        content: "";
        box-sizing: border-box;
        border-radius: 100px;
        border: 1px solid #000;
        transition: opacity 0.25s ease;
        opacity: 0.5;
      }
      .type--scroll-pinned-sections .nectar-sticky-media-section__navigation__inner button:hover:not(.active):after {
        opacity: 1;
      }
      .type--scroll-pinned-sections .nectar-sticky-media-section__navigation__inner button.active {
        color: #fff;
        background-color: #000;
        cursor: pointer;
      }

      .type--scroll-pinned-sections.navigation-color-' . esc_attr( $navigation_color_trimmed ) . ' .nectar-sticky-media-section__navigation__inner button:not(.active) {
        color: ' . esc_attr( $navigation_color ) . ';
      }
      .type--scroll-pinned-sections.navigation-color-' . esc_attr( $navigation_color_trimmed ) . ' .nectar-sticky-media-section__navigation__inner button.active {
        background-color: ' . esc_attr( $navigation_color ) . ';
      }
      .type--scroll-pinned-sections.navigation-color-' . esc_attr( $navigation_color_trimmed ) . ' .nectar-sticky-media-section__navigation__inner button:after {
        border-color: ' . esc_attr( $navigation_color ) . ';
      }
    ' );

    if ( $navigation_color === '#ffffff' ) {
      $this->add_css( '.type--scroll-pinned-sections.navigation-color-' . esc_attr( $navigation_color_trimmed ) . ' .nectar-sticky-media-section__navigation__inner button.active {
         color: #000;
      }' );
    }
  }

  /**
   * Generate section max height CSS for different devices
   *
   * @return void
   */
  private function generate_section_max_height_css() {

    $devices = array(
      'desktop' => ', print',
      'tablet' => 'and (max-width: 999px)',
      'phone' => 'and (max-width: 690px)'
    );

    foreach ( $devices as $device => $media_query_size ) {
      $param = 'section_max_height_' . $device;
      if ( $this->has_attr( $param ) && ! empty( $this->get_attr( $param ) ) ) {
        $this->add_css( '@media only screen ' . $media_query_size . ' {
          .nectar-sticky-media-sections.type--scroll-pinned-sections.section-max-height-' . $device . '-' . $this->generate_unit_class( $param, '' ) . ' .nectar-sticky-media-section__content-section {
            max-height: ' . $this->percent_unit_type( esc_attr( $this->get_attr( $param ) ) ) . ';
          }
        }' );
      }
    }
  }

  /**
   * Generate content alignment CSS
   *
   * @param string $content_alignment
   * @return void
   */
  private function generate_content_alignment_css( $content_alignment ) {

    $type = $this->get_attr( 'type', 'scroll-pinned-sections' );

    if ( $content_alignment === 'middle' ) {
      $this->add_css( 'body .type--' . esc_attr( $type ) . '.content-alignment-middle .nectar-sticky-media-section__content-section__wrap {
        justify-content: center;
      }' );
    }

    if ( $content_alignment === 'bottom' ) {
      $this->add_css( 'body .type--' . esc_attr( $type ) . '.content-alignment-bottom .nectar-sticky-media-section__content-section__wrap {
        justify-content: flex-end;
      }' );
    }

    if ( $content_alignment === 'stretch' ) {
      $this->add_css( 'body .type--' . esc_attr( $type ) . '.content-alignment-stretch .nectar-sticky-media-section__content-section__wrap {
        justify-content: stretch;
      }
      .type--' . esc_attr( $type ) . '.content-alignment-stretch .nectar-sticky-media-section__content-section__wrap > * {
        flex: 1;
      }
      .type--' . esc_attr( $type ) . '.content-alignment-stretch .nectar-sticky-media-section__content-section__wrap > * > .wpb_row:is(:first-child):is(:last-child)  {
        flex: 1;
      }
      body .type--' . esc_attr( $type ) . '.content-alignment-stretch .nectar-sticky-media-section__content-section__wrap > * > .wpb_row:is(:first-child):is(:last-child) > .span_12  {
        height: 100%;
      }' );
    }
  }

  /**
   * Generate section height CSS
   *
   * @param string $section_height
   * @param bool $subtract_nav_height
   * @return void
   */
  private function generate_section_height_css( $section_height, $subtract_nav_height ) {

    $subtract_nav_height_class = '';
    $nav_height = '';
    $calc_section_height = $section_height;

    $percent_gap = ( 100 - intval( $section_height ) ) / 2;
    $calc_gap = 'calc( ' . $percent_gap . 'vh)';

    if ( $subtract_nav_height ) {
      $subtract_nav_height_class = '.subtract-nav-height';
      $nav_height = 'var(--header-nav-height)';
      $calc_section_height = 'calc( ' . $section_height . ' - ' . $nav_height . ' )';
      $calc_gap = 'calc( ' . $percent_gap . 'vh + ' . $nav_height . ' )';
    }

    $this->add_css( '
      .type--scroll-pinned-sections.section-height-' . esc_attr( $section_height ) . $subtract_nav_height_class . ' {
        --section-height: ' . esc_attr( $section_height ) . ';
        --section-offset: ' . $calc_gap . ';
        --section-gap: ' . $percent_gap . 'vh;
      }
      .type--scroll-pinned-sections.section-height-' . esc_attr( $section_height ) . $subtract_nav_height_class . ' .nectar-sticky-media-section__content-section {
        height: ' . esc_attr( $calc_section_height ) . ';
      }
      .type--scroll-pinned-sections.section-height-' . esc_attr( $section_height ) . $subtract_nav_height_class . ' .nectar-sticky-media-section__content-section {
        top: ' . $calc_gap . ';
      }
      .type--scroll-pinned-sections.section-height-' . esc_attr( $section_height ) . $subtract_nav_height_class . ' .nectar-sticky-media-section__content__wrap > div {
        border-bottom: ' . esc_attr( $percent_gap ) . 'vh solid transparent;
        box-sizing: content-box;
      }
        @media only screen and (max-width: 1000px) {
        .type--scroll-pinned-sections.section-height-' . esc_attr( $section_height ) . $subtract_nav_height_class . '[class*="max-height-tablet"] .nectar-sticky-media-section__content__wrap > div {
          border-bottom: 30px solid transparent;
        }
      }
       @media only screen and (max-width: 690px) {
        .type--scroll-pinned-sections.section-height-' . esc_attr( $section_height ) . $subtract_nav_height_class . '[class*="max-height-phone"] .nectar-sticky-media-section__content__wrap > div {
          border-bottom: 30px solid transparent;
        }
      }
    ' );
  }

  private function get_max_sections_count() {
    return 12;
  }



  /**
   * Generate CSS for layered-card-reveal type
   *
   * @return void
   */
  private function generate_layered_card_reveal_css() {


    // Dynamic CSS based on attributes
    $section_height = $this->get_attr( 'section_height', '50vh' );
    $section_width = $this->get_attr( 'horizontal_section_width', '75' );
    $section_gap = $this->get_attr( 'horizontal_section_gap_desktop', '20px' );

    $subtract_nav_height = $this->has_attr( 'subtract_nav_height', 'yes' );
    $content_alignment = $this->get_attr( 'content_alignment', 'default' );
    $card_width = $this->get_attr( 'layered_card_reveal_width_desktop', '25' );

    $subtract_nav_height_class = '';
    $nav_height = '0px';

    if ( $subtract_nav_height ) {
      $subtract_nav_height_class = '.subtract-nav-height';
      $nav_height = 'var(--header-nav-height, 100px)';
    }

    // Section height CSS - now includes gap class in selector
    $this->add_css( '
      .nectar-sticky-media-sections.type--layered-card-reveal'. $subtract_nav_height_class . ' {
        --section-offset: ' . $nav_height . ';
      }
    ' );

    // Per-element card width variable based on attribute.
    $devices = array(
      'desktop' => ', print',
      'tablet' => 'and (max-width: 999px)',
      'phone' => 'and (max-width: 690px)'
    );

    foreach ( $devices as $device => $media_query_size ) {
      $param = 'layered_card_reveal_width_' . $device;
      if ( $this->has_attr( $param ) && ! empty( $this->get_attr( $param ) ) ) {
        $this->add_css( '@media only screen ' . $media_query_size . ' {
          .nectar-sticky-media-sections.type--layered-card-reveal.layered-card-reveal-width-' . $device . '-' . $this->generate_unit_class( $param, '' ) . ' {
            --layered-card-width: ' . $this->percent_unit_type( esc_attr( $this->get_attr( $param ) ) ) . ';
          }
        }' );
      }
    }

    // Card Aspect Ratio
    if ( $this->has_attr( 'layered_card_reveal_aspect_ratio' ) ) {
      $this->add_css( '.nectar-sticky-media-sections.type--layered-card-reveal.layered-card-reveal-aspect-ratio-' . $this->get_attr_esc( 'layered_card_reveal_aspect_ratio' ) . ' {
        --layered-card-aspect-ratio: ' . str_replace( '-', '/', $this->get_attr_esc( 'layered_card_reveal_aspect_ratio' ) ) . ';
      }' );
    }

    // General
    $this->add_css( '

      @media only screen and (min-width: 1000px) {
        html body {
          overflow: visible;
        }
      }
      @media only screen and (max-width: 999px) {
        html body {
          overflow-x: clip;
          overflow-y: visible;
        }
      }

      .nectar-sticky-media-sections.type--layered-card-reveal .nectar-sticky-media-section__content__wrap {
        position: sticky;
        top: calc(var(--wp-admin--admin-bar--height, 0px) + var(--section-offset, 0px));
        gap: var(--section-gap, 20px);
        width: 100%;
        height: calc(100vh - var(--wp-admin--admin-bar--height, 0px) - var(--section-offset, 0px));
      }

    ' );


    // Staggered wave effect
    $this->add_css( '
    .type--layered-card-reveal.layered-card-reveal-effect--staggered-wave {
      position: relative;
      display: block;
      height: calc(var(--section-count, 1) * (100vh - var(--wp-admin--admin-bar--height, 0px) - var(--section-offset, 0px)));
    }
    .type--layered-card-reveal.layered-card-reveal-effect--staggered-wave .nectar-sticky-media-section__content__wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
      }
    .type--layered-card-reveal.layered-card-reveal-effect--staggered-wave .nectar-sticky-media-section__content-section {
      width: 25%;
      }
    ' );

    // Stack effect
    $this->add_css( '
    .type--layered-card-reveal.layered-card-reveal-effect--stack {
      position: relative;
      display: block;
      overflow-x: clip;
      height: calc(var(--section-count, 1) * (90vh - var(--wp-admin--admin-bar--height, 0px) - var(--section-offset, 0px)));
    }


      .type--layered-card-reveal.layered-card-reveal-effect--stack .nectar-sticky-media-section__content-section {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, calc(-50% + var(--stack-offset, 0px))) scale(var(--stack-scale, 1)) rotateZ(var(--stack-rot, 0deg));
        width: var(--layered-card-width, 40%);
        aspect-ratio: var(--layered-card-aspect-ratio, 1/1);
        transform-origin: center center;
      }

      .nectar-sticky-media-sections.type--layered-card-reveal .nectar-sticky-media-section__content-section:not(:nth-child(1)) {
        top: 50%;
      }
      .type--layered-card-reveal.layered-card-reveal-effect--stack .nectar-sticky-media-section__content-section {
        will-change: transform;
      }
      .type--layered-card-reveal.layered-card-reveal-effect--stack .nectar-sticky-media-section__content-section {
        transform: translate(-50%, calc(-50% + var(--stack-offset, 0px))) scale(var(--stack-scale, 1)) rotateZ(var(--stack-rot, 0deg));
      }

     .nectar-sticky-media-sections.type--layered-card-reveal {
        position: relative;
        display: block;
      }
      .type--layered-card-reveal .nectar-sticky-media-content__media-wrap {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--nectar-bg-color);
      }
      .type--layered-card-reveal .nectar-sticky-media-content__media-wrap > * {
        background-size: cover;
        height: 100%;
        width: 100%;
      }
      .type--layered-card-reveal .nectar-sticky-media-content__media-wrap video {
        object-fit: cover;
        object-position: center;
        width: 100%;
        height: 100%;
      }

      .type--layered-card-reveal .nectar-sticky-media-section__content-section__wrap {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
      }



      .type--layered-card-reveal .nectar-sticky-media-content__media-wrap {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--nectar-bg-color);
      }

      .type--layered-card-reveal .nectar-sticky-media-content__media-wrap > * {
        background-size: cover;
        height: 100%;
        width: 100%;
      }

      .type--layered-card-reveal .nectar-sticky-media-content__media-wrap video {
        object-fit: cover;
        object-position: center;
        width: 100%;
        height: 100%;
      }


      .nectar-sticky-media-sections.type--layered-card-reveal .nectar-sticky-media-section__content-section-inner {
        z-index: 10;
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
      }

      .nectar-sticky-media-sections.type--layered-card-reveal .nectar-sticky-media-section__content-section__wrap {
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
        box-sizing: border-box;
      }


    ' );

    if ( $this->using_page_full_screen_rows ) {
      $this->add_css( '
        #nectar_fullscreen_rows .type--layered-card-reveal.layered-card-reveal-effect--stack {
          height: calc(100vh - var(--wp-admin--admin-bar--height, 0px) - var(--section-offset, 0px));
        }
      ' );
    }

    // Content alignment
    $this->generate_content_alignment_css( $content_alignment );
  }

  /**
   * Generate CSS for horizontal-scrolling type
   *
   * @return void
   */
  private function generate_horizontal_scrolling_css( $media_border_radius ) {

    // Get the maximum number of sections to generate nth-child selectors for
    $max_sections = $this->get_max_sections_count();

    // Generate nth-child selectors dynamically
    $nth_child_css = '';
    for ( $i = 1; $i <= $max_sections; $i++ ) {
      $nth_child_css .= '
      .type--horizontal-scrolling .nectar-sticky-media-section__content__wrap > div:nth-child(' . $i . ') {
        z-index: ' . $i . ';
      }';
    }

    // Core CSS
    $this->add_css( '
      .nectar-sticky-media-sections.type--horizontal-scrolling {
        position: relative;
        display: block;
      }
      .type--horizontal-scrolling .nectar-sticky-media-content__media-wrap {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--nectar-bg-color);
      }
      .type--horizontal-scrolling .nectar-sticky-media-content__media-wrap > * {
        background-size: cover;
        height: 100%;
        width: 100%;
      }
      .type--horizontal-scrolling .nectar-sticky-media-content__media-wrap video {
        object-fit: cover;
        object-position: center;
        width: 100%;
        height: 100%;
      }' . $nth_child_css . '
      .nectar-sticky-media-sections.type--horizontal-scrolling .nectar-sticky-media-section__content-section-inner {
        z-index: 10;
        position: relative;
        display: flex;
        flex-direction: column;
      }
      .nectar-sticky-media-sections.type--horizontal-scrolling .nectar-sticky-media-section__content-section__wrap {
        transition: transform 0.25s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
      }

      @media only screen and (max-width: 999px) {
        body .nectar-sticky-media-sections .nectar-sticky-media-section__content-section:not(:last-child) {
          margin-bottom: 0px;
        }
      }

    ' );

    // Dynamic CSS based on attributes
    $section_height = $this->get_attr( 'section_height', '50vh' );
    $section_width = $this->get_attr( 'horizontal_section_width', '75' );
    $section_gap = $this->get_attr( 'horizontal_section_gap_desktop', '20px' );

    $subtract_nav_height = $this->has_attr( 'subtract_nav_height', 'yes' );
    $content_alignment = $this->get_attr( 'content_alignment', 'default' );

     // Content alignment
     $this->generate_content_alignment_css( $content_alignment );

    $subtract_nav_height_class = '';
    $nav_height = '0px';
    $calc_section_height = $section_height;


    // Calculate gap and adjust height accordingly
    $percent_gap = ( 100 - intval( $section_height ) ) / 2;
    $calc_gap = 'calc( ' . $percent_gap . 'vh)';

    // If gap is set, subtract gap * 2 from the section height
    if ( $this->has_attr( 'horizontal_section_gap_desktop' ) && ! empty( $this->get_attr( 'horizontal_section_gap_desktop' ) ) ) {
      $gap_value = $this->get_attr( 'horizontal_section_gap_desktop' );
      $gap_vh = $this->percent_unit_type( $gap_value );
      $calc_section_height = 'calc( ' . $section_height . ' - ( ' . $gap_vh . ' * 2 ) )';
    }

    // Initialize gap class variable
    $gap_class = '';

    if ( $subtract_nav_height ) {
      $subtract_nav_height_class = '.subtract-nav-height';
      $nav_height = 'var(--header-nav-height, 100px)';
      // Apply nav height subtraction after gap calculation
      if ( $this->has_attr( 'horizontal_section_gap_desktop' ) && ! empty( $this->get_attr( 'horizontal_section_gap_desktop' ) ) ) {
        $calc_section_height = 'calc( ' . $section_height . ' - ( ' . $gap_vh . ' * 2 ) - ' . $nav_height . ' )';
      } else {
        $calc_section_height = 'calc( ' . $section_height . ' - ' . $nav_height . ' )';
      }
      $calc_gap = 'calc( ' . $percent_gap . 'vh + ' . $nav_height . ' )';
    }

    // Check if horizontal_section_gap_desktop is set and add gap class
    if ( $this->has_attr( 'horizontal_section_gap_desktop' ) && ! empty( $this->get_attr( 'horizontal_section_gap_desktop' ) ) ) {
      $gap_class = '.section-gap-desktop-' . $this->generate_unit_class( 'horizontal_section_gap_desktop', '', '20px' );
    }

    // Section height CSS
    $this->add_css( '
      .nectar-sticky-media-sections.type--horizontal-scrolling.section-height-' . esc_attr( $section_height ) . $subtract_nav_height_class . $gap_class . ' {
        --section-height: ' . esc_attr( $calc_section_height ) . ';
        --section-offset: ' . $calc_gap . ';
      }
    ' );


    // Section gap CSS
    $this->add_css( '
      .nectar-sticky-media-sections.type--horizontal-scrolling.section-gap-desktop-' . $this->generate_unit_class( 'horizontal_section_gap_desktop', '', '20px' ) . ' {
        --section-gap: ' . esc_attr( $section_gap ) . ';
      }'
    );

    // Section Width CSS
    $this->add_css( '
    @media only screen and (min-width: 1000px) {
      .nectar-sticky-media-sections.type--horizontal-scrolling.horizontal-section-width-' . esc_attr( $section_width ) . ' {
        --horizontal-section-width: ' . esc_attr( $section_width ) . 'vw;
         height: calc(var(--section-count, 1) * ' . esc_attr( $section_width ) . 'vw);
      }
    }
    ' );

    if ( $this->using_page_full_screen_rows ) {
      $this->add_css( '
       @media only screen and (min-width: 1000px) {
          #nectar_fullscreen_rows .nectar-sticky-media-sections.type--horizontal-scrolling.horizontal-section-width-' . esc_attr( $section_width ) . ' {
            --horizontal-section-width: ' . esc_attr( $section_width ) . 'vw;
            height: ' . esc_attr( $section_width ) . 'vw;
          }
        }
        #nectar_fullscreen_rows .nectar-sticky-media-sections.type--horizontal-scrolling.section-height-' . esc_attr( $section_height ) . $subtract_nav_height_class . $gap_class . ' {
          --section-height: calc(100vh - var(--wp-admin--admin-bar--height, 0px) - var(--section-offset, 0px));
        }
        #nectar_fullscreen_rows .nectar-sticky-media-sections.type--horizontal-scrolling {
          overflow-x: scroll;
          max-width: 100vw;
         }
      ' );
    }

     // Section Height CSS
     $this->add_css( '
     @media only screen and (min-width: 1000px) {
     .nectar-sticky-media-sections.type--horizontal-scrolling .nectar-sticky-media-section__content-section {
       height: calc(var(--section-height, 50vh) - var(--wp-admin--admin-bar--height, 0px));
     }
    }
   ' );

    // General
    $this->add_css( '
      @media only screen and (min-width: 1000px) {
      .nectar-sticky-media-sections.type--horizontal-scrolling .nectar-sticky-media-section__content__wrap {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        width: max-content;
        position: sticky;
        top: calc(var(--wp-admin--admin-bar--height, 0px) + var(--section-offset, 0px) + var(--section-gap, 0px));
        gap: var(--section-gap, 20px);
      }
    }
      @media only screen and (max-width: 999px) {
        .nectar-sticky-media-sections.type--horizontal-scrolling .nectar-sticky-media-section__content__wrap {
           display: flex;
          flex-direction: column;
          gap: var(--section-gap, 20px);
        }
      }
    ' );

    // Horizontal section width specific CSS
    $this->add_css( '
    @media only screen and (min-width: 1000px) {
      .nectar-sticky-media-sections.type--horizontal-scrolling.horizontal-section-width-' . esc_attr( $this->get_attr( 'horizontal_section_width', '75' ) ) . ' .nectar-sticky-media-section__content-section {
        width: var(--horizontal-section-width, ' . esc_attr( $section_width ) . 'vw);
      }
    }
    ' );


     // Border radius
     if ( $media_border_radius ) {
      $this->add_css( '.nectar-sticky-media-sections.type--horizontal-scrolling.media-border-radius-' . $media_border_radius . ' .nectar-sticky-media-section__content-section__wrap,
      .nectar-sticky-media-sections.type--horizontal-scrolling.media-border-radius-' . $media_border_radius . ' .nectar-sticky-media-content__media-wrap {
        border-radius: ' . $media_border_radius . ';
        overflow: hidden;
      }' );
    }

  }

  /**
   * Generate CSS to disable effects on specific viewports
   *
   * @return void
   */
  private function generate_effect_disabled_css() {

    $devices = array(
      'desktop' => '@media only screen and (min-width: 1000px)',
      'tablet'  => '@media only screen and (max-width: 999px) and (min-width: 691px)',
      'phone'   => '@media only screen and (max-width: 690px)'
    );

    foreach ( $devices as $device => $media_query ) {
      $param = 'effect_enabled_' . $device;
      if ( $this->has_attr( $param, 'disabled' ) ) {
        $this->add_css( $media_query . ' {
          .nectar-sticky-media-sections.disable-effect--' . $device . ' {
            height: auto !important;
          }
          .nectar-sticky-media-sections.disable-effect--' . $device . ' .nectar-sticky-media-section__content-section {
            position: relative !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: auto !important;
            border-bottom: none !important;
            margin-bottom: 0 !important;
            transform: none !important;
          }
          .nectar-sticky-media-sections.disable-effect--' . $device . '  .nectar-sticky-media-section__content__wrap {
            display: flex;
            top: 0 !important;
            position: relative !important;
            flex-direction: column;
            height: auto !important;
            gap: min(var(--section-gap, 20px), 40px);
          }
        }' );
      }
    }
  }


}
