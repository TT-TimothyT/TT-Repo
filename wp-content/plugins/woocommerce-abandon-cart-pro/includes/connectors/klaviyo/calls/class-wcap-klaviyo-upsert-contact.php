<?php
/**
 * AC insert and update file
 *
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Connectors/klaviyo
 */

/**
 * Insert and Update an contact on klaviyo
 */
class Wcap_Klaviyo_Upsert_Contact extends Wcap_Call {
	/**
	 * Slug name of the class
	 *
	 * @var string $call_slug.
	 */
	public $call_slug = 'wcap_klaviyo_upsert_contact';

	/**
	 * Base url of the API
	 *
	 * @var string $api_url.
	 */

	public $api_url = 'https://a.klaviyo.com/api/v2/';
	/**
	 * Single instance of the class.
	 *
	 * @var object $ins.
	 */
	private static $ins = null;

	/**
	 * Skip sending Merge Fields validation, if merge fields are not being updated
	 *
	 * @var bool $skip_merge_validation - to skip or not to skip validations
	 */
	private $skip_merge_validation = true;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->required_fields = array();
	}

	/**
	 * Get Single instance of the class
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Preprocess before processing by adding, unsetting new fields.
	 */
	private function preprocess() {

		if ( isset( $this->data['customer_id'] ) ) {
			$this->customer_id = $this->data['customer_id'];
			unset( $this->data['customer_id'] );
		}
	}

	/**
	 * Process the REST call to a url/endpoint to insert/update a contact
	 */
	public function process() {
		$this->preprocess();
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		$params = array(
			'token'      => $this->data['public_key'],
			'properties' => array( '$email' => $this->data['email'] ),
		);
		$update = false;

		$update_array = array( '$first_name', '$last_name', '$phone_number' );
		foreach ( $update_array as $key ) {
			if ( isset( $this->data[ $key ] ) ) {
				$params['properties'][ $key ] = $this->data[ $key ];

				$update = true;
			}
		}
		$data = 'data=' . wp_json_encode( $params );

		$headers = array(
			'Accept: text/html',
			'Content-Type: application/x-www-form-urlencoded',
		);
		$this->make_wp_requests( $this->get_identify_endpoint(), $data, $headers, Wcap_Call::$POST ); // phpcs:ignore

		Wcap_Klaviyo::set_headers();
		$check_if_contact_exists = $this->check_if_contact_exists( $this->data['email'] );

		$data    = array( 'profiles' => array( array( 'email' => $this->data['email'] ) ) );
		$data    = wp_json_encode( $data );
		$headers = array(
			'Accept: application/json',
			'Content-Type' => 'application/json; charset=utf-8',
		);
		$url     = $this->get_list_profile_endpoint();
		$result  = $this->make_wp_requests( $url, $data, $headers, Wcap_Call::$POST ); // phpcs:ignore

		return $check_if_contact_exists;
	}

	/**
	 * Check if contact exists
	 *
	 * @param string $email - Email id to check connection.
	 */
	private function check_if_contact_exists( $email = '' ) {

		$klaviyo_customer_details = wcap_get_cart_session( 'klaviyo_customer_details' );
		if ( isset( $klaviyo_customer_details['email'] ) && $klaviyo_customer_details['email'] === $email ) {
			return $klaviyo_customer_details;
		}
		$endpoint = $this->get_endpoint() . '&email=' . rawurlencode( $email ); // . '&filters[connectionid]=' . $connectionid;

		$response = $this->make_wp_requests( $endpoint, '', Wcap_Klaviyo::get_headers(), Wcap_Call::$GET ); //phpcs:ignore
		if ( ! empty( $response['body'] ) ) {
			$response['body']['email'] = $email;
			return $response;
		}
		return false;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->api_url . 'people/search?api_key=' . $this->data['private_key'];
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_identify_endpoint() {
		return 'https://a.klaviyo.com/api/identify';
	}
	/**
	 * Return the profile endpoint.
	 *
	 * @return string
	 */
	public function get_list_profile_endpoint() {
		return 'https://a.klaviyo.com/api/v2/list/' . $this->data['list_id'] . '/members?api_key=' . $this->data['private_key'];
	}
}
return 'Wcap_Klaviyo_Upsert_Contact';
