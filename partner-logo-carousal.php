<?php
 
/*
 
Plugin Name: Partner Logo Carousal
Plugin URI: #
 
Description: Partner Logo Carousal.
 
Version: 1.0.0
 
Author: Web Perfections
 
Author URI: #
 
License: GPLv2 or later
 
Text Domain: #
 
*/

add_action( 'init', 'wp_partner_logo_carousal' );
function wp_partner_logo_carousal() {  // logos custom post type
    // set up labels
    $labels = array(
        'name' => 'Logos',
        'singular_name' => 'Logo Item',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Logo Item',
        'edit_item' => 'Edit Logo Item',
        'new_item' => 'New Logo Item',
        'all_items' => 'All Logos',
        'view_item' => 'View Logo Item',
        'search_items' => 'Search Logos',
        'not_found' =>  'No Logos Found',
        'not_found_in_trash' => 'No Logos found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Partner Logos',
    );
    register_post_type(
        'logos',
        array(
            'labels' => $labels,
            'has_archive' => true,
            'public' => true,
            'hierarchical' => true,
            // 'supports' => array( 'title', 'editor', 'excerpt', 'custom-fields', 'thumbnail','page-attributes' ),
            'supports' => array( 'title', 'thumbnail'),
            // 'taxonomies' => array( 'post_tag', 'category' ),
            'taxonomies' => array('category' ),
            'exclude_from_search' => true,
            'capability_type' => 'post',
        )
    );
}


// Change the Columns in post list page in admin dashboard and Add images
add_filter( 'manage_logos_posts_columns', 'smashing_logos_columns' );
function smashing_logos_columns( $columns ) {
  	$columns = array(
      'cb' => $columns['cb'],
      'image' => __( 'Image' ),
      'title' => __( 'Title' ),
      'categories' => __( 'Categories' ),
      'date' => __( 'Date' ),
    );
	return $columns;
}

add_action( 'manage_logos_posts_custom_column', 'smashing_logos_column', 10, 2);
function smashing_logos_column( $column, $post_id ) {
  // Image column
  if ( 'image' === $column ) {
    echo get_the_post_thumbnail( $post_id, array(80, 80) );
  }
}


// Create a Custom Meta Box Field and save in database
function custom_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");

    ?>
        <div>
            <label for="meta-box-partner-website-url">Partner Website Url</label>
            <input name="meta-box-partner-website-url" type="text" value="<?php echo get_post_meta($object->ID, "meta-box-partner-website-url", true); ?>">
            <br>
            <label for="meta-box-newtab">Open In New Tab</label>
            <?php
                $checkbox_value = get_post_meta($object->ID, "meta-box-newtab", true);

                if($checkbox_value == "")
                {
                    ?>
                        <input name="meta-box-newtab" type="checkbox" value="true">
                    <?php
                }
                else if($checkbox_value == "true")
                {
                    ?>  
                        <input name="meta-box-newtab" type="checkbox" value="true" checked>
                    <?php
                }
            ?>
        </div>
    <?php  
}


function add_custom_meta_box()
{
    // add_meta_box("demo-meta-box", "Custom Meta Box", "custom_meta_box_markup", "logos", null, "high", null);
    add_meta_box("demo-meta-box", "Custom Meta Box", "custom_meta_box_markup", "logos", 'normal', "high", null);
}

add_action("add_meta_boxes", "add_custom_meta_box");



function save_custom_meta_box1($post_id, $post, $update)
{

    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;
	
	$slug = "logos";
    if($slug != $post->post_type)
        return $post_id;

    $meta_box_text_value = "";
    $meta_box_dropdown_value = "";
    $meta_box_checkbox_value = "";

    if(isset($_POST["meta-box-partner-website-url"]))
    {
        $meta_box_text_value = $_POST["meta-box-partner-website-url"];
    }   
    update_post_meta($post_id, "meta-box-partner-website-url", $meta_box_text_value);

    if(isset($_POST["meta-box-newtab"]))
    {
        $meta_box_checkbox_value = $_POST["meta-box-newtab"];
    }   
    update_post_meta($post_id, "meta-box-newtab", $meta_box_checkbox_value);
}

add_action("save_post", "save_custom_meta_box1", 10, 3);

// Create a Shortcode for display([list-posts type='logos' category='test-category-1'])
add_shortcode( 'list-posts', 'rmcc_post_listing_parameters_shortcode' );
function rmcc_post_listing_parameters_shortcode( $atts ) {
    ob_start();

    // define attributes and their defaults
    extract( shortcode_atts( array (
        'type' => 'post',
        'order' => 'date',
        'orderby' => 'title',
        'posts' => -1,
        'category' => '',
    ), $atts ) );

    // define query parameters based on attributes
    $query = new WP_Query( array(
        'post_type' => $type,
        'posts_per_page' => -1,
        'order' => $order,
        'orderby' => $orderby,
        'posts' => $posts,
        'category_name' => $category,
    ) );
    
    if ( $query->have_posts() ) {
        global $post;
        ?>
        <section class="regular slider slider-section" style="display: none;">

	        <?php while ( $query->have_posts() ) : $query->the_post(); 
	            $post_thumbnail_id = get_post_thumbnail_id($post->ID);
	            $imageSRC = wp_get_attachment_image_src($post_thumbnail_id, 'full');
	            // if(has_post_thumbnail()){ the_post_thumbnail();}
	            // echo $post->ID;
	            // echo $post->post_title;
	            $website_url = get_post_meta($post->ID, "meta-box-partner-website-url", true);
	            $newtab = get_post_meta($post->ID, "meta-box-newtab", true);
	        ?>
	        <!--<div><a href="<?php echo $website_url; ?>" <?php if($newtab){ echo 'target=_blank'; } ?>><img src="<?php echo $imageSRC[0]; ?>"></a></div>-->
            <div>
                <a href="<?php echo $website_url; ?>" <?php if($newtab){ echo 'target=_blank'; } ?>><img src="<?php echo $imageSRC[0]; ?>"></a>
            </div>
	        <?php endwhile;
	        wp_reset_postdata(); ?>
	    </section>

	    <?php $myvariable = ob_get_clean();
	    return $myvariable;
    }

}



