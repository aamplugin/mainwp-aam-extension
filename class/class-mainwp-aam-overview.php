<?php
/**
 * MainWP AAM Overview
 *
 * This class handles the Overview process.
 *
 * @package MainWP/Extensions
 */

 namespace MainWP\Extensions\AAM;

 /**
  * Class MainWP_AAM_Overview
  *
  * @package MainWP/Extensions
  */
class MainWP_AAM_Overview {

	/**
	 * @var self|null The singleton instance of the class.
	 */
	private static $instance = null;

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
	 * MainWP_AAM_Overview constructor.
     *
     * @return void
	 */
	public function __construct() {
	}

	/**
	 * Render extension page tabs.
     *
     * @return void
     */
	public static function render_tabs() {

		$current_tab = 'settings';

		?>

		<div class="ui labeled icon inverted menu mainwp-sub-submenu" id="mainwp-pro-aam-menu">
			<a href="admin.php?page=Extensions-Mainwp-Aam-Extension&tab=settings" class="item <?php echo ( $current_tab == 'settings' || $current_tab == '' ) ? 'active' : ''; ?>"><i class="file alternate outline icon"></i> <?php esc_html_e( 'Settings', 'mainwp-aam-extension' ); ?></a>
		</div>
        <div>
			<div class="sub header" style="padding: 3rem;">AAM extension currently does not have any settings</div>
		</div>
		<?php
	}
}
