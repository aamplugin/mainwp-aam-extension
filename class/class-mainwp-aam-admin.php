<?php
/**
 * MainWP AAM Admin
 *
 * This class handles the extension process.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\AAM;

 /**
  * Class MainWP_AAM_Admin
  *
  * @package MainWP/Extensions
  */
class MainWP_AAM_Admin {

	public static $instance = null;
	public $version         = '1.0';

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * MainWP_AAM_Admin constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( &$this, 'localization' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'mainwp_delete_site', array( &$this, 'hook_delete_site' ), 10, 1 );
		add_filter( 'mainwp_sitestable_getcolumns', array( $this, 'manage_sites_column' ), 10 );
		add_filter( 'mainwp_sitestable_item', array( $this, 'manage_sites_item' ), 10 );

		MainWP_AAM_DB::get_instance()->install();
		MainWP_AAM_Overview::get_instance();
	}

	/**
	 * Manage Sites Column
	 *
	 * Adds the custom column in the Manage Sites and Monitoring tables.
	 *
	 * @param array $columns Table comlumns.
	 *
	 * @return array $columns Table comlumns.
	 */
	public function manage_sites_column( $columns ) {
		return array_merge($columns, [
			'aam_summary' =>  __( 'AAM Issues', 'aam-extension-mainwp' )
		]);
	}

	/**
	 * Manage Sites Item
	 *
	 * Adds the custom column data in the Manage Sites and Monitoring tables.
	 *
	 * @param array $item Site comlumn data.
	 *
	 * @return array $item Site comlumn data.
	 */
	public function manage_sites_item( $item ) {
		$data = MainWP_AAM_DB::get_instance()->get_sync_data($item['id']);

		if (empty($data)) {
			$summary = '';
		} else {
			$tooltip = $class = $value = '';

			if (!empty($data['issues_summary']['critical'])) {
				$tooltip = sprintf('%d critical issue(s) identified', $data['issues_summary']['critical']);
				$class = 'ui mini compact basic button red';
				$value = $data['issues_summary']['critical'];
			} elseif (!empty($data['issues_summary']['warning'])) {
				$tooltip = sprintf('%d warning(s) identified', $data['issues_summary']['warning']);
				$class = 'ui mini compact basic button orange';
				$value = $data['issues_summary']['warning'];
			} elseif (!empty($data['issues_summary']['notice'])) {
				$tooltip = sprintf('%d notice(s) identified', $data['issues_summary']['notice']);
				$class = 'ui mini compact basic button blue';
				$value = $data['issues_summary']['notice'];
			} else {
				$tooltip = 'No issues identified';
				$class = 'ui mini compact basic button green';
				$value = 0;
			}

			$summary = '<a href="' . esc_attr(MainWP_AAM_Utility::get_site_aam_url( $item['id'] )) . '" target="_blank" data-tooltip="' . esc_attr($tooltip) . '" data-position="left center" class="' . esc_attr($class) . '">' . esc_attr($value) . '</a>';
		}

		return array_merge($item, [
			'aam_summary' => $summary
		]);
	}

	/**
	 * Register the /languages folder. This will allow us to translate the extension.
	 *
	 * @return void
	 */
	public function localization() {
		load_plugin_textdomain( 'aam-extension-mainwp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Displays the meta data winthin the plugin row on the WP > Plugins > Installed Plugins page.
	 *
	 * @param $plugin_meta
	 * @param $plugin_file
	 *
	 * @return mixed Array of plugin meta data.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( 'aam-extension-mainwp/aam-extension-mainwp.php' != $plugin_file ) {
			return $plugin_meta;
		}
		$slug     = basename( $plugin_file, '.php' );
		$api_data = get_option( $slug . '_APIManAdder' );
		if ( ! is_array( $api_data ) || ! isset( $api_data['activated_key'] ) || $api_data['activated_key'] != 'Activated' || ! isset( $api_data['api_key'] ) || empty( $api_data['api_key'] ) ) {
			return $plugin_meta;
		}
		$plugin_meta[] = '<a href="?do=checkUpgrade" title="Check for updates.">Check for Update</a>';
		return $plugin_meta;
	}

	/**
	 * Widgets screen options.
	 *
	 * @param array $input Input.
	 *
	 * @return array $input Input.
	 */
	public function widgets_screen_options( $input ) {
		$input['advanced-aam-widget'] = __( 'AAM Widget', 'aam-extension-mainwp' );
		return $input;
	}
}
