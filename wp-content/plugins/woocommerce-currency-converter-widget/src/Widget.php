<?php
/**
 * Currency Converter Widget.
 *
 * @since 1.9.0
 */

namespace KoiLab\WC_Currency_Converter;

defined( 'ABSPATH' ) || exit;

use KoiLab\WC_Currency_Converter\Utilities\Currency_Utils;

/**
 * Currency Converter Widget class.
 */
class Widget extends \WC_Widget {

	/**
	 * Constructor.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {
		$this->widget_id          = 'woocommerce_currency_converter';
		$this->widget_name        = __( 'WooCommerce Currency Converter', 'woocommerce-currency-converter-widget' );
		$this->widget_cssclass    = 'widget_currency_converter';
		$this->widget_description = __( 'Allow users to choose a currency for prices to be displayed in.', 'woocommerce-currency-converter-widget' );
		$this->settings           = array(
			'title'            => array(
				'type'  => 'text',
				'label' => __( 'Title:', 'woocommerce-currency-converter-widget' ),
				'std'   => __( 'Currency converter', 'woocommerce-currency-converter-widget' ),
			),
			'currency_codes'   => array(
				'type'  => 'textarea',
				'label' => __( 'Currency codes:', 'woocommerce-currency-converter-widget' ),
				'std'   => __( "USD\nEUR", 'woocommerce-currency-converter-widget' ),
				'desc'  => __( "Use * to control how the amounts and currency symbols are displayed. Example: SEK* becomes 999kr. USD * becomes 999 $. If you omit * and just provide the currency (USD, EUR), WooCommerce's default currency position will be used.", 'woocommerce-currency-converter-widget' ),
			),
			'currency_display' => array(
				'type'    => 'select',
				'label'   => __( 'Currency Display Mode:', 'woocommerce-currency-converter-widget' ),
				'std'     => '',
				'options' => array(
					''       => __( 'Buttons', 'woocommerce-currency-converter-widget' ),
					'select' => __( 'Select Box', 'woocommerce-currency-converter-widget' ),
				),
			),
			'message'          => array(
				'type'  => 'textarea',
				'label' => __( 'Widget message:', 'woocommerce-currency-converter-widget' ),
				'std'   => __( 'Currency conversions are estimated and should be used for informational purposes only.', 'woocommerce-currency-converter-widget' ),
			),
			'show_symbols'     => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show currency symbols', 'woocommerce-currency-converter-widget' ),
			),
			'show_reset'       => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show reset link', 'woocommerce-currency-converter-widget' ),
			),
			'disable_location' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( "Disable location detection and default to the store's currency.", 'woocommerce-currency-converter-widget' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output the widget content
	 *
	 * @since 1.9.0
	 *
	 * @param array $args     Arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		$this->widget_start( $args, $instance );

		/**
		 * Fires just after displaying the widget title.
		 *
		 * @since 1.4.0
		 *
		 * @param array $instance Widget instance.
		 */
		do_action( 'woocommerce_currency_converter', $instance, true );

		$this->widget_end( $args );
	}

	/**
	 * Sets the current currency into a cookie.
	 *
	 * @since 1.6.4
	 * @deprecated 2.2.2
	 *
	 * @param array $instance Widget instance.
	 */
	public function maybe_set_cookie( $instance ) {
		wc_deprecated_function( __FUNCTION__, '2.2.2', '\KoiLab\WC_Currency_Converter\AJAX::fetch_initial_currency()' );
		$current_currency = $this->get_current_currency( $instance );

		// Save the currency in the cookie.
		if ( empty( $_COOKIE['woocommerce_current_currency'] ) || ( $_COOKIE['woocommerce_current_currency'] !== $current_currency ) ) {
			?>
			<script type="text/javascript">
				let set_initial_currency = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( wp_json_encode( $current_currency ) ); ?>' ) );
			</script>
			<?php
		}
	}

	/**
	 * Gets the current currency to set the cookie.
	 *
	 * @since 1.9.0
	 * @deprecated 2.2.2
	 *
	 * @param array $instance Widget instance.
	 * @return string
	 */
	protected function get_current_currency( array $instance ) {
		wc_deprecated_function( __FUNCTION__, '2.2.2', '\KoiLab\WC_Currency_Converter\Utilities\Currency_Utils::get_by_widget()' );

		// If a cookie is set then use that.
		if ( ! empty( $_COOKIE['woocommerce_current_currency'] ) ) {
			return wc_clean( wp_unslash( $_COOKIE['woocommerce_current_currency'] ) );
		}

		return Currency_Utils::get_by_widget( $this->id );
	}
}

class_alias( Widget::class, 'Themesquad\WC_Currency_Converter\Widget' );
