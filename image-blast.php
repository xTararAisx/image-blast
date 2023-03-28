<?php
/*
Plugin Name: Image Blast
Description: Busca imágenes en Google utilizando la API, las sube automáticamente a la biblioteca de medios de WordPress y reemplaza el shortcode [blast image="palabra_clave"] con una imagen encontrada. Incluye un panel de control para activar/desactivar su funcionalidad y personalizar opciones.
Version: 1.0
Author: Tony Ruiz
Text Domain: image-blast
*/

if (!class_exists('Image_Blast')) {
    class Image_Blast {
        public function __construct() {
            add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
            add_action('plugins_loaded', 'image_blast_load_textdomain');
            add_action('admin_menu', array($this, 'add_plugin_page'));
            add_action('admin_init', array($this, 'page_init'));
            add_shortcode('blast', array($this, 'google_image_search_shortcode'));
            add_action('save_post', array($this, 'replace_shortcode_with_image'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

        }

        public function add_plugin_page() {
            add_options_page('Image Blast', 'Image Blast', 'manage_options', 'image-blast', array($this, 'admin_page'));
        }

        
        public function admin_page() {
            ?>
            <div class="wrap">
                <h1>Image Blast</h1>
                <form method="post" action="options.php">
                    <?php
                        settings_fields('image_blast_options');
                        do_settings_sections('image-blast');
                        submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
        
        public function load_plugin_textdomain() {
    load_plugin_textdomain('image-blast', false, basename(dirname(__FILE__)) . '/languages/');
}

public function add_settings_link($links) {
    $settings_link_text = __('Settings', 'image-blast');
    $settings_link = '<a href="' . admin_url('options-general.php?page=image-blast') . '">' . $settings_link_text . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
public function page_init() {
    register_setting('image_blast_options', 'image_blast_example_shortcode');
    $shortcode_title = __('Shortcode Example', 'image-blast');
    add_settings_field('image_blast_example_shortcode', $shortcode_title, array($this, 'image_blast_example_shortcode_callback'), 'image-blast', 'image_blast_section');
    load_plugin_textdomain('image-blast', false, basename(dirname(__FILE__)) . '/languages/');
            register_setting('image_blast_options', 'image_blast_enabled');
            register_setting('image_blast_options', 'image_blast_api_key');
            register_setting('image_blast_options', 'image_blast_cx');
            register_setting('image_blast_options', 'image_blast_image_size');
            register_setting('image_blast_options', 'image_blast_custom_dimensions_enabled');
            register_setting('image_blast_options', 'image_blast_custom_width');
            register_setting('image_blast_options', 'image_blast_custom_height');

add_settings_section('image_blast_section', __('Settings', 'image-blast'), null, 'image-blast');

add_settings_field('image_blast_enabled', __('Activate Image Blast', 'image-blast'), array($this, 'image_blast_enabled_callback'), 'image-blast', 'image_blast_section');
add_settings_field('image_blast_api_key', __('API Key', 'image-blast'), array($this, 'image_blast_api_key_callback'), 'image-blast', 'image_blast_section');
add_settings_field('image_blast_cx', __('ID Google Search Engine', 'image-blast'), array($this, 'image_blast_cx_callback'), 'image-blast', 'image_blast_section');
add_settings_field('image_blast_image_size', __('Image size
', 'image-blast'), array($this, 'image_blast_image_size_callback'), 'image-blast', 'image_blast_section');
add_settings_field('image_blast_custom_dimensions_enabled', __('Enable custom dimensions', 'image-blast'), array($this, 'image_blast_custom_dimensions_enabled_callback'), 'image-blast', 'image_blast_section');
add_settings_field('image_blast_custom_width', __('Custom width', 'image-blast'), array($this, 'image_blast_custom_width_callback'), 'image-blast', 'image_blast_section');
add_settings_field('image_blast_custom_height', __('Custom height', 'image-blast'), array($this, 'image_blast_custom_height_callback'), 'image-blast', 'image_blast_section');

        }

        public function image_blast_enabled_callback() {
            echo '<input type="checkbox" name="image_blast_enabled" value="1" ' . checked(1, get_option('image_blast_enabled'), false) . '>';
        }

        public function image_blast_api_key_callback() {
            echo '<input type="text" name="image_blast_api_key" value="' . esc_attr(get_option('image_blast_api_key')) . '" size="40">';
        }

        public function image_blast_cx_callback() {
            echo '<input type="text" name="image_blast_cx" value="' . esc_attr(get_option('image_blast_cx')) . '" size="40">';
        }
        
public function image_blast_example_shortcode_callback() {
    $example_shortcode = '[blast image="Keyword"]';
    echo '<input type="text" value="' . esc_attr($example_shortcode) . '" readonly size="30"> <p class="description">' . __('Use this shortcode in your posts, replace "keyword" with your desired keyword', 'image-blast') . '</p>';
}


        
public function image_blast_image_size_callback() {
    $selected = get_option('image_blast_image_size', 'medium');
    $image_sizes = array('icon', 'small', 'medium', 'large', 'xlarge', 'xxlarge', 'huge');
    echo '<select name="image_blast_image_size">';
    foreach ($image_sizes as $size) {
        echo '<option value="' . $size . '"' . selected($selected, $size, false) . '>' . ucfirst($size) . '</option>';
    }
    echo '</select>';
}

        public function image_blast_custom_dimensions_enabled_callback() {
            echo '<input type="checkbox" name="image_blast_custom_dimensions_enabled" value="1" ' . checked(1, get_option('image_blast_custom_dimensions_enabled'), false) . '>';
        }

        public function image_blast_custom_width_callback() {
            echo '<input type="number" name="image_blast_custom_width" value="' . esc_attr(get_option('image_blast_custom_width')) . '" min="1">';
        }

        public function image_blast_custom_height_callback() {
            echo '<input type="number" name="image_blast_custom_height" value="' . esc_attr(get_option('image_blast_custom_height')) . '" min="1">';
        }

public function google_image_search_shortcode($atts) {
    if (!get_option('image_blast_enabled')) {
        return '';
    }

$a = shortcode_atts(array(
    'image' => 'default_keyword',
), $atts);

    error_log(print_r($a, true)); // Agrega esta línea para depurar el valor de $a['blast']


$image_url = $this->google_image_search($a['image']);

    $uploaded_image_id = $this->download_and_save_image($image_url, $a['blast']);

    if ($uploaded_image_id) {
        $size = get_option('image_blast_custom_dimensions_enabled') ? 'image_blast_custom' : 'full';
        $image = wp_get_attachment_image($uploaded_image_id, $size);
        return $image;
    } else {
        return '';
    }
}

public function replace_shortcode_with_image($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $post = get_post($post_id);

    if ($post && $post->post_type == 'post') {
        $content = $post->post_content;

        $updated_content = preg_replace_callback('/(\[blast image="([^"]*)"\])/', array($this, 'shortcode_to_image_tag'), $content);

        if ($content !== $updated_content) {
            remove_action('save_post', array($this, 'replace_shortcode_with_image'));
            wp_update_post(array('ID' => $post_id, 'post_content' => $updated_content));
            add_action('save_post', array($this, 'replace_shortcode_with_image'));
        }
    }
}




private function shortcode_to_image_tag($matches) {
    $shortcode = $matches[1];
    $keyword = $matches[2];

    $image_url = $this->google_image_search($keyword);
    $uploaded_image_id = $this->download_and_save_image($image_url, $keyword);

    if ($uploaded_image_id) {
        $size = get_option('image_blast_custom_dimensions_enabled') ? 'image_blast_custom' : 'full';
        $image = wp_get_attachment_image($uploaded_image_id, $size);
        return $image; // Retorna solo la imagen sin el shortcode.
    } else {
        return $shortcode;
    }
}


       

 private function google_image_search($keyword) {
    $api_key = get_option('image_blast_api_key');
    $search_engine_id = get_option('image_blast_cx');
    $img_size = get_option('image_blast_image_size', 'medium');

    $url = "https://www.googleapis.com/customsearch/v1?key={$api_key}&cx={$search_engine_id}&q={$keyword}&searchType=image&imgSize={$img_size}";

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return '';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['items'][0]['link'])) {
        return $data['items'][0]['link'];
    } else {
        if ($img_size === 'large') {
            // Intenta buscar imágenes de tamaño 'medium' si no se encuentran imágenes 'large'.
            $img_size = 'medium';
            $url = "https://www.googleapis.com/customsearch/v1?key={$api_key}&cx={$search_engine_id}&q={$keyword}&searchType=image&imgSize={$img_size}";
            $response = wp_remote_get($url);

            if (is_wp_error($response)) {
                return '';
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['items'][0]['link'])) {
                return $data['items'][0]['link'];
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
}





        private function download_and_save_image($image_url, $alt) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $post_id = get_the_ID();

            $tmp = download_url($image_url);

            $file_array = array(
                'name' => basename($image_url),
                'tmp_name' => $tmp,
            );

            if (is_wp_error($tmp)) {
                @unlink($file_array['tmp_name']);
                $file_array['tmp_name'] = '';
            }

            $uploaded_image_id = media_handle_sideload($file_array, $post_id);

            if (is_wp_error($uploaded_image_id)) {
                @unlink($file_array['tmp_name']);
                return false;
            }

            update_post_meta($uploaded_image_id, '_wp_attachment_image_alt', $alt);

            if (get_option('image_blast_custom_dimensions_enabled')) {
                $custom_width = get_option('image_blast_custom_width');
                $custom_height = get_option('image_blast_custom_height');
                $resized_image = image_make_intermediate_size(get_attached_file($uploaded_image_id), $custom_width, $custom_height, true);
                wp_update_attachment_metadata($uploaded_image_id, $resized_image);
            }
                if (get_option('image_blast_custom_dimensions_enabled')) {
        $custom_width = get_option('image_blast_custom_width');
        $custom_height = get_option('image_blast_custom_height');
        $resized_image = image_make_intermediate_size(get_attached_file($uploaded_image_id), $custom_width, $custom_height, true);
        if ($resized_image) {
            $custom_image_size_name = 'image_blast_custom';
            $metadata = wp_get_attachment_metadata($uploaded_image_id);
            $metadata['sizes'][$custom_image_size_name] = $resized_image;
            wp_update_attachment_metadata($uploaded_image_id, $metadata);
        }
    }

            return $uploaded_image_id;
        }
    }
}

function run_image_blast() {
    $plugin = new Image_Blast();
}

add_action('plugins_loaded', 'run_image_blast');

