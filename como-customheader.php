<?php
/*
Plugin Name: Como Custom Headers
Plugin URI: http://www.comocreative.com/
Version: 1.0.7
Author: Como Creative LLC
Description: Plugin designed to enable custom headers on interior pages
*/
defined('ABSPATH') or die('No Hackers!');
/* Include plugin updater. */
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/updater.php' );
/* ##################### Page Header Meta Box ##################### */
// Custom Header Content Box
function como_headertext_init() {
	$post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'] ;
	add_meta_box('como_headertext', __('Custom Header Content','como-header-wysiwyg'),'comoheader_wysiwyg_callback','page','normal','high');
}
//add_action('admin_init','como_headertext_init', 1);
// Custom Meta  WYSIWYG
function comoheader_wysiwyg_callback($post) {
	$content = get_post_meta($post->ID, 'comoheader-text', true);
	wp_editor(htmlspecialchars_decode($content) , 'comoheader-text', array("media_buttons" => true));
}
// Saves the Page Section meta input
function como_headertext_meta_save( $post_id ) {
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'como_headertext_nonce' ] ) && wp_verify_nonce( $_POST[ 'como_headertext_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return; // Exits script depending on save status
    }
	
	// Specify Meta Variables to be Updated
	$metaVars = array('comoheader-text');
	$checkboxVars = array();
	
	// Update Meta Variables
	foreach ($metaVars as $var) {
		if (in_array($var,$checkboxVars)) {
			if (isset($_POST[$var])) {
				update_post_meta($post_id, $var, 'yes');
			} else {
				update_post_meta($post_id, $var, $var);
			}
		} elseif ($var == 'comoheader-text') {
			if (!empty($_POST['comoheader-text'])) {
				$data = htmlspecialchars($_POST['comoheader-text']);
			} else {
				$data = '';
			}
			update_post_meta($post_id, 'comoheader-text', $data); 
		} else {
			if(isset($_POST[$var])) {
				update_post_meta($post_id, $var, $_POST[$var]);
			}
		}
	}
}
add_action( 'save_post', 'como_headertext_meta_save' );
function comocusthead_bg_enqueue() {
    global $typenow;
    if( $typenow == 'page' ) {
        wp_enqueue_media();
        // Registers and enqueues the required javascript.
        wp_register_script( 'comocusthead-bg-image-upload', plugin_dir_url( __FILE__ ) . '/js/image-upload.js', array( 'jquery' ) );
        wp_localize_script( 'comocusthead-bg-image-upload', 'meta_image',
            array(
                'title' => 'Choose or Upload an Image',
                'button' => 'Use this image',
            )
        );
        wp_enqueue_script( 'comocusthead-bg-image-upload' );
    }
}
add_action( 'admin_enqueue_scripts', 'comocusthead_bg_enqueue' );
/**
 * Create Custom Header background image metabox
 */