// Create the Setting Menu in Custom Post Type(Logos) and save Settigs page
add_action('admin_menu', 'add_logos_setting_cpt_submenu');
//admin_menu callback function
function add_logos_setting_cpt_submenu(){
    add_submenu_page(
        'edit.php?post_type=logos', //$parent_slug
        'Logos Settings',  //$page_title
        'Logos Settings',        //$menu_title
        'manage_options',           //$capability
        'logos_settings',//$menu_slug
        'logos_settings_render_page'//$function
    );
}

//add_submenu_page callback function
function logos_settings_render_page() {
    ?>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'my_logo_plugin_options' );
        do_settings_sections( 'my_logo_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function my_register_settings() {
    register_setting( 'my_logo_plugin_options', 'my_logo_plugin_options', 'my_logo_plugin_options_validate' );
    add_settings_section( 'api_settings', 'Logo Plugin Settings', 'my_plugin_section_text', 'my_logo_plugin' );

    add_settings_field( 'my_plugin_setting_logo_slide', ' How many images slide at a time', 'my_plugin_setting_logo_slide', 'my_logo_plugin', 'api_settings' );
    add_settings_field( 'my_plugin_setting_logo_delay', 'Slide delay duration', 'my_plugin_setting_logo_delay', 'my_logo_plugin', 'api_settings' );
    add_settings_field( 'my_plugin_setting_logo_show', 'How many image will show in slider', 'my_plugin_setting_logo_show', 'my_logo_plugin', 'api_settings' );
    add_settings_field( 'my_plugin_setting_logo_arrows', 'Navigation Arrows', 'my_plugin_setting_logo_arrows', 'my_logo_plugin', 'api_settings' );
}
add_action( 'admin_init', 'my_register_settings' );

function my_plugin_section_text() {
    echo '<p>Here you can set all the options for partner Logo Plugin</p>';
}

function my_plugin_setting_logo_slide() {
    $options = get_option( 'my_logo_plugin_options' );
    $logo_slide=$options["logo_slide"];
    echo "<input id='my_plugin_setting_logo_slide' name='my_logo_plugin_options[logo_slide]' type='text' value='$logo_slide' />";
}

function my_plugin_setting_logo_delay() {
    $options = get_option( 'my_logo_plugin_options' );
    $logo_delay=$options['logo_delay'];
    echo "<input id='my_plugin_setting_logo_delay' name='my_logo_plugin_options[logo_delay]' type='text' value='$logo_delay' />";
}

function my_plugin_setting_logo_show() {
    $options = get_option( 'my_logo_plugin_options' );
    $logo_show=$options['logo_show'];
    echo "<input id='my_plugin_setting_logo_show' name='my_logo_plugin_options[logo_show]' type='text' value='$logo_show' />";
}

function my_plugin_setting_logo_arrows() {
    $options = get_option( 'my_logo_plugin_options' );
    $logo_arrows=$options['logo_arrows'];
    ?>
    <input id='my_plugin_setting_logo_arrows' name='my_logo_plugin_options[logo_arrows]' type='checkbox' <?php if($logo_arrows=='on'){ echo 'checked';}?> />
    <?php
}


// Enqueue style and js files
function tutsplus_movie_styles() {
    wp_enqueue_style( 'mystyle0', plugins_url( '', __FILE__ ). '/slick/style1.css' );
    wp_enqueue_style( 'mystyle1', plugins_url( '', __FILE__ ). '/slick/slick.css' );
    wp_enqueue_style( 'mystyle2', plugins_url( '', __FILE__ ). '/slick/slick-theme.css' );
    // wp_enqueue_script( 'my-jquery-js', 'https://code.jquery.com/jquery-2.2.0.min.js' );
    wp_enqueue_script( 'my-jquery-js', plugins_url( '', __FILE__ ). '/js/jquery-2.2.0.min.js' );
    wp_enqueue_script( 'my-jquery-js1', plugins_url( '', __FILE__ ). '/slick/slick.js' );
    wp_enqueue_script( 'my-jquery-js2', plugins_url( '', __FILE__ ). '/js/logo_carousel_custom_1.js',array( 'jquery' ) );
}
add_action( 'wp_enqueue_scripts', 'tutsplus_movie_styles' );










function wpb_hook_javascript() {
    $a = get_option( 'my_logo_plugin_options' );
// print_r($a);
$logo_delay=1000;$logo_slide=1;$logo_show=4;$logo_arrows='false';
if($a['logo_delay']){$logo_delay=$a['logo_delay'];}
if($a['logo_slide']){$logo_slide=$a['logo_slide'];}
if($a['logo_show']){$logo_show=$a['logo_show'];}
if($a['logo_arrows']=='on'){$logo_arrows='true';}
    ?>
        <script>
        var logo_delay = <?php echo $logo_delay; ?>;
        var logo_slide = <?php echo $logo_slide; ?>;
        var logo_show = <?php echo $logo_show; ?>;
        var logo_arrows = <?php echo $logo_arrows; ?>;
        </script>
    <?php
}
add_action('wp_footer', 'wpb_hook_javascript');


