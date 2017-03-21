<?php

if( !class_exists('base_plugin') )
    require_once dirname(__FILE__) . '/class.base.php';

class laf_core extends base_plugin {
	private $plugin_name = 'Lost and Found';
	private $products_endpoint = 'registered-products';
	private $table_name = 'unique_codes';
	private $db_version = '1.0';
	private $template_dir;
	public  $errors, $success;
	protected $templates;


    protected function __construct() {
        parent::__construct();
	    $this->template_dir = dirname(__FILE__) . '/../templates/';;
    }

    public function init() {

        add_action( 'wp_enqueue_scripts', array( $this, 'add_css_js' ), 10 );
        add_action( 'admin_enqueue_scripts', array( $this, 'add_css_js' ), 10 );

	    # shortcodes and form logic stuff
	    add_action('uc_finder_post', array( $this, 'unique_code_finder_post' ) );
	    add_shortcode('unique_code_finder', array( $this, 'unique_code_finder_shortcode' ) );
	    #add_action('product_register_post', array( $this, 'product_register_post' ), 10, 2 );
	    add_shortcode('product_register_form', array( $this, 'product_register_shortcode' ) );

	    register_activation_hook( plugin_dir_path(dirname(__FILE__)) . '/index.php', array( $this, 'plugin_activate' ) );
	    #register_deactivation_hook( plugin_dir_path(dirname(__FILE__)) . '/index.php', array( $this, 'plugin_activate' ) );

	    # csv, settings page
	    if( is_admin() ) {
		    add_action( "admin_init", array( $this, "register_settings_page" ) );
		    add_action( "admin_menu", array( $this, "menu_item" ) );
	    }

    }

	public function add_css_js() {
		wp_enqueue_style( 'laf-style', plugin_dir_url(dirname(__FILE__)) . 'style.css' );
	}


	public function plugin_activate() {
		$this->create_db_table();
		#$this->plugin_deactivate();
	}


	/*
	 * Custom db table stuff
	 */

	public function create_db_table() {
		global $wpdb;

		if ( get_option( "laf_db_version" ) != $this->db_version ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE {$wpdb->prefix}{$this->table_name} (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
		        created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		        updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				unique_code VARCHAR(255) NOT NULL,
				owner_id mediumint(9),
				product_id mediumint(9),
				finder_email VARCHAR(255),
				details text,
				PRIMARY KEY (id),
				UNIQUE KEY id(unique_code)
			);";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );
			update_option( 'laf_db_version', $this->db_version );
		}

	}


