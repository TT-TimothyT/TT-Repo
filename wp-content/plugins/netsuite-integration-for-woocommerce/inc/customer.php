<?php

/**
 * This class handles all API operations related to creating and updating customer
 * on Netsuite
 * API Ref : http://tellsaqib.github.io/NSPHP-Doc/index.html
 *
 * Author : Manish Gautam
 */

// Including development toolkit provided by Netsuite
use NetSuite\NetSuiteService;
use NetSuite\Classes\Customer;
use NetSuite\Classes\UpdateRequest;
use NetSuite\Classes\AddRequest;
use NetSuite\Classes\SearchStringField;
use NetSuite\Classes\CustomerSearchBasic;
use NetSuite\Classes\SearchRequest;
use NetSuite\Classes\StringCustomFieldRef;
use NetSuite\Classes\CustomFieldList;
use NetSuite\Classes\MultiSelectCustomFieldRef;
use NetSuite\Classes\ListOrRecordRef;
use NetSuite\Classes\SelectCustomFieldRef;
use NetSuite\Classes\RecordRef;
use NetSuite\Classes\BooleanCustomFieldRef;
use NetSuite\Classes\UpdateResponse;
use NetSuite\Classes\DateCustomFieldRef;
use NetSuite\Classes\SearchMultiSelectField;

class CustomerClient extends CommonIntegrationFunctions {

	public $netsuiteService;
	public $object_id;
	public $user_id;
	public $custFields = array();

	public function __construct() {
		if ( TMWNI_Settings::areCredentialsDefined() ) {
			$this->netsuiteService = new NetSuiteService( null, array( 'exceptions' => true ) );
		}
	}
	/**
	 * Search customer using email
	 */
	public function searchCustomer( $email, $customer_id = 0 ) {
		$search_customer_status = true;

		/**
			*Hook for check search customer status to netsuite or not.

			* @since 1.0.0
			*/
			$search_customer_status  = apply_filters( 'tm_netsuite_search_customer_status', $search_customer_status, $email, $customer_id );

		if ( false != $search_customer_status ) {
			$this->object_id = $customer_id;
			$this->netsuiteService->setSearchPreferences( false, 20 );
			$SearchField = new SearchStringField();
			$SearchField->operator = 'is';
			$SearchField->searchValue = $email;
			$search = new CustomerSearchBasic();
			$search->email = $SearchField;
			/**
				*Hook for search customer to netsuite.

				* @since 1.0.0
				*/
			$search = apply_filters( 'tm_ns_search_customer_request', $search, $email, $customer_id );
			$request = new SearchRequest();
			$request->searchRecord = $search;
			try {
				$searchResponse = $this->netsuiteService->search( $request );
				$customer_internalId = $this->handleAPISearchResponse( $searchResponse, 'customer Search', $email );
				/**
					*Hook for netsuite customer response.

					* @since 1.0.0
					*/
				$searchResponse = apply_filters( 'tm_ns_customer_response', $searchResponse, $customer_internalId, $customer_id );

				if ( isset( $searchResponse->searchResult->totalRecords ) && $searchResponse->searchResult->totalRecords > 0 ) {
					$customer_internalId = $searchResponse->searchResult->recordList->record[0]->internalId;
				}

				if ( ! empty( $customer_internalId ) ) {
					return $searchResponse;
				} else {
					return 0;
				}
			} catch ( SoapFault $e ) {
				$object = 'customer';
				$error_msg = "SOAP API Error occured on '" . ucfirst( $object ) . " Search' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
				$error_msg .= 'Search Keyword: ' . $email . '. ';
				$error_msg .= 'Error Message: ' . $e->getMessage();

				$this->handleLog( 0, $this->object_id, $object, $error_msg );

				return 0;
			}
		}
		return 0;
	}

