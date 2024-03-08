<?php 

/**
 * Plugin Name:       Digioh 
 * Description:       Configures and deploys the Digioh JavaScript tag to your site pages.
 * Version:           1.1.2
 * Requires at least: 5.2.3
 * Author:            Digioh   
 */
 

class Digioh{

	function add_async($tag, $handle, $src) {
		if ($handle === 'digioh-low-impact' || $handle === 'digioh-fast-activation') {
			if (false === stripos($tag, 'async')) {
				$tag = str_replace(' src', ' async="async" src', $tag);
			}
		}
		return $tag;
	}

	function enqueue(){

		$tag_type = get_option('oh_tagtype','LOW');
		$client_id = get_option('oh_client_id','');

		if($tag_type=='LOW'){ 
			$url = esc_url( 'https://www.lightboxcdn.com/vendor/' . $client_id . '/lightbox_speed.js' );
			wp_enqueue_script( 'digioh-low-impact', $url, array(), null);
		}

		if($tag_type=='FAST'){ 
			$url = esc_url( 'https://www.lightboxcdn.com/vendor/' . $client_id . '/lightbox_inline.js' );
			wp_enqueue_script( 'digioh-fast-activation', $url, array(), null);
		}

		add_filter('script_loader_tag', [$this,'add_async'], 10, 3);
	}

	function inject(){

		$client_id = get_option('oh_client_id','');

		if( empty(trim($client_id)) ){
			return;
		}

		$tag_type = get_option('oh_tagtype','LOW');
		
		if($tag_type=='COMPATIBILITY'){

			$url = plugin_dir_path(__FILE__) . 'templates/javascript.txt';
			$inline_script = file_get_contents( $url ); 
			$inline_script = str_replace('{CLIENT_ID}', $client_id, $inline_script); 
			
			//Check compatability with 5.7 for injection method
			if (function_exists('wp_print_inline_script_tag')) {
				wp_print_inline_script_tag($inline_script,
					array(
						'id'    => 'digioh_compatability_tag',
						'async' => true,
					)
				);
			}
		}
	}

	function get_product_by_sku( $sku ) {

    global $wpdb;

    $product_id = $wpdb->get_row("SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key='_sku' AND meta_value='".$sku."' LIMIT 1" );

    if ( $product_id ){ 
    	return wc_get_product( $product_id->post_id );
    }

    return null;
}

	function digioh_settings(){

		if(isset($_POST['saveChanges'])){  

			$sanitized_tagtype = strtoupper(sanitize_title( $_POST['oh_tagtype'] ));
			$sanitized_clientid = sanitize_key( $_POST['oh_client_id'] );

			update_option( 'oh_tagtype', $sanitized_tagtype ); 
			update_option( 'oh_client_id', $sanitized_clientid );   

		}

		$options = [
				'LOW' => 'Low Impact Tag',
				'FAST' => 'Fast Activation',
				'COMPATIBILITY' => 'Site Compatibility'
			];

		$oh_client_id = get_option( 'oh_client_id', '' ); 
		$oh_tagtype = get_option( 'oh_tagtype', 'LOW' ); 
		
		require_once "templates/settings.php";

	}
	 
	function add_settings(){

		add_options_page(
						'Digioh Settings',
						'Digioh Settings',
						'manage_options',
						'digioh', 
						[$this,'digioh_settings']
					);  
	}

	function oh_notice() {

		$oh_client_id = get_option( 'oh_client_id', '' ); 

		if( empty( trim( $oh_client_id ) ) 
			&& ! isset( $_POST['oh_tagtype'] ) ){

			$url = admin_url( 'options-general.php?page=digioh' );
		   ?>
		   <div class="notice notice-error">
			  <p><?php _e( 'Digioh client key is not set. To activate, please enter your key <a href="'
							. esc_url($url)
							. '">Settings</a>', 'digioh' ); ?></p>
		   </div>
		   <?php 
	   }
	}

	function add_settings_link_to_plugins( $links ) {
		$links[] = '<a href="' .
			admin_url( 'options-general.php?page=digioh' ) .
			'">' . __('Settings') . '</a>';
		return $links;
	}

