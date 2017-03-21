<?php

if( !class_exists('base_plugin') )
    require_once dirname(__FILE__) . '/class.base.php';

class laf_wc extends laf_core {
	private $products_endpoint = 'registered-products';
	#private $errors;

    protected function __construct() {
        parent::__construct();
    }

    public function init() {

	    # adds registered-products endpoint to my-account page
	    # https://github.com/woocommerce/woocommerce/wiki/2.6-Tabbed-My-Account-page
	    add_action( 'init', array( $this, 'wc_custom_endpoints' ) );
	    add_filter( 'query_vars', array( $this, 'wc_custom_query_vars' ), 0  );
	    add_action( 'woocommerce_account_' . $this->products_endpoint . '_endpoint', array( $this, 'wc_custom_endpoint_content' ) );
	    add_filter( 'woocommerce_account_menu_items', array( $this, 'wc_add_menu_item' ) );
	    add_filter( 'the_title', array( $this, 'wc_custom_endpoint_title' ) );

	    #add_action('parse_request', array( $this, 'wc_custom_endpoint_parse_request' ), 0);

	    register_activation_hook( plugin_dir_path(dirname(__FILE__)) . '/index.php', array( $this, 'plugin_activate' ) );
	    register_deactivation_hook( plugin_dir_path(dirname(__FILE__)) . '/index.php', array( $this, 'plugin_activate' ) );
    }


	public function plugin_activate() {
		#parent::plugin_activate();
		$this->wc_custom_endpoints();
		flush_rewrite_rules();
	}


	public function wc_custom_endpoints() {
		add_rewrite_endpoint( $this->products_endpoint, EP_ROOT | EP_PAGES );
	}


	public function wc_custom_query_vars( $vars ) {
		$vars[] = $this->products_endpoint;
		return $vars;
	}


	public function wc_add_menu_item( $items ) {
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );
		$items[$this->products_endpoint] = __( 'Registered Products', 'woocommerce' );
		$items['customer-logout'] = $logout;

		return $items;
	}



	public function wc_custom_endpoint_content() {
		require_once dirname( __FILE__ ) . '/../templates/wc-registered-products.php';
	}

	public function wc_custom_endpoint_title( $title ) {
	    global $wp_query;

	    $is_endpoint = isset( $wp_query->query_vars[$this->products_endpoint] );

	    if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
		    // New page title.
		    $title = __( 'Registered Products', 'woocommerce' );
		    remove_filter( 'the_title', 'my_custom_endpoint_title' );
	    }

		return $title;
	}


	public function wc_custom_endpoint_parse_request( $request ) {


		if( array_key_exists( $this->products_endpoint, $request->query_vars ) ){
			# process variables
			#echo '<pre>';
			#var_dump($request);
			#die;
		}

	}







	public function register_product( $product_id, $unique_code ) {
		if( !is_numeric($product_id) || !isset($unique_code) )
			return false;

		$data = array(
			'product_id' => $product_id,
			'user_id' => get_current_user_id()
		);
		$this->update_unique_code( $unique_code, $data );


		return true;
	}


}

