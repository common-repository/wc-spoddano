<?php
/**
 * Cardano (ADA) Payment Gateway.
 *
 * Provides a Payment Gateway using the Cryptocurrency ADA from Cardano.
 *
 * @class       WC_Gateway_spoddano
 * @extends     WC_Payment_Gateway
 * @version     1.0.0
 * @package     WooCommerce\Classes\Payment
 */

//Security - exit file if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_spoddano extends WC_Payment_Gateway {
/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		// Setup general properties.
		$this->setup_properties();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get settings.
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->disclaimer         = $this->get_option( 'disclaimer' );
		$this->address            = $this->get_option( 'address' );
		$this->qrcode             = $this->get_option( 'qrcode' );
		$this->apikey             = $this->get_option( 'apikey' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
		$this->id                 = 'spoddano';
		$this->icon               = apply_filters( 'woocommerce_spoddano_icon', plugins_url('../assets/icon.png', __FILE__) );
		$this->method_title       = __( 'Cardano (ADA) Gateway', 'woocommerce' );
		$this->method_description = __( 'Have your customers pay with Cardano (ADA).', 'woocommerce' );
		$this->has_fields         = false;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'            => array(
				'title'       => __( 'Enable/Disable', 'spod-ada-txt' ),
				'label'       => __( 'Enable Cardano Gateway', 'spod-ada-txt' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),

			'firstitle'       => array(
				'title'       => __( 'Cardano Payment Settings', 'spod-ada-txt' ),
				'css'		    => 'display:none;',
			),

			'apikey' => array(
				'title' 	  => __('API Key', 'spod-ada-txt'), 
				'type' 	      => 'text',
				'css'		  => 'width:100%;',
				'description' => __('This is your api key from coinmarketcap.com.<br>Sign up for the free basic one here: <a href="https://pro.coinmarketcap.com/signup/" target="_blank">https://pro.coinmarketcap.com/signup/</a>', 'spod-ada-txt'),
				'default'     => __('','spod-ada-txt'),
				//'desc_tip'    => true,
			),

			'address' => array(
				'title' 	  => __('Cardano Receiving Address', 'spod-ada-txt'), 
				'type' 	      => 'text',
				'css'		  => 'width:100%;',
				'description' => __('This is your address the Cardano will be paid into, SHELLY ADDRESSES ONLY', 'spod-ada-txt'),
				'default'     => __('Enter Address','spod-ada-txt'),
				'desc_tip'    => true,
			),
			'qrcode' => array(
				'title' 		=> __('Upload receiving address QR Code Image', 'spod-ada-txt'),
				'type'  		=> 'text',
				'css'		    => 'width:100%;',
				'description'   => __('This is the QR code image associated with your receiving address. This will make processing transactions easier for customers.'),
				//'default'       => __(''),
				'desc_tip'      => true,
			),

			'secondtitle'     => array(
				'title'       => __( 'Checkout Page Details', 'spod-ada-txt' ),
				'css'		    => 'display:none;',
			),

			'title'              => array(
				'title'       => __( 'Title', 'spod-ada-txt' ),
				'type'        => 'text',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'spod-ada-txt' ),
				'default'     => __( 'Cardano (ADA)', 'spod-ada-txt' ),
				'desc_tip'    => true,
			),
			'description'        => array(
				'title'       => __( 'Description', 'spod-ada-txt' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your website.', 'spod-ada-txt' ),
				'default'     => __( 'Pay with Cardano.', 'spod-ada-txt' ),
				'desc_tip'    => true,
			),
			'disclaimer'       => array(
				'title'       => __( 'Disclaimer', 'spod-ada-txt' ),
				'type'        => 'textarea',
				'description' => __( 'A disclaimer that will be added to checkout panel', 'spod-ada-txt' ),
				'default'     => __( 'Please only send Cardano (ADA) to this address. Sending any other digital asset will result in permanent loss. Please make sure you have correctly scanned the QR Code above and/or verified the address is correct in your wallet. We can not be held responsible for incorrectly processed transactions.', 'spod-ada-txt' ),
				'desc_tip'    => true,
			),
		);
	}


	/**
	 * Checks to see whether or not the admin settings are being accessed by the current request.
	 *
	 * @return bool
	 */
	private function is_accessing_settings() {
		if ( is_admin() ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['tab'] ) || 'checkout' !== $_REQUEST['tab'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['section'] ) || 'spoddano' !== $_REQUEST['section'] ) {
				return false;
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return true;
		}

		return false;
	}



	/**
	 * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
	 *
	 * @since  3.4.0
	 *
	 * @param array $rate_ids Rate ids to check.
	 * @return boolean
	 */
	private function get_matching_rates( $rate_ids ) {
		// First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
		return array_unique( array_merge( array_intersect( $this->enable_for_methods, $rate_ids ), array_intersect( $this->enable_for_methods, array_unique( array_map( 'wc_get_string_before_colon', $rate_ids ) ) ) ) );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_total() > 0 ) {
			// Mark as processing or on-hold (payment won't be taken until delivery).
			$order->update_status( apply_filters( 'woocommerce_spoddano_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ), __( 'Payment to be confirmed.', 'woocommerce' ) );
		} else {
			$order->payment_complete();
		}

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Change payment complete order status to completed for spoddano orders.
	 *
	 * @since  3.1.0
	 * @param  string         $status Current order status.
	 * @param  int            $order_id Order ID.
	 * @param  WC_Order|false $order Order object.
	 * @return string
	 */
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && 'spoddano' === $order->get_payment_method() ) {
			$status = 'completed';
		}
		return $status;
	}

  }
 

  