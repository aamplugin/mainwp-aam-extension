<?php
/*
  Plugin Name: AAM Extension for MainWP
  Plugin URI: https://aamportal.com
  Description: AAM extension to connect all sites to MainWP Dashboard
  Version: 1.0.0
  Author: Vasyltech LLC
  Author URI: https://vasyltech.com
  Documentation URI: https://aamportal.com/integrations/mainwp
  License: GPLv3
  License URI: http://www.gnu.org/licenses/gpl.html
 */


namespace MainWP\Extensions\AAM;

use MainWP\Extensions\AAM\MainWP_AAM_DB;

if ( ! defined( 'MAINWP_AAM_PLUGIN_FILE' ) ) {
	define( 'MAINWP_AAM_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MAINWP_AAM_PLUGIN_DIR' ) ) {
	define( 'MAINWP_AAM_PLUGIN_DIR', plugin_dir_path( MAINWP_AAM_PLUGIN_FILE ) );
}

if ( ! defined( 'MAINWP_AAM_PLUGIN_URL' ) ) {
	define( 'MAINWP_AAM_PLUGIN_URL', plugin_dir_url( MAINWP_AAM_PLUGIN_FILE ) );
}

if ( ! defined( 'MAINWP_AAM_LOG_PRIORITY' ) ) {
	define( 'MAINWP_AAM_LOG_PRIORITY', 2024011 );
}

if ( ! defined( 'MAINWP_AAM_EXTENSION_VERSION' ) ) {
	define( 'MAINWP_AAM_EXTENSION_VERSION', '1.0.0' );
}

class MainWP_AAM_Extension_Activator {

	protected $mainwpMainActivated = false;
	protected $childEnabled        = false;
	protected $childKey            = false;
	protected $childFile;
	protected $plugin_handle    = 'aam-extension-aam';
	protected $product_id       = 'AAM Extension for MainWP';
	protected $software_version = '1.0';

