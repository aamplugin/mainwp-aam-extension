<?php
/**
 * MainWP AAM Widget
 *
 * This class handles the Widget process.
 *
 * @package MainWP/Extensions
 */

namespace MainWP\Extensions\AAM;


/**
 * Class MainWP_AAM_Widget
*
* @package MainWP/Extensions
*/
class MainWP_AAM_Widget {

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// construct.
	}


	/**
	 * Render Metabox
	 *
	 * Initiates the correct widget depending on which page the user lands on.
	 */
	public function render_metabox() {
		if ( ! isset( $_GET['page'] ) || 'managesites' == $_GET['page'] ) {
			$this->render_site_overview_widget();
		}
	}

	/**
	 * Individual Metabox
	 *
	 * Renders the individual site Overview page widget content.
	 */
	public static function render_site_overview_widget() {
		$site_id = isset( $_GET['dashboard'] ) ? intval( $_GET['dashboard'] ) : 0;

		if ( empty( $site_id ) ) {
			return;
		}

		$data = MainWP_AAM_DB::get_instance()->get_sync_data($site_id);

		?>
        <div class="ui grid">
            <div class="twelve wide column">
                <h3 class="ui header handle-drag">
					<?php echo esc_js( __( 'AAM Security Score', 'aam-extension-mainwp' ) ); ?>
                    <div class="sub header"><?php echo esc_js( __( 'Your current website score', 'aam-extension-mainwp' ) ); ?></div>
                </h3>
            </div>
        </div>
        <div class="ui hidden divider">
			<div class="gauge-wrapper">
                <div id="security_gauge" class="gauge-container" data-score="<?php echo esc_attr($data['security_score']); ?>"></div>
            </div>
		</div>
        <div class="ui fluid placeholder">
        </div>
        <div class="ui hidden divider"></div>
        <div class="ui divider" style="margin-left:-1em;margin-right:-1em;"></div>
        <div class="ui two columns grid">
            <div class="left aligned column">
                <a href="<?php echo esc_attr(MainWP_AAM_Utility::get_site_aam_url( $site_id )); ?>" target="_blank" class="ui mini green button"><?php esc_html_e( 'Go to Report', 'aam-extension-mainwp' ); ?></a>
            </div>
        </div>
		<?php
	}
}
