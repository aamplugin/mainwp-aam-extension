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
		$site_id = isset( $_GET['dashboard'] ) ? $_GET['dashboard'] : 0;

		if ( empty( $site_id ) ) {
			return;
		}

		$data = MainWP_AAM_DB::get_instance()->get_sync_data($site_id);

		?>
        <div class="ui grid">
            <div class="twelve wide column">
                <h3 class="ui header handle-drag">
					<?php echo __( 'AAM Security Score', 'mainwp-aam-extension' ); ?>
                    <div class="sub header"><?php echo __( 'Your current website score', 'mainwp-aam-extension' ); ?></div>
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
		<style>.gauge-wrapper{height: 175px;overflow: hidden;}.gauge-wrapper>.gauge-container{margin: auto;}.gauge-container{width: 250px;height: 150px;display: block;}.gauge-container>.gauge .dial{stroke: rgb(233, 231, 231);stroke-width: 9;fill: rgba(0, 0, 0, 0);}.gauge-container>.gauge .value{stroke: rgb(131, 127, 127);stroke-width: 12;fill: rgba(0, 0, 0, 0);}.gauge-container>.gauge .value-text{fill: rgb(131, 127, 127);font-family: sans-serif;font-weight: bold;font-size: 1em;}</style>
		<script>
			!function(e){var t,o,F,S,n=(o=(t=e).document,F=Array.prototype.slice,S=t.requestAnimationFrame||t.mozRequestAnimationFrame||t.webkitRequestAnimationFrame||t.msRequestAnimationFrame||function(e){return setTimeout(e,1e3/60)},function(){var r="http://www.w3.org/2000/svg",M={centerX:50,centerY:50},k={dialRadius:40,dialStartAngle:135,dialEndAngle:45,value:0,max:100,min:0,valueDialClass:"value",valueClass:"value-text",dialClass:"dial",gaugeClass:"gauge",showValue:!0,gaugeColor:null,label:function(e){return Math.round(e)}};function V(e,t,n){var a=o.createElementNS(r,e);for(var i in t)a.setAttribute(i,t[i]);return n&&n.forEach(function(e){a.appendChild(e)}),a}function R(e,t){return e*t/100}function E(e,t,n){var a=Number(e);return n<a?n:a<t?t:a}function q(e,t,n,a){var i=a*Math.PI/180;return{x:Math.round(1e3*(e+n*Math.cos(i)))/1e3,y:Math.round(1e3*(t+n*Math.sin(i)))/1e3}}return function(e,r){r=function(){var n=arguments[0];return F.call(arguments,1).forEach(function(e){for(var t in e)e.hasOwnProperty(t)&&(n[t]=e[t])}),n}({},k,r);var o,l,t,n=e,s=r.max,u=r.min,a=E(r.value,u,s),c=r.dialRadius,d=r.showValue,f=r.dialStartAngle,v=r.dialEndAngle,i=r.valueDialClass,m=r.valueClass,g=(r.valueLabelClass,r.dialClass),h=r.gaugeClass,p=r.color,w=r.label,x=r.viewBox;if(f<v){console.log("WARN! startAngle < endAngle, Swapping");var A=f;f=v,v=A}function y(e,t,n,a){var i=function(e,t,n){var a=M.centerX,i=M.centerY;return{end:q(a,i,e,n),start:q(a,i,e,t)}}(e,t,n),r=i.start,o=i.end,l=void 0===a?1:a;return["M",r.x,r.y,"A",e,e,0,l,1,o.x,o.y].join(" ")}function b(e,t){var n=function(e,t,n){return 100*(e-t)/(n-t)}(e,u,s),a=R(n,360-Math.abs(f-v)),i=a<=180?0:1;d&&(o.textContent=w.call(r,e)),l.setAttribute("d",y(c,f,a+f,i))}function C(e,t){var n=p.call(r,e),a=1e3*t,i="stroke "+a+"ms ease";l.style.stroke=n,l.style["-webkit-transition"]=i,l.style["-moz-transition"]=i,l.style.transition=i}return t={setMaxValue:function(e){s=e},setValue:function(e){a=E(e,u,s),p&&C(a,0),b(a)},setValueAnimated:function(e,t){var n=a;a=E(e,u,s),n!==a&&(p&&C(a,t),function(e){var t=e.duration,a=1,i=60*t,r=e.start||0,o=e.end-r,l=e.step,s=e.easing||function(e){return(e/=.5)<1?.5*Math.pow(e,3):.5*(Math.pow(e-2,3)+2)};S(function e(){var t=a/i,n=o*s(t)+r;l(n,a),a+=1,t<1&&S(e)})}({start:n||0,end:a,duration:t||1,step:function(e,t){b(e,t)}}))},getValue:function(){return a}},function(e){o=V("text",{x:50,y:50,fill:"#999",class:m,"font-size":"100%","font-family":"sans-serif","font-weight":"normal","text-anchor":"middle","alignment-baseline":"middle","dominant-baseline":"central"}),l=V("path",{class:i,fill:"none",stroke:"#666","stroke-width":2.5,d:y(c,f,f)});var t=R(100,360-Math.abs(f-v)),n=V("svg",{viewBox:x||"0 0 100 100",class:h},[V("path",{class:g,fill:"none",stroke:"#eee","stroke-width":2,d:y(c,f,v,t<=180?0:1)}),V("g",{class:"text-container"},[o]),l]);e.appendChild(n)}(n),t.setValue(a),t}}());"function"==typeof define&&define.amd?define(function(){return n}):"object"==typeof module&&module.exports?module.exports=n:e.Gauge=n}("undefined"==typeof window?this:window);

			jQuery(function () {
				Gauge(document.getElementById('security_gauge'), {
                    min: 0,
                    max: 100,
                    dialStartAngle: 180,
                    dialEndAngle: 0,
                    value: jQuery('#security_gauge').data('score'),
                    label: function(value) {
                        return value;
                    },
                    color: function(value) {
                        let result = '#3c763d';

                        if(value < 75) {
                            result = '#a94442';
                        } else if(value <= 90) {
                            result = '#8a6d3b';
                        }

                        return result;
                    }
                });
			});
		</script>
        <div class="ui hidden divider"></div>
        <div class="ui divider" style="margin-left:-1em;margin-right:-1em;"></div>
        <div class="ui two columns grid">
            <div class="left aligned column">
                <a href="<?php echo esc_attr(MainWP_AAM_Utility::get_site_aam_url( $site_id )); ?>" target="_blank" class="ui mini green button"><?php esc_html_e( 'Go to Report', 'mainwp-aam-extension' ); ?></a>
            </div>
        </div>
		<?php
	}
}