	public function searchCustomerByInternalId( $internal_id, $customer_id = 0 ) {
		$this->object_id = $customer_id;
		$this->netsuiteService->setSearchPreferences( false, 20 );

		$record = new RecordRef();
		$record->internalId = $internal_id;
		$SearchField = new SearchMultiSelectField();
		$SearchField->operator = 'anyOf';
		$SearchField->searchValue = $record;
		$search = new CustomerSearchBasic();
		$search->internalId = $SearchField;
		$request = new SearchRequest();
		$request->searchRecord = $search;
		try {
			$searchResponse = $this->netsuiteService->search( $request );
			/**
					* Customer search response hook.

					* @since 1.0.0
					*/
					apply_filters( 'tm_ns_customer_internal_id_response', $searchResponse, $customer_id );
			if ( isset( $searchResponse->searchResult->recordList->record[0]->addressbookList ) && ! empty( $searchResponse->searchResult->recordList->record[0]->addressbookList ) ) {
				if ( isset( $searchResponse->searchResult->recordList->record[0]->addressbookList ) && ! empty( $searchResponse->searchResult->recordList->record[0]->addressbookList ) ) {
					return $searchResponse->searchResult->recordList->record[0]->addressbookList;
				}
			}
		} catch ( SoapFault $e ) {
			$object = 'customer';
			$error_msg = "SOAP API Error occured on '" . ucfirst( $object ) . " Search' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
			$error_msg .= 'Search Keyword: ' . $internal_id . '. ';
			$error_msg .= 'Error Message: ' . $e->getMessage();

			$this->handleLog( 0, $this->object_id, $object, $error_msg );

			return 0;
		}
	}