	function url_actions(){

		if ( !class_exists( 'WooCommerce' ) ){ 
			return; 
		}

		if(isset($_GET['addproduct']) && isset($_GET['qty'])
			&& !empty(trim($_GET['addproduct'])) && !empty(trim($_GET['qty']))){
			$qty = sanitize_text_field($_GET['qty']);
 			if(!is_numeric($_GET['qty'])){
 				die('Quantity parameter must be a number.');
 			}
 			
 			$skus = explode(',', sanitize_text_field($_GET['addproduct']));

 			foreach ($skus as $sku) {
 				$product = $this->get_product_by_sku( $sku );
 				if(!$product){
					echo "Skipping";
 					continue;
 				}
				//die(var_dump($product )); 
 				WC()->cart->add_to_cart($product->get_id(), intval($_GET['qty']) );
 			}  
 			die('OK');
		}else if(isset($_GET['getproduct']) && !empty(trim($_GET['getproduct']))){
			
			if($_GET['getproduct']=='all'){
				$products = get_posts([ 'post_type' => 'product',
        'posts_per_page' => -1]); 
 				$output = "SKU, Page, Thumbnail, Image, Price, In Stock\r\n";
				foreach ($products as $key => $value) {
					$product = wc_get_product( $value->ID );
	 				$page = get_permalink( $value->ID );
	 				$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $value->ID ), 'woocommerce_thumbnail' );
					if(!$thumbnail) 
						$thumbnail = wc_placeholder_img_src('woocommerce_thumbnail');
	 				$full_image = wp_get_attachment_image_src( get_post_thumbnail_id( $value->ID ), 'full' );
					if(!$full_image) 
						$full_image = wc_placeholder_img_src('full');
	 				$price = $product->get_price();
					 
	 				$in_stock = $product->get_stock_status();
					
	 				$output .=  $product->get_sku().","."$page,".
						(is_string($thumbnail) ? $thumbnail : $thumbnail[0]).",".
						(is_string($full_image) ? $full_image : $full_image[0]).",$price,$in_stock\r\n";
				}
				header("Content-Type: text/csv"); 
				die($output);
			}
 			$skus = explode(',', sanitize_text_field($_GET['getproduct']));
 			$output = "SKU, Page, Thumbnail, Image, Price, In Stock\r\n";
 			foreach ($skus as $sku) {
 				$product = $this->get_product_by_sku( $sku );
 				if(!$product){ 
 					continue;
 				} 
 				//$product = wc_get_product( $id );
 				$page = get_permalink( $product->ID );
 				$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $product->ID ), 'single-post-thumbnail' );
					if(!$thumbnail) 
						$thumbnail = wc_placeholder_img_src('woocommerce_thumbnail');
 				$full_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->ID ), 'full' );
					if(!$full_image) 
						$full_image = wc_placeholder_img_src('full');
 				$price = $product->get_price();
 				$in_stock = $product->get_stock_status();
	 			$output .= $sku.","."$page,".
					(is_string($thumbnail) ? $thumbnail : $thumbnail[0]).",".
					(is_string($full_image) ? $full_image : $full_image[0]).",$price,$in_stock\r\n";
 			}
			header("Content-Type: text/csv"); 
 			die($output);
		}
	}


	function digioh_host_url_handler($query) {
		if($_SERVER["REQUEST_URI"] == '/apps/dghbox') {
			echo "<html><head></head><body></body></html>";
			exit();
		}
	}

	function __construct(){
		add_action('wp_enqueue_scripts',[$this,'enqueue']);
		add_action('wp_head',[$this,'inject']);
		add_action('template_redirect',[$this, 'url_actions']);
		add_action('admin_menu', [$this,'add_settings'] );
		add_action('admin_notices', [$this,'oh_notice'] );
		add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$this,'add_settings_link_to_plugins']); 
		add_action( 'parse_request', [$this, 'digioh_host_url_handler'] );
	}
}

new Digioh();
