<?php
/*
 * Plugin Name: Slenderbox
 * Plugin URI: https://github.com/mpetroff/slenderbox-wp
 * Description: Overlays images on the current page using Slenderbox, a lightweight and framework-free lightbox plugin that can be used with valid HTML5.
 * Version: 1.1.2
 * Author: Matthew Petroff
 * Author URI: http://www.mpetroff.net/
 */

/*
 * Slenderbox - A Lightweight Lightbox Script
 * Copyright (c) 2012-2015 Matthew Petroff
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Automatically add lightboxing by default
add_option('slenderbox_automate', 1);

// Add an admin options page under "Options"
function slenderbox_add_options() {
    add_options_page('Slenderbox Options', 'Slenderbox', 'administrator', __FILE__, 'slenderbox_options_page');
}
add_action('admin_menu', 'slenderbox_add_options');

// Admin options page
function slenderbox_options_page() {
    // Check for admin options submission and update options
    if ($_REQUEST['set_options']) {
        update_option('slenderbox_automate', $_REQUEST['automate']);
    }
    ?>
    
    <div class="wrap">
        <h2><?php _e('Slenderbox Options', 'slenderbox') ?></h2>
        
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>&amp;updated=true">
        <p><?php _e('Slenderbox needs "data-sbox" or "data-sbox=\'REFERENCE\'" added to image links to function; this can be done either manually or automatically.', 'slenderbox') ?></p>
        <p><input type="checkbox" value="1" name="automate" <?php echo (get_option('slenderbox_automate') == '1') ? 'checked="checked"' : '' ?> /> <?php _e('Automatically use lightboxes for image links.', 'slenderbox') ?></p>
        <p><input type="submit" class="button-primary" name="set_options" value="<?php _e('Save Changes', 'slenderbox') ?>"/></p>
        </form>
    </div>
    
    <?php
}

// Add slenderbox stylesheet
function slenderbox_add_style() {
    wp_register_style('slenderbox_style', plugins_url('slenderbox.css', __FILE__));
    wp_enqueue_style('slenderbox_style');
}
add_action('wp_enqueue_scripts', 'slenderbox_add_style');

// Automatically insert "data-sbox='name_of_post'" into every image link, leaving existing data attributes intact
function slenderbox_add_data_attribute($content) {
    global $post;
    $patterns = array();
    $replacements = array();
    
    // Copy image titles to link titles
    $patterns[0] = '/(<a(?![^>]*?data-sbox.*)(?![^>]*?title=[\'"].*)[^>]*?href=[\'"][^\'"]+?\.(?:bmp|gif|jpg|jpeg|png|webp)[\'"][^\>]*)(>.*?title=)(["].*?["]|[\'].*?[\'])(.*?<\/a>)/i';
    $replacements[0] = '$1 title=$3$2$3$4';
    
    // Add data attribute
    $patterns[1] = '/(<a(?![^>]*?data-sbox.*)[^>]*?href=[\'"][^\'"]+?\.(?:bmp|gif|jpg|jpeg|png|webp)[\'"][^\>]*)>/i';
    $replacements[1] = '$1 data-sbox="'.$post->ID.'">';
    
    // Do replacements
    $content = preg_replace($patterns, $replacements, $content);
    return $content;
}
if(get_option('slenderbox_automate') == 1) {
    add_filter('the_content', 'slenderbox_add_data_attribute', 99);
    add_filter('the_excerpt', 'slenderbox_add_data_attribute', 99);
    add_filter('widget_text', 'slenderbox_add_data_attribute', 99);
}

// Include script if not viewing an admin page
if(!is_admin()) {
    function slenderbox_scripts() {
        wp_enqueue_script('slenderbox', plugins_url('slenderbox.js', __FILE__));
    }
    add_action('wp_enqueue_scripts', 'slenderbox_scripts');
}

?>