	public function CustomerRequestData( $customer_data, $add_list, $order_id, $customer_internal_id = 0 ) {
		global $TMWNI_OPTIONS;
		$order = wc_get_order( $order_id );
		$log_id = ! empty( $customer_data['customer_id'] ) ? $customer_data['customer_id'] : $order_id;
		$customer = new Customer();
		$this->customerConditionalMapping( $customer_data, $order, $customer_internal_id );
		if ( ! isset( $TMWNI_OPTIONS['isEntityIdAuto'] ) ) {
			$customer->entityId = $customer_data['email'];
		}
		if ( isset( $TMWNI_OPTIONS['customer_subsidiary'] ) && ! empty( $TMWNI_OPTIONS['customer_subsidiary'] ) ) {
			$subsidiary = new RecordRef();
			$subsidiary->internalId = $TMWNI_OPTIONS['customer_subsidiary'];
			$customer->subsidiary = $subsidiary;
		}
		$customer->addressbookList = $add_list;
		if ( isset( $TMWNI_OPTIONS['enableSendCustomersAsCo'] ) && 'on' == $TMWNI_OPTIONS['enableSendCustomersAsCo'] ) {
			$customer->isPerson = false;
			if ( ! empty( $customer_data['companyName'] ) ) {
				$customer->companyName = $customer_data['companyName'];
			} else {
				$customer->companyName = $customer_data['firstName'] . ' ' . $customer_data['lastName']; // set customer as company
			}
		} else {
			$customer->isPerson = true;
			$customer->firstName = ! empty( $customer_data['firstName'] ) ? $customer_data['firstName'] : TMWNI_Settings::$customerDummyFisrtName;
			$customer->lastName = ! empty( $customer_data['lastName'] ) ? $customer_data['lastName'] : TMWNI_Settings::$customerDummyLastName;
		}
		$customer->email = $customer_data['email'];
		$customer->phone = $customer_data['phone'];
		$customer->logId = $log_id;
		$customer->requestType = 'customer';
		$this->createRequest( $customer, $customer_data );
		return $customer;
	}
	/**
	 * Adding customer in Netsuite based on woocommerce customer data
	 */
	public function addCustomer( $customer_data, $add_list, $order_id = 0 ) {
		$customer_sync_status = true;
		/**
				* Hook for  add customer status.

				* @since 1.0.0
				*/
				$customer_sync_status  = apply_filters( 'tm_netsuite_customer_sync_status', $customer_sync_status, $customer_data, $add_list, $order_id );
		if ( false != $customer_sync_status ) {
			if ( ! empty( $customer_data['customer_id'] ) ) {
				$this->netsuiteService->logRequests( true );
			}
			global $TMWNI_OPTIONS;
			$this->object_id = $customer_data['customer_id'];
			$customer = $this->CustomerRequestData( $customer_data, $add_list, $order_id );
			/**
				* Hook for  add customer request data .
			 *
					* @since 1.0.0              */
			$customer = apply_filters( 'tm_add_request_customer_data', $customer, $customer_data['customer_id'], $order_id );
			$request = new AddRequest();
			$request->record = $customer;
			try {
					$addResponse = $this->netsuiteService->add( $request );
				if ( 1 == $addResponse->writeResponse->status->isSuccess ) {
					/**
						* Hook for  after add customer.

						* @since 1.0.0
						*/
					do_action( 'tm_netsuite_after_customer_add', $addResponse, $customer_data, $add_list, $order_id );
				}
					return $this->handleAPIAddResponse( $addResponse, 'customer-add', $order_id );
			} catch ( SoapFault $e ) {
				$object = 'customer-add';
				$error_msg = "SOAP API Error occured on '" . ucfirst( $object ) . " Add' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
				$error_msg .= 'Error Message: ' . $e->getMessage();

				$this->handleLog( 0, $this->object_id, $object, $error_msg, $order_id );

				return 0;
			}
		}
	}
	/**
	 * Updating customer based on its internal id
	 */
	public function updateCustomer( $customer_data, $customer_internal_id, $add_list, $order_id = 0 ) {
		$customer_sync_status = true;
		/**
				* Hook for  update customer status.

				* @since 1.0.0
				*/
				$customer_sync_status  = apply_filters( 'tm_netsuite_customer_update_status', $customer_sync_status, $customer_data, $add_list, $order_id );
		if ( false != $customer_sync_status ) {
			if ( ! empty( $customer_data['customer_id'] ) ) {
				$this->netsuiteService->logRequests( true );
			}

			global $TMWNI_OPTIONS;
			$this->object_id = $customer_data['customer_id'];

			$customer = $this->CustomerRequestData( $customer_data, $add_list, $order_id, $customer_internal_id );

			$customer->internalId = $customer_internal_id;
			/**
					* Hook for  update customer data.

					* @since 1.0.0
					*/
			$customer = apply_filters( 'tm_update_request_customer_data', $customer, $customer_data['customer_id'], $order_id );

			$request = new UpdateRequest();
			$request->record = $customer;

			try {
					$updateResponse = $this->netsuiteService->update( $request );
					// pr($updateResponse); die('zzz');
				if ( 1 == $updateResponse->writeResponse->status->isSuccess ) {
					/**
						* Hook for  after update customer data.

						* @since 1.0.0
						*/
					do_action( 'tm_netsuite_after_customer_update', $customer_data, $add_list, $order_id );
				}
					return $this->handleAPIUpdateResponse( $updateResponse, 'customer-update', $order_id );
			} catch ( SoapFault $e ) {
				$object = 'customer-update';
				$error_msg = "SOAP API Error occured on '" . ucfirst( $object ) . " Update' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
				$error_msg .= 'Error Message: ' . $e->getMessage();
				$this->handleLog( 0, $this->object_id, $object, $error_msg, $order_id );

				return 0;
			}
		} else {
			return 0;
		}
	}
	/**
	 * Creating customer request.
	 */
	public function createRequest( &$customer, $customer_data ) {
		// pr($customer_data['ns_customer_fields']); die;
		foreach ( $customer_data['ns_customer_fields'] as $nsfield => $value ) {
			if ( isset( $value['type'] ) && ( 'string' == $value['type'] || 'float' == $value['type'] || 'integer' == $value['type'] || 'GlobalSubscriptionStatus' == $value['type'] ) ) {

				if ( 'phone' == $nsfield && strlen( $value['value'] ) > 6 ) {
					$customer->$nsfield = $value['value'];
				} else {
					$customer->$nsfield = $value['value'];
				}
			} elseif ( isset( $value['type'] ) && 'dateTime' == $value['type'] ) {

				$customer->$nsfield = gmdate( DATE_ATOM, strtotime( $value['value'] ) );

			} elseif ( isset( $value['type'] ) && 'boolean' == $value['type'] ) {

				$customer->$nsfield = $value['value'];

			} elseif ( isset( $value['type'] ) && 'RecordRef' == $value['type'] ) {

				$customer->$nsfield = new RecordRef();

				$customer->$nsfield->internalId = $value['value'];

			} elseif ( isset( $value['type'] ) && 'customboolean' == $value['type'] ) {

				$customer->customFieldList = $this->customFieldboolean( $nsfield, $value['value'] );

			} elseif ( isset( $value['type'] ) && 'customstringfield' == $value['type'] ) {

				$customer->customFieldList = $this->customField( $nsfield, $value['value'] );

			} elseif ( isset( $value['type'] ) && 'customselectfield' == $value['type'] ) {

				$customer->customFieldList = $this->customFieldSelect( $nsfield, $value['value'] );

			} elseif ( isset( $value['type'] ) && 'custommultiselectfield' == $value['type'] ) {

				$multiselectvalues = explode( ',', $value['value'] );

				$customer->customFieldList = $this->customFieldMultiSelect( $nsfield, $multiselectvalues );

			} elseif ( isset( $value['type'] ) && 'customrecordref' == $value['type'] ) {

				$customer->customFieldList = $this->customRecordRefField( $nsfield, $value['value'] );

			} elseif ( isset( $value['type'] ) && 'customcurrdatefield' == $value['type'] ) {

				$customer->customFieldList = $this->customField( $nsfield, gmdate( DATE_ATOM ) );

			} elseif ( isset( $value['type'] ) && 'customdateTime' == $value['type'] ) {

				$customer->customFieldList = $this->customField( $nsfield, $value['value'] );

			}
		}
	}

