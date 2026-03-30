<?php
/**
 * Social Networks Helper Functions
 *
 * Functions to handle custom social networks from Redux options
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get custom social networks from Redux options
 *
 * @return array Array of social network data
 */
function nectar_get_custom_social_networks() {
    global $salient_redux;

    $custom_networks = array();

    if (isset($salient_redux['custom_social_networks']) && is_array($salient_redux['custom_social_networks'])) {
        foreach ($salient_redux['custom_social_networks'] as $network) {
            if (!empty($network['name']) && !empty($network['url'])) {
                $custom_networks[] = array(
                    'name' => sanitize_text_field($network['name']),
                    'url' => esc_url($network['url']),
                    'icon' => !empty($network['icon']) ? esc_url($network['icon']) : '',
                    'attachment_id' => !empty($network['attachment_id']) ? intval($network['attachment_id']) : 0,
                    'thumb' => !empty($network['thumb']) ? esc_url($network['thumb']) : '',
                    'width' => !empty($network['width']) ? intval($network['width']) : 0,
                    'height' => !empty($network['height']) ? intval($network['height']) : 0
                );
            }
        }
    }

    return $custom_networks;
}

/**
 * Display custom social networks in header
 *
 * @param string $container_class CSS class for the container
 * @param string $icon_class CSS class for the icons
 */
function nectar_display_custom_social_networks($container_class = 'custom-social-networks', $icon_class = 'custom-social-icon') {
    $custom_networks = nectar_get_custom_social_networks();

    if (!empty($custom_networks)) {
        echo '<div class="' . esc_attr($container_class) . '">';

        foreach ($custom_networks as $network) {
            $icon_url = !empty($network['thumb']) ? $network['thumb'] : $network['icon'];

            if (!empty($icon_url)) {
                echo '<a href="' . esc_url($network['url']) . '" target="_blank" rel="noopener noreferrer" class="' . esc_attr($icon_class) . '" title="' . esc_attr($network['name']) . '">';
                echo '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr($network['name']) . '" width="' . esc_attr($network['width']) . '" height="' . esc_attr($network['height']) . '" />';
                echo '</a>';
            } else {
                // Fallback to text if no icon
                echo '<a href="' . esc_url($network['url']) . '" target="_blank" rel="noopener noreferrer" class="' . esc_attr($icon_class) . ' text-only" title="' . esc_attr($network['name']) . '">';
                echo '<span>' . esc_html($network['name']) . '</span>';
                echo '</a>';
            }
        }

        echo '</div>';
    }
}

/**
 * Get all social networks (custom + predefined)
 *
 * @return array Combined array of all social networks
 */
function nectar_get_all_social_networks() {
    $custom_networks = nectar_get_custom_social_networks();
    $predefined_networks = nectar_get_predefined_social_networks();

    return array_merge($custom_networks, $predefined_networks);
}

/**
 * Get predefined social networks from Redux options
 *
 * @return array Array of predefined social network data
 */
