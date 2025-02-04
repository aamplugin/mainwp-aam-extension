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
		global $wpdb;

		// Check if we are going to update or insert new value
		$existing = $this->get_sync_data($site_id);

		if (!empty($existing)) {
			$result = $wpdb->query( $wpdb->prepare(
				'UPDATE %i SET `value` = %s WHERE `wpid` = %d AND `name` = %s',
				$this->table,
				serialize( $data ),
				$site_id,
				'aam_sync_data'
			) );
		} else {
			$result = $wpdb->query( $wpdb->prepare(
				'INSERT INTO %i (`wpid`, `name`, `value`) VALUES (%d, %s, %s)',
				$this->table,
				$site_id,
				'aam_sync_data',
				serialize( $data )
			) );
		}

		return $result;
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
		global $wpdb;

		$query = $wpdb->prepare(
			'SELECT `value` FROM %i WHERE `name` = %s AND `wpid` = %d LIMIT 1',
			$this->table,
			'aam_sync_data',
			$site_id
		);

		$cache_key = md5( $query );
		$result    = wp_cache_get( $cache_key, 'mainwp-aam' );


		if ( empty( $result ) ) {
			$result = $wpdb->get_var( $query );

			wp_cache_set( $cache_key, $result, 'mainwp-aam' );
		}

		return maybe_unserialize( $result );
	}

}