	/**
	 * Creating custom field list array.
	 */
	public function customFieldList( $custfield ) {
		$this->custFields[] = $custfield;
		$customFieldList = new customFieldList();
		$customFieldList->customField = $this->custFields;
		return $customFieldList;
	}


	/**
	 * Creating custom string field instance.
	 */
	public function customField( $scriptId, $value ) {
		$custfield = new StringCustomFieldRef();
		$custfield->scriptId = $scriptId;
		$custfield->value = $value;
		return $this->customFieldList( $custfield );
	}

	/**
	 * Creating custom string field instance.
	 */
	public function customRecordRefField( $scriptId, $value ) {
		$custfield = new StringCustomFieldRef();
		$custfield->scriptId = $scriptId;
		$custfield->value = new RecordRef();
		$custfield->value->internalId = $value;
		return $this->customFieldList( $custfield );
	}

	/**
	 * Creating custom select field instance.
	 */
	public function customFieldSelect( $scriptId, $value ) {
		$custfieldselect = new SelectCustomFieldRef();
		$custfieldselect->scriptId = $scriptId;
		$recref = new ListOrRecordRef();
		$recref->internalId = $value;
		$custfieldselect->value = $recref;
		return $this->customFieldList( $custfieldselect );
	}


	/**
	 * Creating custom multiselect field instance.
	 */
	public function customFieldMultiSelect( $scriptId, array $value ) {
		$multivalues = array();
		$custfieldmultiselect = new MultiSelectCustomFieldRef();
		$custfieldmultiselect->scriptId = $scriptId;
		foreach ( $value as $key => $item ) {
			$recref = new ListOrRecordRef();
			$recref->name = $item;
			$multivalues[] = $recref;
		}
		$custfieldmultiselect->value = $multivalues;
		return $this->customFieldList( $custfieldmultiselect );
	}


	/**
	 * Creating custom boolean field instance.
	 */
	public function customFieldboolean( $scriptId, $value ) {
		$custfieldselect = new BooleanCustomFieldRef();
		$custfieldselect->scriptId = $scriptId;
		$custfieldselect->value = $value;
		return $this->customFieldList( $custfieldselect );
	}


