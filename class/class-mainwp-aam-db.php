<?php
/**
 * MainWP AAM DB
 *
 * This class handles the DB process.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\AAM;

 /**
  * Class MainWP_AAM_DB
  *
  * @package MainWP/Extensions
  */
class MainWP_AAM_DB {

	/**
	 * @var self|null The singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * @var string DB table prefix
	 */
	private $table;

	/**
	 * @var \wpdb $wpdb WordPress database object.
	 */
	private $wpdb;

	/**
	 * Get the singleton instance.
	 *
	 * @return self|null
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * MainWP_AAM_DB constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		$this->table = $wpdb->prefix . 'mainwp_wp_options';
  		$this->wpdb = &$wpdb;
	}

	/**
	 * Install Extension.
	 *
	 * @return void
	 */
	public function install() {

	}

	/**
	 * Save sync data
	 *
	 * @param array $data
	 * @param int   $site_id
	 *
	 * @return int|bool
	 * @access public
	 */
	public function save_sync_data($data, $site_id) {
		// Check if we are going to update or insert new value
		$existing = $this->get_sync_data($site_id);

		if (!empty($existing)) {
			$query = $this->wpdb->prepare( "UPDATE `$this->table` SET `value` = %s WHERE `wpid` = %d AND `name` = %s", serialize($data), $site_id, 'aam_sync_data' );
		} else {
			$query = $this->wpdb->prepare( "INSERT INTO `$this->table` (`wpid`, `name`, `value`) VALUES (%d, %s, %s)", $site_id, 'aam_sync_data', serialize($data) );
		}

		return $this->wpdb->query( $query );
	}

	/**
	 * Get sync data
	 *
	 * @param int $site_id
	 * @return mixed
	 *
	 * @access public
	 */
	public function get_sync_data($site_id) {
		return maybe_unserialize( $this->wpdb->get_var( $this->wpdb->prepare( "SELECT `value` FROM $this->table WHERE `name` = %s AND `wpid` = %d LIMIT 1", 'aam_sync_data', $site_id ) ));
	}

}