	public function create_unique_code( $params = array() ) {
		global $wpdb;

		$defaults = array(
			'created' => current_time( 'mysql' ),
			'updated' => current_time( 'mysql' ),
			'unique_code' => '',
			'owner_id' => NULL,
			'product_id' => NULL,
			'finder_email' => '',
			'details' => ''
		);

		$data = array_merge($defaults, (array)$params);

		return $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'created' => $data['updated'],
				'updated' => $data['updated'],
				'unique_code' => $data['unique_code'],
				'owner_id' => $data['owner_id'],
				'product_id' => $data['product_id'],
				'finder_email' => $data['finder_email'],
				'details' => $data['details']
			)
		);

	}


	public function get_unique_code( $unique_code ) {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->table_name} WHERE unique_code = %s LIMIT 1", $unique_code );
		return $wpdb->get_row( $sql );
	}


	public function get_registered_products( $owner_id ) {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->table_name} WHERE owner_id = %d AND product_id IS NOT NULL", $owner_id );
		return $wpdb->get_results( $sql );
	}


	public function update_unique_code( $unique_code, $params = array() ) {
		global $wpdb;

		$defaults = array(
			'updated' => current_time( 'mysql' ),
		);

		$data = array_merge($defaults, (array)$params);
		$format = array();

		foreach( $data as $k => $d ) {
			if( in_array( $k, array('owner_id', 'product_id')) )
				$format[$k] = '%d';
			else
				$format[$k] = '%s';
		}


		$where = array(
			'unique_code' => $unique_code
		);
		$where_format = array(
			'unique_code' => '%s'
		);

		$query = $wpdb->update(
			$wpdb->prefix . $this->table_name,
			$data,
			$where,
			$format,
			$where_format
		);
		return $query;
	}



	public function parse_csv( $file ) {
		$tmpName = $file;
		return array_map('str_getcsv', file($tmpName));
	}








	/*
	 * Shortcodes and form logic
	 */


	public function product_register_shortcode( $atts ) {
		ob_start();
		$file = $this->template_dir . 'form-unique_code_product.php';

		if( file_exists($file) )
			include( $file );
		return ob_get_clean();
	}


	public function product_register_post( $product_id, $unique_code ) {
		$unique_code = $this->get_unique_code( $unique_code );
		if( empty( $product_id ) ) {
			$this->errors = 'Please choose a product';
			return false;
		}
		if( $unique_code ) {
			if( empty($unique_code->owner_id) ) {
				$data = array(
					'owner_id' => get_current_user_id(),
					'product_id' => $product_id
				);
				return $this->update_unique_code( $unique_code->unique_code, $data );
			} else {
				if( $unique_code->owner_id == get_current_user_id() )
					$this->errors = 'Unique code already registered to your account';
				else
					$this->errors = 'Unique code already used';
				return false;
			}
		} else {
			$this->errors = 'Unique code not found';
			return false;
		}
	}



	public function unique_code_finder_shortcode( $atts ) {
		#$this->unique_code_finder_post();
		$file = $this->template_dir . 'form-unique_code_finder.php';
		ob_start();
		if( file_exists($file) ) {
			if( $_POST['submit'] )
				do_action('uc_finder_post');
			include( $file );
		}
		return ob_get_clean();
	}

	public function unique_code_finder_post() {
		$unique_code = $this->get_unique_code( $_POST['unique_code'] );
		if( $unique_code ) {
			$owner = new WP_User($unique_code->owner_id);

			$to = $owner->user_email;
			$subject = 'unique code found! - (' . $_POST['unique_code'] . ')';
			$body = 'Great News, your product has been found! Someone has found your product and entered the unique code. Here\'s their message:<br /><br />';
			$body .= $_POST['details'] . '<br /><br />';
			$body .= '- ' . $_POST['email'] . '<br /><br />';
			$body .= 'Click "reply" to get in touch!';

			$headers = array('Content-Type: text/html; charset=UTF-8');
			$admin_email = 'admin@' . str_replace('www.', '', $_SERVER['HTTP_HOST']);
			$headers[] = 'Reply-To: ' . $_POST['email'] . ' <' . $_POST['email'] . '>';
			$headers[] = 'From: ' . $admin_email . ' <' . $admin_email . '>';
			$headers[] = 'X-Mailer: PHP/' . phpversion();

			$data = array(
				'finder_email' => $_POST['email'],
				'details' => $_POST['details'],
			);
			$this->update_unique_code( $unique_code->unique_code, $data );
			#$this->success = wp_mail( $to, $subject, $body, $headers );
			$this->success = mail( $to, $subject, $body, implode("\r\n", $headers) );
		} else {
			$this->errors = 'Unique code not found!';
		}

	}





	/*
	 * Settings Page
	 */


	public function register_settings_page() {
		add_settings_section("section", "CSV Uploader", null, "laf");
		add_settings_field("csv_file", "CSV File", array( $this, "csv_file_display" ), "laf", "section");
		register_setting("section", "csv_file", array( $this, "handle_file_upload" ) );
	}

	public function handle_file_upload($option) {
		if(!empty($_FILES["csv_file"]["tmp_name"]))
		{
			$file = $_FILES['csv_file']['tmp_name'];
			$csv = $this->parse_csv( $file );
			foreach( $csv as $row ) :
				$unique_code = $row[0];
				if( !empty($unique_code) ) {
					$data = array(
						'unique_code' => $unique_code
					);
					$this->create_unique_code( $data );
				}
			endforeach;
		}

		return false;
	}

	public function csv_file_display() {
		echo '<input type="file" name="csv_file" />';
	}


	public function settings_page() {
		include $this->template_dir . 'form-csv_uploader.php';
	}

	public function menu_item() {
		add_submenu_page("tools.php", "Lost and Found Settings", "Lost and Found", "manage_options", "laf", array( $this, "settings_page" ) );
	}








	/*
	 * Woocommerce
	 */


	public function wc_get_all_products() {

		$products = array();
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1
		);
		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			foreach( $query->get_posts() as $product )
				$products[] = new WC_Product( $product->ID );
		}
		wp_reset_postdata();

		return $products;

	}


}