function nectar_get_predefined_social_networks() {
    global $salient_redux;

    $predefined_networks = array();

    // Define the predefined social networks and their URLs
    $social_config = array(
        'use-facebook-icon-header' => array(
            'name' => 'Facebook',
            'url' => isset($salient_redux['facebook-url']) ? $salient_redux['facebook-url'] : '',
            'icon' => 'facebook'
        ),
        'use-twitter-icon-header' => array(
            'name' => 'Twitter',
            'url' => isset($salient_redux['twitter-url']) ? $salient_redux['twitter-url'] : '',
            'icon' => 'twitter'
        ),
        'use-x-twitter-icon-header' => array(
            'name' => 'X (Twitter)',
            'url' => isset($salient_redux['x-twitter-url']) ? $salient_redux['x-twitter-url'] : '',
            'icon' => 'x-twitter'
        ),
        'use-bluesky-icon-header' => array(
            'name' => 'Bluesky',
            'url' => isset($salient_redux['bluesky-url']) ? $salient_redux['bluesky-url'] : '',
            'icon' => 'bluesky'
        ),
        'use-google-plus-icon-header' => array(
            'name' => 'Google+',
            'url' => isset($salient_redux['google-plus-url']) ? $salient_redux['google-plus-url'] : '',
            'icon' => 'google-plus'
        ),
        'use-vimeo-icon-header' => array(
            'name' => 'Vimeo',
            'url' => isset($salient_redux['vimeo-url']) ? $salient_redux['vimeo-url'] : '',
            'icon' => 'vimeo'
        ),
        'use-dribbble-icon-header' => array(
            'name' => 'Dribbble',
            'url' => isset($salient_redux['dribbble-url']) ? $salient_redux['dribbble-url'] : '',
            'icon' => 'dribbble'
        ),
        'use-pinterest-icon-header' => array(
            'name' => 'Pinterest',
            'url' => isset($salient_redux['pinterest-url']) ? $salient_redux['pinterest-url'] : '',
            'icon' => 'pinterest'
        ),
        'use-youtube-icon-header' => array(
            'name' => 'YouTube',
            'url' => isset($salient_redux['youtube-url']) ? $salient_redux['youtube-url'] : '',
            'icon' => 'youtube'
        ),
        'use-tumblr-icon-header' => array(
            'name' => 'Tumblr',
            'url' => isset($salient_redux['tumblr-url']) ? $salient_redux['tumblr-url'] : '',
            'icon' => 'tumblr'
        ),
        'use-linkedin-icon-header' => array(
            'name' => 'LinkedIn',
            'url' => isset($salient_redux['linkedin-url']) ? $salient_redux['linkedin-url'] : '',
            'icon' => 'linkedin'
        ),
        'use-rss-icon-header' => array(
            'name' => 'RSS',
            'url' => isset($salient_redux['rss-url']) ? $salient_redux['rss-url'] : '',
            'icon' => 'rss'
        ),
        'use-behance-icon-header' => array(
            'name' => 'Behance',
            'url' => isset($salient_redux['behance-url']) ? $salient_redux['behance-url'] : '',
            'icon' => 'behance'
        ),
        'use-instagram-icon-header' => array(
            'name' => 'Instagram',
            'url' => isset($salient_redux['instagram-url']) ? $salient_redux['instagram-url'] : '',
            'icon' => 'instagram'
        ),
        'use-flickr-icon-header' => array(
            'name' => 'Flickr',
            'url' => isset($salient_redux['flickr-url']) ? $salient_redux['flickr-url'] : '',
            'icon' => 'flickr'
        ),
        'use-spotify-icon-header' => array(
            'name' => 'Spotify',
            'url' => isset($salient_redux['spotify-url']) ? $salient_redux['spotify-url'] : '',
            'icon' => 'spotify'
        ),
        'use-github-icon-header' => array(
            'name' => 'GitHub',
            'url' => isset($salient_redux['github-url']) ? $salient_redux['github-url'] : '',
            'icon' => 'github'
        ),
        'use-stackexchange-icon-header' => array(
            'name' => 'Stack Exchange',
            'url' => isset($salient_redux['stackexchange-url']) ? $salient_redux['stackexchange-url'] : '',
            'icon' => 'stackexchange'
        ),
        'use-soundcloud-icon-header' => array(
            'name' => 'SoundCloud',
            'url' => isset($salient_redux['soundcloud-url']) ? $salient_redux['soundcloud-url'] : '',
            'icon' => 'soundcloud'
        ),
        'use-vk-icon-header' => array(
            'name' => 'VK',
            'url' => isset($salient_redux['vk-url']) ? $salient_redux['vk-url'] : '',
            'icon' => 'vk'
        ),
        'use-vine-icon-header' => array(
            'name' => 'Vine',
            'url' => isset($salient_redux['vine-url']) ? $salient_redux['vine-url'] : '',
            'icon' => 'vine'
        ),
        'use-houzz-icon-header' => array(
            'name' => 'Houzz',
            'url' => isset($salient_redux['houzz-url']) ? $salient_redux['houzz-url'] : '',
            'icon' => 'houzz'
        ),
        'use-yelp-icon-header' => array(
            'name' => 'Yelp',
            'url' => isset($salient_redux['yelp-url']) ? $salient_redux['yelp-url'] : '',
            'icon' => 'yelp'
        )
    );

    foreach ($social_config as $option_key => $network_data) {
        if (isset($salient_redux[$option_key]) && $salient_redux[$option_key] == '1' && !empty($network_data['url'])) {
            $predefined_networks[] = array(
                'name' => $network_data['name'],
                'url' => esc_url($network_data['url']),
                'icon' => $network_data['icon'],
                'type' => 'predefined'
            );
        }
    }

    return $predefined_networks;
}



  if ( !function_exists('nectar_brands_icon_list') ) {

      function nectar_brands_icon_list() {
          return [
              'nectar-brands-applemusic' => "\e903",
              'nectar-brands-artstation' => "\e90b",
              'nectar-brands-behance' => "\e920",
              'nectar-brands-bluesky' => "\e919",
              'nectar-brands-deviantart' => "\e908",
              'nectar-brands-discord' => "\e90c",
              'nectar-brands-dribbble' => "\e924",
              'nectar-brands-email' => "\e901",
              'nectar-brands-facebook' => "\e926",
              'nectar-brands-flickr' => "\e91f",
              'nectar-brands-github' => "\e91d",
              'nectar-brands-googlepay' => "\e900",
              'nectar-brands-houzz' => "\e904",
              'nectar-brands-instagram' => "\e91e",
              'nectar-brands-mastodon' => "\e917",
              'nectar-brands-medium' => "\e914",
              'nectar-brands-messenger' => "\e90d",
              'nectar-brands-mixcloud' => "\e90e",
              'nectar-brands-patreon' => "\e912",
              'nectar-brands-phone' => "\e902",
              'nectar-brands-pinterest' => "\e923",
              'nectar-brands-reddit' => "\e906",
              'nectar-brands-rss' => "\e907",
              'nectar-brands-snapchat' => "\e910",
              'nectar-brands-soundcloud' => "\e91b",
              'nectar-brands-spotify' => "\e909",
              'nectar-brands-stackexchange' => "\e91c",
              'nectar-brands-strava' => "\e90a",
              'nectar-brands-threads' => "\e913",
              'nectar-brands-tiktok' => "\e90f",
              'nectar-brands-trustpilot' => "\e916",
              'nectar-brands-tumblr' => "\e921",
              'nectar-brands-twitch' => "\e905",
              'nectar-brands-vimeo' => "\e925",
              'nectar-brands-vk' => "\e91a",
              'nectar-brands-x-twitter' => "\e918",
              'nectar-brands-yelp' => "\e911",
              'nectar-brands-youtube' => "\e922",
          ];
      }
  }

