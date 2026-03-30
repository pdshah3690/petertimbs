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
class Nectar_Chat_Thread extends Nectar_Element_Base {

  /**
   * Generate styles for chat thread
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




  }

  /**
   * Generate CSS rules based on attributes
   *
   * @return void
   */
  private function generate_css_rules() {

    // Core CSS
    $this->add_css('.nectar-chat-thread {
        display: flex;
        flex-direction: column;
        gap: 1em;
    }

    .nectar-chat-thread__bubble {
        display: flex;
        flex-direction: row;
        align-items: flex-end;
        gap: 0.5em;
    }

    .nectar-chat-thread__bubble-content {
        padding: 0.9em 1.2em;
        line-height: 1.5;
        border-radius: var(--border-radius, 10px) var(--border-radius, 10px) var(--border-radius, 10px) 0;
    }

    .nectar-chat-thread__bubble-content ul {
        margin-bottom: 0;
    }

    .nectar-chat-thread__bubble-content:has(.nectar-chat-thread__bubble-name) .nectar-chat-thread__bubble-content__inner {
        margin-top: 0.3em;
    }

    .nectar-chat-thread__bubble-name {
        line-height: 1.5;
    }

    body .nectar-chat-thread .nectar-chat-thread__bubble-image {
        width: var(--image-size, 30px);
        height: var(--image-size, 30px);
        margin: 0;
        aspect-ratio: 1/1;
        object-fit: cover;
        border-radius: 100px;
    }

    .animation-resetting .nectar-chat-thread__bubble-name,
    .animation-resetting .nectar-chat-thread__word {
        transition: 0.1s ease;
        opacity: 0!important;
    }
    ');

    // Direction-specific CSS.
    $this->add_outgoing_styles();
    $this->add_incoming_styles();

    // Initial states to avoid flash before JS animations
    $this->add_initial_state_styles();

  }

  /**
   * Outgoing bubble styles
   *
   * @return void
   */
  private function add_outgoing_styles() {
    $this->add_css('.nectar-chat-thread__bubble[data-direction="outgoing"] {
        align-self: flex-end;
        display: flex;
        flex-direction: row-reverse;
    }
    .nectar-chat-thread__bubble[data-direction="outgoing"] .nectar-chat-thread__bubble-content {
        background-color: var(--outgoing-bubble-color, rgba(0, 0, 0, 0.1));
        color: var(--outgoing-text-color, #000);
        border-radius: var(--border-radius, 10px) var(--border-radius, 10px) 0 var(--border-radius, 10px);
    }');
  }

  /**
   * Incoming bubble styles
   *
   * @return void
   */
  private function add_incoming_styles() {
    $this->add_css('.nectar-chat-thread__bubble[data-direction="incoming"] {
        align-self: flex-start;
    }
    .nectar-chat-thread__bubble[data-direction="incoming"] .nectar-chat-thread__bubble-content {
        background-color: var(--incoming-bubble-color, rgba(0, 0, 0, 0));
        color: var(--incoming-text-color, #000);
    }');
  }

  /**
   * Initial CSS states to prevent flash before JS animations
   *
   * @return void
   */
  private function add_initial_state_styles() {
    $this->add_css('.nectar-chat-thread[data-animation="fade-in"] > .nectar-chat-thread__bubble {
        opacity: 0;
        transform: translateY(30px);
    }

    .nectar-chat-thread[data-animation="typing"][data-auto-height-animation="true"] > .nectar-chat-thread__bubble:not(:first-child) {
        opacity: 0;
        max-height: 0px;
    }

    .nectar-chat-thread[data-animation="typing"] > .nectar-chat-thread__bubble .nectar-chat-thread__bubble-content__inner {
        visibility: visible;
        max-height: none;
        overflow: visible;
    }

    /* Reveal from side via clip-path per direction on bubble content */
    .nectar-chat-thread[data-animation="typing"] > .nectar-chat-thread__bubble[data-direction="incoming"]:not(:first-child) > .nectar-chat-thread__bubble-content {
        clip-path: inset(100% 100% 0 0);
        -webkit-clip-path: inset(100% 100% 0 0);
        will-change: clip-path;
    }
    .nectar-chat-thread[data-animation="typing"] > .nectar-chat-thread__bubble[data-direction="outgoing"]:not(:first-child) > .nectar-chat-thread__bubble-content {
        clip-path: inset(100% 0 0 100%);
        -webkit-clip-path: inset(100% 0 0 100%);
        will-change: clip-path;
    }

    .nectar-chat-thread[data-animation="typing"] > .nectar-chat-thread__bubble:not(:first-child) .nectar-chat-thread__word {
        opacity: 0;
    }');
  }


}
