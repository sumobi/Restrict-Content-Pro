<?php
/**
 * Payment Gateways Class
 *
 * @package     Restrict Content Pro
 * @subpackage  Classes/Roles
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
*/

class RCP_Payment_Gateways {

	public $available_gateways;

	public $enabled_gateways; 

	public function __construct() {

		$this->available_gateways = $this->get_gateways();
		$this->enabled_gateways   = $this->get_enabled_gateways();

	}

	public function get_gateway( $id = '' ) {

		if( isset( $this->available_gateways[ $id ] ) ) {

			return $this->available_gateways[ $id ];

		}

		return false;
	}

	private function get_gateways() {

		$gateways = array(
			'manual' => array(
				'label' => __( 'Manual Payment', 'rcp' ),
				'class' => 'RCP_Payment_Gateway_Manual'
			),
			'paypal' => array(
				'label' => __( 'PayPal', 'rcp' ),
				'class' => 'RCP_Payment_Gateway_PayPal'
			),
			'stripe' => array(
				'label' => __( 'Credit / Debit Card', 'rcp' ),
				'class' => 'RCP_Payment_Gateway_Stripe'
			)
		);

		return apply_filters( 'rcp_payment_gateways', $gateways );

	}

	private function get_enabled_gateways() {

		global $rcp_options;

		$enabled = array();
		$saved   = isset( $rcp_options['gateways'] ) ? array_map( 'trim', $rcp_options['gateways'] ) : array();

		if( $saved ) {

			foreach( $this->available_gateways as $key => $gateway ) {

				if( isset( $saved[ $key ] ) && $saved[ $key ] == 1 ) {
				
					$enabled[ $key ] = $gateway;
				
				}
			}

		}

		return apply_filters( 'rcp_enabled_payment_gateways', $enabled, $this->available_gateways );

	}

	public function load_fields() {

		if( ! empty( $_POST['rcp_gateway'] ) ) {

			$gateway = $this->get_gateway( sanitize_text_field( $_POST['rcp_gateway'] ) );
	
			if( isset( $gateway['class'] ) ) {
				$gateway = new $gateway['class'];
			}

			if ( is_object( $gateway ) ) {
				wp_send_json_success( array( 'success' => true, 'fields' => $gateway->fields() ) );
			} else {
				wp_send_json_error( array( 'success' => false ) );
			}

		}
	}
	
}