	/**
	 * Creating conditional mapping for customer.
	 */
	public function customerConditionalMapping( &$customer_data, $order, $customer_internal_id = '' ) {
		$this->user_id = $customer_data['customer_id'];
		$customer_data['ns_customer_fields'] = array();
		$cm_options = get_option( 'customer_cm_options' );
		if ( ! empty( $cm_options ) ) {
			foreach ( $cm_options as $key => $mapping ) {
				if ( ( ! empty( $customer_internal_id ) && isset( $mapping['exlcude_in_update'] ) && 'on' == $mapping['exlcude_in_update'] ) ) {
					continue;
				}
				$saved_value = '';
				switch ( $mapping['operator'] ) {
					case 1:
						if ( '' != $mapping['wc_field_key'] && '' != $mapping['wc_field_value'] && '' != $mapping ['ns_field_key'] && '' != $mapping['ns_field_value'] ) {
							if ( 1 == $mapping['type'] ) {
								if ( 'user_id' == $mapping['wc_field_key'] ) {
									$saved_value = $this->user_id;
								} elseif ( 'email' == $mapping['wc_field_key'] ) {
									$saved_value = $customer_data['email'];
								} else {
									// woo default
									$saved_value = get_user_meta( $this->user_id, ( false === strstr( $mapping['wc_field_key'], 'shipping_' ) ? 'billing_' : '' ) . $mapping ['wc_field_key'], true );
								}
							} else if ( 2 == $mapping['type'] ) {
								$saved_value = get_user_meta( $this->user_id, $mapping['wc_field_key'], true );
							} else if ( 3 == $mapping['type'] ) {
								$orderkey = $mapping['wc_field_key'];
								if ( ! empty( $order ) ) {
									$saved_value = tm_ns_get_order_data( $order, $mapping );
								}
							} else if ( 4 == $mapping['type'] ) {
								if ( ! empty( $order ) ) {
									// $saved_value = get_post_meta($order->get_id(), $mapping ['wc_field_key'], true);
									$saved_value = tm_ns_get_post_meta( $order->get_id(), $mapping['wc_field_key'] );
								}
							}

							if ( 'contains' == $mapping['wc_where_op'] ) {
								if ( false !== strpos( strtolower( $saved_value ), strtolower( $mapping['wc_field_value'] ) ) ) {
									$customer_data['ns_customer_fields'][ $mapping['ns_field_key'] ] = array(
										'type' => trim( $mapping['ns_field_type_value'] ),
										'value' => $mapping['ns_field_value'],
									);
								}
							} elseif ( 'doesnotcontain' == $mapping['wc_where_op'] ) {
								if ( false === strpos( strtolower( $saved_value ), strtolower( $mapping['wc_field_value'] ) ) ) {
									$customer_data['ns_customer_fields'][ $mapping['ns_field_key'] ] = array(
										'type' => trim( $mapping['ns_field_type_value'] ),
										'value' => $mapping['ns_field_value'],
									);
								}
							} elseif ( 'is' == $mapping['wc_where_op'] ) {
								if ( 'null' == strtolower( $mapping['wc_field_value'] ) ) {
									if ( empty( $saved_value ) ) {
										$customer_data['ns_customer_fields'][ $mapping['ns_field_key'] ] = array(
											'type' => trim( $mapping['ns_field_type_value'] ),
											'value' => $mapping['ns_field_value'],
										);
									}
								} elseif ( strtolower( $saved_value ) == strtolower( $mapping['wc_field_value'] ) ) {
									$customer_data['ns_customer_fields'][ $mapping['ns_field_key'] ] = array(
										'type' => trim( $mapping['ns_field_type_value'] ),
										'value' => $mapping['ns_field_value'],
									);
								}
							} elseif ( 'isnot' == $mapping['wc_where_op'] ) {
								if ( 'null' == strtolower( $mapping['wc_field_value'] ) ) {
									if ( ! empty( $saved_value ) ) {
										$customer_data['ns_customer_fields'][ $mapping['ns_field_key'] ] = array(
											'type' => trim( $mapping['ns_field_type_value'] ),
											'value' => $mapping['ns_field_value'],
										);
									}
								} elseif ( strtolower( $saved_value ) != strtolower( $mapping['wc_field_value'] ) ) {
									$customer_data['ns_customer_fields'][ $mapping['ns_field_key'] ] = array(
										'type' => trim( $mapping['ns_field_type_value'] ),
										'value' => $mapping['ns_field_value'],
									);
								}
							}
						}
						break;
					case 2:
						if ( '' != $mapping['ns_field_key'] && '' != $mapping['ns_field_value'] ) {
							$customer_data['ns_customer_fields'][ $mapping['ns_field_key'] ] = array(
								'type' => trim( $mapping['ns_field_type_value'] ),
								'value' => $mapping['ns_field_value'],
							);
						}
						break;
					case 3:
						if ( '' != $mapping['wc_field_key'] && '' != $mapping['ns_field_key'] ) {
							$prefix = isset( $mapping['wc_field_value_prefix'] ) ? $mapping['wc_field_value_prefix'] : '';
							if ( 1 == $mapping['type'] ) {
								if ( 'user_id' == $mapping['wc_field_key'] ) {
									$saved_value = $this->user_id;
								} elseif ( 'email' == $mapping['wc_field_key'] ) {
									$saved_value = $customer_data['email'];
								} else {
									$saved_value = get_user_meta( $this->user_id, ( false === strstr( $mapping['wc_field_key'], 'shipping_' ) ? 'billing_' : '' ) . $mapping ['wc_field_key'], true );
								}
							} else if ( 2 == $mapping['type'] ) {
								$saved_value = get_user_meta( $this->user_id, $mapping['wc_field_key'], true );
							} else if ( 3 == $mapping['type'] ) {
								if ( ! empty( $order ) ) {
									$saved_value = tm_ns_get_order_data( $order, $mapping );
								}
							} else if ( 4 == $mapping ['type'] ) {
								if ( ! empty( $order ) ) {
									$saved_value = tm_ns_get_post_meta( $order->get_id(), $mapping['wc_field_key'] );
								}
							}
							if ( isset( $saved_value ) && ! empty( $saved_value ) ) {
								$customer_data['ns_customer_fields'][ $mapping['ns_field_key'] ] = array(
									'type' => trim( $mapping['ns_field_type_value'] ),
									'value' => $prefix . $saved_value,
								);
							}
						}
						break;
					default:
						break;
				}
			}
		}

		return true;
	}
}