	public function __construct() {
		$this->childFile = __FILE__;

		// Register given function as __autoload() implementation
		spl_autoload_register( array( $this, 'autoload' ) );

		// Register activation and deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		/**
		 * This is a filter similar to adding the management page in WordPress.
		 * It calls the function get_this_extension, which adds the extension to the $extensions array.
		 * This array is a list of all of the extensions MainWP uses, and the functions that
		 * it has to call to show settings for them. In this case, the function is settings.
		 */
		add_filter( 'mainwp_getextensions', array( &$this, 'get_this_extension' ) );
		add_filter( 'mainwp_log_specific_actions', array( $this, 'hook_log_specific' ), 10, 2 );

		add_filter( 'mainwp_sync_others_data', array( $this, 'sync_others_data' ));
		add_filter( 'mainwp_before_save_sync_result', array( $this, 'save_sync_result' ), 10, 2);
		/**
		 * This variable checks to see if MainWP is activated. By default it will return false & return admin notices to the user
		 * that MainWP Dashboard needs to be activated. If MainWP is activated, then call the function activate_this_plugin.
		 */
		$this->mainwpMainActivated = apply_filters( 'mainwp_activated_check', false );
		if ( $this->mainwpMainActivated !== false ) {
			$this->activate_this_plugin();
		} else {
			add_action( 'mainwp_activated', array( &$this, 'activate_this_plugin' ) );
		}

		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * This function will go through the /class folder and require all of the files.
	 * The class name is passed in by spl_autoload_register.
	 * It is the name of the class that is being instantiated.
	 * For example, if you do new MainWP_AAM_Overview, then
	 * $class_name will be MainWP_AAM_Overview.
	 *
	 * The class name is also used to determine the file name.
	 * For example, MainWP_AAM_Overview is in the file
	 * class/class-mainwp-aam-overview.php.
	 *
	 * @param string $class_name The name of the class to load.
	 */
	public function autoload( $class_name ) {

		if ( 0 === strpos( $class_name, 'MainWP\Extensions\AAM' ) ) {
			// trim the namespace prefix: MainWP\Extensions\AAM\.
			$class_name = str_replace( 'MainWP\Extensions\AAM\\', '', $class_name );
		} else {
			return;
		}

		if ( 0 !== strpos( $class_name, 'MainWP_AAM' ) ) {
			return;
		}
		$class_name = str_replace( '_', '-', strtolower( $class_name ) );
		$class_file = implode( DIRECTORY_SEPARATOR, array( MAINWP_AAM_PLUGIN_DIR, 'class' , 'class-' . $class_name . '.php' ) );
		if ( file_exists( $class_file ) ) {
			require_once $class_file;
		}
	}

	/**
	 * Add AAM flag to data sync request
	 *
	 * @param array $data
	 *
	 * @return array
	 * @access public
	 */
	public function sync_others_data( $data ) {
		return array_merge( $data, [
			'aam' => [ 'security_score', 'issues_summary' ]
		] );
	}

	/**
	 * Save sync result
	 *
	 * @param array  $information
	 * @param object $site
	 *
	 * @return array
	 * @access public
	 */
	public function save_sync_result( $information, $site ) {
		if (!empty( $information['aam'] )) {
			MainWP_AAM_DB::get_instance()->save_sync_data( $information['aam'], $site->id );
		}

		return $information;
	}

	/**
	 * It calls the function get_this_extension, which adds the extension to the $extensions array.
	 * This array is a list of all of the extensions MainWP uses, and the functions that
	 * it has to call to show settings for them. In this case, the function is settings.
	 */
	public function get_this_extension( $pArray ) {

		$pArray[] = array(
			'plugin'     => __FILE__,
			'api'        => $this->plugin_handle,
			'mainwp'     => true,
		    'callback'   => array( &$this, 'settings' ),
			'apiManager' => false
		);

		return $pArray;
	}

	/**
	 * Main extension page.
	 *
	 * @return void
	 */
	public function settings() {
		do_action( 'mainwp_pageheader_extensions', __FILE__ );
		MainWP_AAM_Overview::get_instance()->render_tabs();
		do_action( 'mainwp_pagefooter_extensions', __FILE__ );
	}

	/**
	 * This function is called when the plugin is activated. It checks to see if MainWP is activated. If it is, then it calls the functions
	 * hook_managesites_subpage, hook_get_metaboxes, widgets_screen_options & initiates the main Admin Class that controls the rest of the extension's behavior.
	 */
	public function activate_this_plugin() {

		$this->mainwpMainActivated = apply_filters( 'mainwp_activated_check', $this->mainwpMainActivated );
		$this->childEnabled        = apply_filters( 'mainwp_extension_enabled_check', __FILE__ );
		$this->childKey            = $this->childEnabled['key'];

		if ( function_exists( 'mainwp_current_user_can' ) && ! mainwp_current_user_can( 'extension', 'mainwp-development-extension' ) ) {
			return;
		}

		add_filter( 'mainwp_getmetaboxes', array( &$this, 'hook_get_metaboxes' ) );
		add_filter( 'mainwp_widgets_screen_options', array( MainWP_AAM_Admin::get_instance(), 'widgets_screen_options' ), 10, 1 );

		MainWP_AAM_Admin::get_instance();
	}

	/**
	 * Hook hook_log_specific.
	 *
	 * @return mixed $inputs.
	 */
	public function hook_log_specific( $inputs ) {
		$inputs[ MAINWP_AAM_LOG_PRIORITY ] = __( 'AAM logs', 'aam-extension-mainwp' );
		return $inputs;
	}
	public function get_child_key() {
		return $this->childKey;
	}

	public function get_child_file() {
		return $this->childFile;
	}

	/**
	 * Displays an admin notice if MainWP is not activated.
	 *
	 * @return void
	 */
	public function admin_notices() {
		global $current_screen;
		if ( $current_screen->parent_base == 'plugins' && $this->mainwpMainActivated == false ) {
			echo '<div class="error"><p>' . sprintf(
				/* translators: 1: A-tag to MainWP, 2: A-tag to MainWP, 3: A-tag to MainWP, 4: A-tag to MainWP */
				esc_html__(
					'MainWP AAM Extension requires %1$sMainWP Dashboard Plugin%2$s to be activated in order to work. Please install and activate %3$sMainWP Dashboard Plugin%4$s first.',
					'aam-extension-mainwp'
				),
				'<a href="http://mainwp.com/" target="_blank">',
				'</a>',
				'<a href="http://mainwp.com/" target="_blank">',
				'</a>'
			) . '</p></div>';
		}
	}

	/**
	 * Admin init hook
	 *
	 * @return void
	 */
	public function admin_init() {
        if ( ! isset( $_GET['page'] ) || 'managesites' == $_GET['page'] ) {
            wp_enqueue_style( 'aam-extension-mainwp', MAINWP_AAM_PLUGIN_URL . 'media/style.css', array(), MAINWP_AAM_EXTENSION_VERSION );
            wp_enqueue_script( 'aam-extension-mainwp', MAINWP_AAM_PLUGIN_URL . 'media/javascript.js', array( 'jquery' ), MAINWP_AAM_EXTENSION_VERSION, array( 'in_footer' => true ) );
        }
    }

	/**
	 * Activates the extension in WordPress.
	 * @return void
	 */
	public function activate() {
		$options = array(
			'product_id'       => $this->product_id,
			'software_version' => $this->software_version,
		);
		do_action( 'mainwp_activate_extention', $this->plugin_handle, $options );
	}

	/**
	 * Deactivates the extension in WordPress.
	 *
	 * @return void
	 */
	public function deactivate() {
		do_action( 'mainwp_deactivate_extention', $this->plugin_handle );
	}

	/**
	 * Adds metabox (widget) on the MainWP Dashboard overview page via the 'mainwp_getmetaboxes' filter.
	 *
	 * @param array $metaboxes Array containing metaboxes data.
	 *
	 * @return array $metaboxes Updated array that contains metaboxes data.
	 */
	public function hook_get_metaboxes( $metaboxes ) {
		if ( ! $this->childEnabled ) {
			return $metaboxes;
		}

		if ( ! is_array( $metaboxes ) ) {
			$metaboxes = array();
		}

		if ( isset( $_GET['dashboard'] ) ) {
			$data = MainWP_AAM_DB::get_instance()->get_sync_data( intval( $_GET['dashboard'] ) );

			if ( ! empty( $data['security_score'] ) ) {
				$metaboxes[] = array(
					'id'            => 'aam-widget',
					'plugin'        => $this->childFile,
					'key'           => $this->childKey,
					'metabox_title' => __( 'Advanced Access Manager', 'aam-extension-mainwp' ),
					'callback'      => array( MainWP_AAM_Widget::get_instance(), 'render_metabox' ),
				);
			}
		}


		return $metaboxes;
	}

}

global $mainWPAAMExtensionActivator;
$mainWPAAMExtensionActivator = new MainWP_AAM_Extension_Activator();