function comocusthead_bg( $post ) {
    wp_nonce_field( 'comocusthead_bg_submit', 'comocusthead_bg_nonce' );
    $comocusthead_bg_stored_meta = get_post_meta($post->ID); 
	
	global $post;
	
	// Get WordPress' media upload URL
	$upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );
	
	// See if there's a media id already saved as post meta
	$comoheadbg_img_id = ((isset($comocusthead_bg_stored_meta['comoheadbg-img'][0])) ? $comocusthead_bg_stored_meta['comoheadbg-img'][0] : '');
	
	// Get the image src
	$comoheadbg_img_src = (($comoheadbg_img_id) ? wp_get_attachment_image_src( $comoheadbg_img_id, 'full') : '');
	
	// For convenience, see if the array is valid
	$have_comoheadbg_img = is_array( $comoheadbg_img_src );

	$comoheadbg_title_override = ((isset($comocusthead_bg_stored_meta['comoheadbg-title-override'][0])) ? $comocusthead_bg_stored_meta['comoheadbg-title-override'][0] : '');
	$comoheadbg_content = ((isset($comocusthead_bg_stored_meta['comoheadbg-content'][0])) ? $comocusthead_bg_stored_meta['comoheadbg-content'][0] : '');
	$comoheadbg_additional = ((isset($comocusthead_bg_stored_meta['comoheadbg-additional'][0])) ? $comocusthead_bg_stored_meta['comoheadbg-additional'][0] : '');
	$comoheadbg_class = ((isset($comocusthead_bg_stored_meta['comoheadbg-class'][0])) ? $comocusthead_bg_stored_meta['comoheadbg-class'][0] : '');
	?>
	
	<!-- Your image container, which can be manipulated with js -->
	<div class="custom-img-container">
		<?php if ( $have_comoheadbg_img ) : ?>
			<img src="<?=$comoheadbg_img_src[0]?>" alt="" style="max-width:100%;" />
		<?php endif; ?>
	</div>
	
	<!-- Your add & remove image links -->
	<p class="hide-if-no-js">
		<a class="upload-comoheadbg-img <?php if ( $have_comoheadbg_img  ) { echo 'hidden'; } ?>" 
		   href="<?=$upload_link?>">
			<?php _e('Set custom image') ?>
		</a>
		<a class="delete-comoheadbg-img <?php if ( ! $have_comoheadbg_img  ) { echo 'hidden'; } ?>" 
		  href="#">
			<?php _e('Remove this image') ?>
		</a>
	</p>
	<!-- A hidden input to set and post the chosen image id -->
	<input class="comoheadbg-img" id="comoheadbg-img" name="comoheadbg-img" type="hidden" value="<?=esc_attr($comoheadbg_img_id)?>" />
	
	<p><label for="comoheadbg-title-override">Custom Header Title Override</label><input id="comoheadbg-title-override" name="comoheadbg-title-override" type="text" style="width: 100%" value="<?=esc_attr($comoheadbg_title_override)?>" /></p>
	
	<p><label for="comoheadbg-content">Custom Header Content</label><textarea id="comoheadbg-content" name="comoheadbg-content" type="text" style="width: 100%" rows="5"><?=esc_attr($comoheadbg_content)?></textarea></p>

	<p><label for="comoheadbg-additional">Custom Header Additional</label><textarea id="comoheadbg-additional" name="comoheadbg-additional" type="text" style="width: 100%" rows="5"><?=esc_attr($comoheadbg_additional)?></textarea></p>
	
	<p><label for="comoheadbg-class">Custom Header Class</label><input id="comoheadbg-class" name="comoheadbg-class" type="text" style="width: 100%" value="<?=esc_attr($comoheadbg_class)?>" /></p>
	<input type="hidden" name="comoupdate_flag" value="true" />
	
<?php    
}
/* Add Custom Header background image metabox to the back end of Custom Header posts */
function comocusthead_bg_metabox() {
    add_meta_box( 'comocusthead-bg', 'Custom Header Info', 'comocusthead_bg', 'page', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'comocusthead_bg_metabox', 1);
/* Save background image metabox for Custom Header posts */
function save_comocusthead_bg( $post_id ) {
	
	// Only do this if our custom flag is present
    if (isset($_POST['comoupdate_flag'])) {
	
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'comocusthead_bg_nonce' ] ) && wp_verify_nonce( $_POST[ 'comocusthead_bg_nonce' ], 'comocusthead_bg_submit' ) ) ? 'true' : 'false';
		// Exits script depending on save status
		if ($is_autosave || $is_revision || !$is_valid_nonce) {
			return;
		}
		// Checks for input and sanitizes/saves if needed
		if (isset($_POST['comoheadbg-img'])) {
			update_post_meta( $post_id, 'comoheadbg-img', $_POST[ 'comoheadbg-img' ] );
		}
		
		// Update Header Title Override
		if (isset($_POST['comoheadbg-title-override'])) {
			update_post_meta($post_id, 'comoheadbg-title-override', $_POST['comoheadbg-title-override']);
		} else {
			update_post_meta($post_id, 'comoheadbg-title-override', '');
		}
		
		// Update Header Content
		if (isset($_POST['comoheadbg-content'])) {
			update_post_meta($post_id, 'comoheadbg-content', $_POST['comoheadbg-content']);
		} else {
			update_post_meta($post_id, 'comoheadbg-content', '');
		}
		
		// Update Header Additional
		if (isset($_POST['comoheadbg-additional'])) {
			update_post_meta($post_id, 'comoheadbg-additional', $_POST['comoheadbg-additional']);
		} else {
			update_post_meta($post_id, 'comoheadbg-additional', '');
		}
		
		// Update Header Class
		if (isset($_POST['comoheadbg-class'])) {
			update_post_meta($post_id, 'comoheadbg-class', $_POST['comoheadbg-class']);
		} else {
			update_post_meta($post_id, 'comoheadbg-class', '');
		}
	}
}
add_action( 'save_post', 'save_comocusthead_bg' );
// Custom Image Sizes
function comoheader_img_sizes() {
  	add_image_size( 'page-header', 1920, 600, false ); // (not-cropped)
}
add_action( 'after_setup_theme', 'comoheader_img_sizes' );
?>