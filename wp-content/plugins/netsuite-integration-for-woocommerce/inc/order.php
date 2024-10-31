<?php

/**
 * This class handles all API operations related to creating sales order on Netsuite
 * API Ref : http://tellsaqib.github.io/NSPHP-Doc/index.html
 *
 * Author : Manish Gautam
 */
use NetSuite\NetSuiteService;
use NetSuite\Classes\SearchStringField;
use NetSuite\Classes\ItemSearchBasic;
use NetSuite\Classes\SearchRequest;
use NetSuite\Classes\SalesOrder;
use NetSuite\Classes\RecordRef;
use NetSuite\Classes\Address;
use NetSuite\Classes\SalesOrderItemList;
use NetSuite\Classes\SalesOrderItem;
use NetSuite\Classes\UpdateRequest;
use NetSuite\Classes\AddRequest;
use NetSuite\Classes\PromotionsList;
use NetSuite\Classes\Promotions;
use NetSuite\Classes\PromotionCodeUseType;
use NetSuite\Classes\PromotionCode;
use NetSuite\Classes\PromotionCodeSearchBasic;
use NetSuite\Classes\DeleteRequest;
use NetSuite\Classes\CustomFieldList;
use NetSuite\Classes\StringCustomFieldRef;
use NetSuite\Classes\SelectCustomFieldRef;
use NetSuite\Classes\ListOrRecordRef;
use NetSuite\Classes\MultiSelectCustomFieldRef;
use NetSuite\Classes\BooleanCustomFieldRef;
use NetSuite\Classes\DoubleCustomFieldRef;
use Netsuite\Classes\SearchCustomFieldList;
use NetSuite\Classes\SearchCustomField;
use NetSuite\Classes\SearchStringCustomField;
use NetSuite\Classes\CashSale;
use NetSuite\Classes\CashSaleItemList;
use NetSuite\Classes\CashSaleItem;
use NetSuite\Classes\InitializeRecord;
use NetSuite\Classes\InitializeRef;
use NetSuite\Classes\Invoice;
use NetSuite\Classes\InitializeRequest;
use NetSuite\Classes\InvoiceItemList;
use NetSuite\Classes\InvoiceItem;
use NetSuite\Classes\CustomerDeposit;
use NetSuite\Classes\SearchBooleanField;


class OrderClient extends CommonIntegrationFunctions {

	public $netsuiteService;
	public $object_id;
	public $custFields = array();
	public $user_id = 0;

	public function __construct() {
		//set netsuite API client object
		if (TMWNI_Settings::areCredentialsDefined()) {
			$this->netsuiteService = new NetSuiteService();
		}
	}

	/**
	 * Search item using woocommerce product SKU
	 * ref: samples provided in Netsuite API toolkit
	 */
	public function searchItem( $item_sku, $product_id = 0, $variation_id = 0 ) {
		/** 
			*Order  item  search sku  hook.
		
			* @since 1.4.8
 
			**/
			$item_sku = apply_filters('tm_netsuite_order_item_search_sku', $item_sku, $product_id, $variation_id);

			$this->netsuiteService->setSearchPreferences(false, 20);

			global $TMWNI_OPTIONS;

			$SearchField = new SearchStringField();
			$SearchField->operator = 'is';
			$SearchField->searchValue = $item_sku;
			$search = new ItemSearchBasic();

		if (!isset($TMWNI_OPTIONS['sku_mapping_field']) || empty($TMWNI_OPTIONS['sku_mapping_field']) ) {

			$search->itemId = $SearchField;

		} elseif ('customFieldList' == $TMWNI_OPTIONS['sku_mapping_field']) {

			$search->{$TMWNI_OPTIONS['sku_mapping_field']} = $this->customSearchField($TMWNI_OPTIONS['sku_mapping_custom_field'], $item_sku);

		} else {

			$search->{$TMWNI_OPTIONS['sku_mapping_field']} = $SearchField;

		}
			$inactive = new SearchBooleanField();
			$inactive->searchValue = false;
			$search->isInactive = $inactive;
			/** 
				*Search item on NetSuite hook.
		
				* @since 1.0.0
 
				**/
				$search = apply_filters('tm_ns_order_search_item', $search, $item_sku, $product_id);
				$request = new SearchRequest();
				$request->searchRecord = $search;
				$item_internal_id = 0;
		if (!empty($variation_id)) {
			$object_id = $variation_id;
			$object = 'order product_variation search';
		} else {
			$object_id = $product_id;
			$object = 'order product search';
		}
		try {
			$searchResponse = $this->netsuiteService->search($request);
			if (!$searchResponse->searchResult->status->isSuccess) {
				$error_msg = "'" . ucfirst($object) . " Search' operation failed for WooCommerce " . $object . ', ID = ' . $object_id . '. ';
				$error_msg .= 'Search Keyword:' . $item_sku . '. ';
				$error_msg .= 'Error Message : ' . $response->writeResponse->status->statusDetail[0]->message;
				$this->handleLog(0, $object_id, $object, $error_msg);
			} else {
				if (1 == $searchResponse->searchResult->status->isSuccess) {
					/** 
						*After search item hook.
		
						* @since 1.0.0
 
						**/
				apply_filters('tm_ns_after_search_item', $searchResponse, $item_sku, $product_id);
				$item_internal_id = $searchResponse->searchResult->recordList->record[0]->internalId;
					if (!empty($variation_id)) {
							update_post_meta($variation_id, TMWNI_Settings::$ns_product_id, $item_internal_id);
					} else {
						update_post_meta($product_id, TMWNI_Settings::$ns_product_id, $item_internal_id);
					}
						$item_location_id = isset($searchResponse->searchResult->recordList->record[0]->location->internalId) ? $searchResponse->searchResult->recordList->record[0]->location->internalId : '' ;
					if (!empty($item_location_id)  || !is_null($item_location_id)) { 
						if (!empty($variation_id)) {
							update_post_meta($variation_id, 'ns_item_location_id', $item_location_id);
						} else {
							update_post_meta($product_id, 'ns_item_location_id', $item_location_id);
						}
					}
						$this->handleLog(1, $object_id, $object);
				}
			return $item_internal_id;
			}
		} catch (SoapFault $e) {
			$error_msg = "SOAP API Error occured on '" . ucfirst($object) . " Search' operation failed for WooCommerce " . $object . ', ID = ' . $object_id . '. ';
			if (!empty($variation_id)) {
				$error_msg .= 'Product ID: ' . $product_id . '. ';
			}
			$error_msg .= 'SKU: ' . $item_sku . '. ';
			$error_msg .= 'Error Message: ' . $e->getMessage();
			$this->handleLog(0, $object_id, $object, $error_msg);
			return 0;
		}
	}

	public function OrderNSRequest( $order_data, $customer_internal_id , $order_internal_id=0) {
			
		global $TMWNI_OPTIONS;

		$order = $order_data['order'];
		if (isset($TMWNI_OPTIONS['ns_order_record_type']) && 'cashsale' == $TMWNI_OPTIONS['ns_order_record_type']) {
			$so = new CashSale();
			$so->itemList = new CashSaleItemList();
			$record_type =  'cashsale.nl';
		} else {
			$so = new SalesOrder();
			$so->itemList = new SalesOrderItemList();
			$record_type = 'salesord.nl';
		}

		$so->entity = new RecordRef();
		$so->entity->internalId = $customer_internal_id;

		$this->salesOrderConditionalMapping($order_data, $order, $order_internal_id);

		$this->createRequest($so, $order_data);

		$so->billingAddress = $this->getBillingAddress($order_data, $so);
		$so->shippingAddress = $this->getShippingAddress($order_data, $so);

		$items = $this->_setOrderItems($order_data['items'], $order_data['total_shipping']);

		$so->itemList->item = $items;
		/** 
			* Order item object hook.
	
			* @since 1.0.0

			**/         
		$so->itemList->item = apply_filters('tm_ns_order_item', $items, $order_data['items'], $order_data['total_shipping'], $order_data['order_id']);

		if (isset($TMWNI_OPTIONS['ns_coupon_netsuite_sync']) && !empty($TMWNI_OPTIONS['ns_coupon_netsuite_sync'])) {

			$promoCodes = array();

			$order = new WC_Order($order_data['order_id']);

			$applied_coupons = $order->get_coupon_codes();

			$coupon_sync_status = true;
			/** 
				* Coupon sync status hook.
	
				* @since 1.4.8

				**/
			$coupon_sync_status = apply_filters('tm_ns_add_netsuite_coupon', $order_data, $customer_internal_id);

			if (!empty($applied_coupons) && true == $coupon_sync_status) {
				foreach ( $applied_coupons as $key => $value) {
					if (isset($TMWNI_OPTIONS['ns_promo_discount_id']) && !empty($TMWNI_OPTIONS['ns_promo_discount_id']) ) {
						$promoCodes[] = $this->_addNSpromo($value, $TMWNI_OPTIONS['ns_promo_discount_id']);
					} else {
						$promoCodes[] = $this->_addNSpromo($value);
					}
				}
			}

			if ( isset($promoCodes) && !empty($promoCodes) ) {

				$so->canHaveStackable = 1;

				$promoListObj = new PromotionsList();
				$promoListObj->promotions = array();
				foreach ($promoCodes as $codeKey => $codeValue) {

					$recRef = new RecordRef();
					$recRef->internalId = $codeValue;
					$promoListObj->promotions[$codeKey] = new Promotions();
					$promoListObj->promotions[$codeKey]->promoCode = $recRef;
				}

				$so->promotionsList = $promoListObj;

			}
		}

		$so->logId = $order_data['order_id'];
		$so->requestType = 'order';

		tm_ns_update_post_meta($order_data['order_id'], TMWNI_Settings::$ns_external_order_id, $order_data['order_id']);
		tm_ns_update_post_meta($order_data['order_id'], 'ns_record_type', $record_type);

		return $so;
	}
	/**
	 * Adding sales order to Netsuite
	 */
	public function addOrder( $order_data, $customer_internal_id ) {

		$order_sync_status = true;
		global $TMWNI_OPTIONS;

		/** 
			*Before order add hook.
		
			* @since 1.0.0
 
			**/
			do_action('before_add_netsuite_order', $order_data, $customer_internal_id);


		/** 
			*Order add status hook.
		
			* @since 1.0.0
 
			**/
			$order_sync_status  = apply_filters('tm_netsuite_order_sync_status', $order_sync_status, $order_data);
		if (false != $order_sync_status) {
			$this->netsuiteService->logRequests(true);

			$order = $order_data['order'];

			$this->object_id = $order_data['order_id'];

			$so = $this->OrderNSRequest($order_data, $customer_internal_id);

			/** 
				*Order add request data hook.
	
				* @since 1.0.0

				**/
			$so = apply_filters('tm_add_request_order_data', $so, $order_data['order_id']);     

			$request = new AddRequest();
			$request->record = $so;
			try {
				$addResponse = $this->netsuiteService->add($request);
				if (1 == $addResponse->writeResponse->status->isSuccess) {
					tm_ns_update_post_meta($order_data['order_id'], TMWNI_Settings::$ns_customer_id, $customer_internal_id);

					$order_internal_id = $addResponse->writeResponse->baseRef->internalId;
					//Customer Deposit code
					if ('salesorder' == $TMWNI_OPTIONS['ns_order_record_type'] && isset($TMWNI_OPTIONS['enableCustomerDeposit']) && !empty($TMWNI_OPTIONS['enableCustomerDeposit'])) {
						$this->createCustomerDeposite($order_data, $customer_internal_id, $order_internal_id, $so);
					}

					if ('salesorder' == $TMWNI_OPTIONS['ns_order_record_type'] && isset($TMWNI_OPTIONS['ns_auto_invoice_sync']) && !empty($TMWNI_OPTIONS['ns_auto_invoice_sync'])) {
						$order = wc_get_order($order_data['order_id']);
						if ( $order->has_status($TMWNI_OPTIONS['ns_auto_invoice_status']) ) {
							$this->createSOInvoice($order_data['order_id'], $customer_internal_id, $order_internal_id);
						}			
					}
					/** 
						*After Add order data response hook.
		
						* @since 1.0.0
 
						**/
					do_action('tm_netsuite_after_order_add', $order_data, $customer_internal_id, $order_internal_id);
				} elseif (isset($TMWNI_OPTIONS['ns_order_send_email_to_admin_enable']) && 'on' == $TMWNI_OPTIONS['ns_order_send_email_to_admin_enable'] && !empty($TMWNI_OPTIONS['ns_order_admin_email'])) {

					$this->sendEmailIfOrderFailed($addResponse, $order_data, $order_data['order_id'], $order_data['items']);
				}
				return $this->handleAPIAddResponse($addResponse, 'order-add');
			} catch (SoapFault $e) {
				$object = 'order-add';

				$error_msg = "SOAP API Error occured on '" . ucfirst($object) . " Add' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
				$error_msg .= 'Error Message: ' . $e->getMessage();

				$this->sendEmailIfOrderFailed($addResponse, $order_data, $order_data['order_id'], $order_data['items']);

				$this->handleLog(0, $this->object_id, $object, $error_msg);

				return 0;
			}
		}   
	}

	/**
	 * Update Order
	 *
	*/ 
	public function updateOrder( $order_data, $customer_internal_id, $order_internal_id ) {
		global $TMWNI_OPTIONS;
		$order_sync_status = true;
		/**
			* Before order update hook
		 *
			* @since 1.0.0
			*
		*/ 
			do_action('before_update_netsuite_order', $order_data, $customer_internal_id, $order_internal_id);
		/** 
			*Update order status hook.
		
			* @since 1.0.0
 
			**/
			$order_sync_status  = apply_filters('tm_netsuite_order_update_status', $order_sync_status, $order_data, $order_sync_status);

		if (false != $order_sync_status) {
			$this->netsuiteService->logRequests(true);

			$order = $order_data['order'];

			$this->object_id = $order_data['order_id'];

			$so = $this->OrderNSRequest($order_data, $customer_internal_id, $order_internal_id);

			$so->internalId = $order_internal_id;
			/** 
				*Update order data request hook.
		
				* @since 1.0.0
 
				**/
			$so = apply_filters('tm_update_request_order_data', $so, $order_data['order_id']);

			$request = new UpdateRequest();
			$request->record = $so;
			try {
				$updateResponse = $this->netsuiteService->update($request);
				// pr($updateResponse); die('zzzz');
				if (isset($updateResponse->writeResponse->status->isSuccess) && 1 == $updateResponse->writeResponse->status->isSuccess) {
					/** 
						*After Update order hook.
		
						* @since 1.0.0
 
						**/
					do_action('tm_netsuite_after_order_update', $order_data, $customer_internal_id, $order_internal_id, $so);
				}
				return $this->handleAPIAddResponse($updateResponse, 'order-update');
			} catch (SoapFault $e) {
				$object = 'order-update';
				$error_msg = "SOAP API Error occured on '" . ucfirst($object) . " Add' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
				$error_msg .= 'Error Message: ' . $e->getMessage();

				$this->handleLog(0, $this->object_id, $object, $error_msg);

				return 0;
			}
		}
	}
	public function createSOInvoice($order_id,$customer_internal_id,$order_internal_id) {
		$this->object_id = $order_id;

		$invoice = new Invoice();
			
		$invoice->createdFrom = new RecordRef();
		$invoice->createdFrom->internalId = $order_internal_id;
			
		$invoice->entity = new RecordRef();
		$invoice->entity->internalId = $customer_internal_id; // Replace with the customer internal ID

		// Submit the Invoice
		$request = new AddRequest();
		$request->record = $invoice;

		try {
			$addResponse = $this->netsuiteService->add($request);   
			$invoice_id =  $this->handleAPIAddResponse($addResponse, 'invoice');  
			tm_ns_update_post_meta($order_id, TMWNI_Settings::$ns_invoice_id, $invoice_id);
		} catch (SoapFault $e) {
			$object = 'invoice';
			$error_msg = "SOAP API Error occured on '" . ucfirst($object) . " Add' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
			$error_msg .= 'Error Message: ' . $e->getMessage();

			$this->handleLog(0, $this->object_id, $object, $error_msg);

			return 0;
		}
	} 

	public function getBillingAddress( $order_data, $so ) {
		if (isset($order_data['billing_address']['country']) && !empty($order_data['billing_address']['country'])) {
			$ns_billing_country = $order_data['billing_address']['country'];
			;
		} else {
			$ns_billing_country = '';
		}
		$so->billingAddress = new Address();
		$so->billingAddress->addr1 = $order_data['billing_address']['address_1'];
		$so->billingAddress->addr2 = $order_data['billing_address']['address_2'];
		$so->billingAddress->city = $order_data['billing_address']['city'];
		$so->billingAddress->state = $order_data['billing_address']['state'];
		$so->billingAddress->zip = $order_data['billing_address']['postcode'];
		$so->billingAddress->country = $ns_billing_country;
		$so->billingAddress->addrPhone = $order_data['billing_address']['phone'];
		if (!empty($order_data['billing_address']['company'])) {
			$so->billingAddress->attention = $order_data['billing_address']['first_name'] . ' ' . $order_data['billing_address']['last_name'];
			$so->billingAddress->addressee = $order_data['billing_address']['company'];

		} else {
			$so->billingAddress->attention = $order_data['billing_address']['first_name'] . ' ' . $order_data['billing_address']['last_name'];

		}


		return $so->billingAddress;
	}


	public function getShippingAddress( $order_data, $so ) {
		if (isset($order_data['shipping_address']['country']) && !empty($order_data['shipping_address']['country'])) {
			$ns_shipping_country = $order_data['shipping_address']['country'];
		} else {
			$ns_shipping_country = '';
		}
		$so->shippingAddress = new Address();
		$so->shippingAddress->addr1 = $order_data['shipping_address']['address_1'];
		$so->shippingAddress->addr2 = $order_data['shipping_address']['address_2'];
		$so->shippingAddress->city = $order_data['shipping_address']['city'];
		$so->shippingAddress->state = $order_data['shipping_address']['state'];
		$so->shippingAddress->zip = $order_data['shipping_address']['postcode'];
		$so->shippingAddress->addrPhone = $order_data['shipping_address']['phone'];
		$so->shippingAddress->country = $ns_shipping_country;
		if (!empty($order_data['shipping_address']['company'])) {
			$so->shippingAddress->attention = $order_data['shipping_address']['first_name'] . ' ' . $order_data['shipping_address']['last_name'];
			$so->shippingAddress->addressee = $order_data['shipping_address']['company'];

		} else {
			$so->shippingAddress->attention = $order_data['shipping_address']['first_name'] . ' ' . $order_data['shipping_address']['last_name'];

		}


		return $so->shippingAddress;
	}
	/**
	 * Setting sales order items
	 */
	private function _setOrderItems( $order_items, $shipping_cost ) {
		global $TMWNI_OPTIONS;
		$soi = array();
		if (!empty($order_items)) {
			foreach ($order_items as $key => $item) {

				if (isset($TMWNI_OPTIONS['ns_order_record_type']) && 'cashsale' == $TMWNI_OPTIONS['ns_order_record_type']) {
					$soi[$key] =  new CashSaleItem();
				} else {
					$soi[$key] = new SalesOrderItem();
				}
				$soi[$key]->item = new RecordRef();
				$soi[$key]->item->internalId = $item['internalId'];
				$soi[$key]->quantity = $item['qty'];
				// $soi[$key]->taxcode = NS_ITEM_TAX_CODE;

				if (isset($TMWNI_OPTIONS['order_item_location']) && !empty($TMWNI_OPTIONS['order_item_location'])) {
					if (3 == $TMWNI_OPTIONS['order_item_location'] && isset($TMWNI_OPTIONS['order_item_location_value']) && !empty($TMWNI_OPTIONS['order_item_location_value'])) {

						$soi[$key]->location = new RecordRef();
						$soi[$key]->location->internalId = $TMWNI_OPTIONS['order_item_location_value'];
					}

					if (2 == $TMWNI_OPTIONS['order_item_location'] && ( isset($item['locationId']) && !empty($item['locationId']) )) {
						 //get items location id 
						$item_location_id = $item['locationId'];
						$soi[$key]->location = new RecordRef();
						$soi[$key]->location->internalId = $item_location_id;
					}
				}

				if (isset($TMWNI_OPTIONS['order_item_price_level_name_enable']) && !empty($TMWNI_OPTIONS['order_item_price_level_name_enable']) ) {
					$price = new RecordRef();
					$price->internalId = $TMWNI_OPTIONS['order_item_price_level_name'];

					$soi[$key]->price = $price;
					$soi[$key]->amount = $item['subtotal'];
					// $soi[$key]->rate = $item['unit_price'];


				} else {
					$soi[$key]->amount = $item['subtotal'];
					$soi[$key]->price = $item['unit_price'];
					// $soi[$key]->rate = $item['unit_price'];


				}
				//For order line item tax codes
				if (isset($TMWNI_OPTIONS['order_item_tax_code_enable']) && !empty($TMWNI_OPTIONS['order_item_tax_code_enable']) ) {

					$taxcode = new RecordRef();
					$taxcode->internalId = $TMWNI_OPTIONS['order_item_tax_code'];

					$soi[$key]->taxCode = $taxcode;


				}   

			}

			$soi = array_reverse($soi);

			if (isset($TMWNI_OPTIONS['ns_order_shiping_line_item']) && !empty($TMWNI_OPTIONS['ns_order_shiping_line_item']) && isset($TMWNI_OPTIONS['ns_order_shiping_line_item_enable']) && !empty($TMWNI_OPTIONS['ns_order_shiping_line_item_enable']) ) {
				$key = ++$key;
				if (isset($TMWNI_OPTIONS['ns_order_record_type']) && 'cashsale' == $TMWNI_OPTIONS['ns_order_record_type']) {
					$soi[$key] =  new CashSaleItem();
				} else {
					$soi[$key] = new SalesOrderItem();
				}

				$soi[$key]->item = new RecordRef();
				$soi[$key]->item->internalId = $TMWNI_OPTIONS['ns_order_shiping_line_item'];
				$soi[$key]->quantity = 1;
				// $soi[$key]->taxcode = NS_SHIPPING_TAX_CODE;
				// $soi[$key]->price = $shipping_cost;
				
				if (isset($TMWNI_OPTIONS['order_item_price_level_name_enable']) && !empty($TMWNI_OPTIONS['order_item_price_level_name_enable']) ) {
					$price = new RecordRef();
					$price->internalId = $TMWNI_OPTIONS['order_item_price_level_name'];

					$soi[$key]->price = $price;
					$soi[$key]->amount = $shipping_cost;

				} else {
					$soi[$key]->amount = $shipping_cost;
				}
			}
		}


		return $soi;
	}

	public function getPromoData( $value, $discount_internal_id, $promoInternalID ) {

		$coupon_post_obj = get_page_by_title($value, OBJECT, 'shop_coupon');

		$coupon_meta = get_metadata( 'post', $coupon_post_obj->ID);

		if ('fixed_cart' == $coupon_meta['discount_type'][0] || 'fixed_product'  == $coupon_meta['discount_type'][0] ) {
			$discountRate = $coupon_meta['coupon_amount'][0];
		} elseif ('percent' == $coupon_meta['discount_type'][0]) {
			$discountRate = $coupon_meta['coupon_amount'][0] . '%';
		}

			//Set Coupon code Use Type 
		$couponUseType = new PromotionCodeUseType();

		if ($coupon_meta['limit_usage_to_x_items'][0] > 0) {
			$usetype = $couponUseType::_MULTIPLEUSES;
		} else {
			$usetype = $couponUseType::_SINGLEUSE;
		}

		$coupan_expiry = '';

		if (!empty($coupon_meta['date_expires'][0])) {
			$coupan_expiry = gmdate(DATE_ATOM, $coupon_meta['date_expires'][0]);
		}

		$coupon_data = new PromotionCode();

		if (!empty($promoInternalID)) {
			$coupon_data->internalId = $promoInternalID;
		}

		$discRef = new RecordRef();

		$discRef->internalId = $discount_internal_id;

		$customForm = new RecordRef();

		if (isset($TMWNI_OPTIONS['ns_promo_custform_id']) && !empty($TMWNI_OPTIONS['ns_promo_custform_id']) ) {
			$customForm->internalId = $TMWNI_OPTIONS['ns_promo_custform_id'];
		}

		$fieldsArray = array(
			'customForm' => $customForm,
			'name' => $coupon_post_obj->post_name, 
			'code' => $coupon_post_obj->post_title, 
						//"discount" => $this->createRecordRef($discount_internal_id),   //Uncomment if discount internal id is availble (Use 1701 for testing)
			'discount' => $discRef,
						'rate' => $discountRate,                                               //Uncomment if "discount" field is active
						//"useType" => $usetype,                                               //INSUFFICIENT_PERMISSION or Readonly field
						'description' => $coupon_post_obj->post_excerpt,
						'startDate' => gmdate(DATE_ATOM, strtotime($coupon_post_obj->post_date)),
						'endDate' => $coupan_expiry,
					);

		setFields($coupon_data, $fieldsArray);


		return $coupon_data;
	}


	/**
	 * Add order promo to NetSuite
	 */
	public function _addNSpromo( $value, $discount_internal_id = 0 ) {

		global $TMWNI_OPTIONS;

		$promoInternalID = $this->searchPromoCode($value);


		if (0 == $promoInternalID) {

			$add_coupon = $this->getPromoData($value, $discount_internal_id, $promoInternalID);
			try {

				$netsuitePromo = new AddRequest();
				$netsuitePromo->record = $add_coupon;
				$writeResponse = $this->netsuiteService->add($netsuitePromo);
				$this->handleAPIAddResponse($writeResponse, 'coupon');

				if (isset($writeResponse->writeResponse->status->isSuccess) && 1 == $writeResponse->writeResponse->status->isSuccess && isset($writeResponse->writeResponse->baseRef->internalId) && !empty($writeResponse->writeResponse->baseRef->internalId) ) {
					return $writeResponse->writeResponse->baseRef->internalId;
				} else {
					return 0;
				}
			} catch (SoapFault $exc) {
				$object = 'coupon';
				$error_msg = "SOAP API Error occured on '" . ucfirst($object) . " Add' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
				$error_msg .= 'Error Message: ' . $exc->getMessage();

				$this->handleLog(0, $this->object_id, $object, $error_msg);

				return 0;
			}
		} else {
			$update_coupon = $this->getPromoData($value, $discount_internal_id, $promoInternalID);
			try {

				$netsuitePromo = new UpdateRequest();
				$netsuitePromo->record = $update_coupon;
				$writeResponse = $this->netsuiteService->update($netsuitePromo);
				$this->handleAPIAddResponse($writeResponse, 'coupon_update');
			} catch (SoapFault $exc) {
				$object = 'coupon update';
				$error_msg = "SOAP API Error occured on '" . ucfirst($object) . " update' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
				$error_msg .= 'Error Message: ' . $exc->getMessage();

				$this->handleLog(0, $this->object_id, $object, $error_msg);

			}

			return $promoInternalID;
		}
	}


	/**
	 * Search order promo on NetSuite
	 */
	public function searchPromoCode( $PromotionCode ) {


		$this->netsuiteService->setSearchPreferences(false, 20);

		$SearchCoupon = new SearchStringField();

		$SearchCoupon->operator = 'is';

		$SearchCoupon->searchValue = $PromotionCode;


		$search = new PromotionCodeSearchBasic();

		$search->code = $SearchCoupon;


		$request = new SearchRequest();
		$request->searchRecord = $search;

		try {
			$searchResponse = $this->netsuiteService->search($request);
			$this->handleAPISearchResponse($searchResponse, 'coupon', $PromotionCode);
			if (isset($searchResponse->searchResult->status->isSuccess) && 1 == $searchResponse->searchResult->status->isSuccess && isset($searchResponse->searchResult->recordList->record[0]->internalId) && !empty($searchResponse->searchResult->recordList->record[0]->internalId)) {
				return $searchResponse->searchResult->recordList->record[0]->internalId;
			} else {
				return 0;
			}
		} catch (SoapFault $exc) {
			$object = 'coupon';
			$error_msg = "SOAP API Error occured on '" . ucfirst($object) . " Add' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
			$error_msg .= 'Error Message: ' . $exc->getMessage();

			$this->handleLog(0, $this->object_id, $object, $error_msg);

			return 0;

		}
	}
	/**
	 * Adding customer deposite on NetSuite
	 */
	public function createCustomerDeposite( $order_data, $customer_internal_id, $order_internal_id ) {
		global $TMWNI_OPTIONS;
		$customer_deposit_sync = true;
		$customer_deposit_sync = apply_filters('tm_ns_customer_deposit_sync_status_check', $customer_deposit_sync, $order_data, $customer_internal_id, $order_internal_id);

		if (true == $customer_deposit_sync) {
			$order_id = $order_data['order_id'];
			$order = wc_get_order($order_id);

			$this->object_id = 'CustomerDeposite';

			$customer_deposite = new CustomerDeposit();

			$customer_deposite->customer = new RecordRef();
			$customer_deposite->customer->internalId = $customer_internal_id;

			$customer_deposite->salesOrder = new RecordRef();
			$customer_deposite->salesOrder->internalId = $order_internal_id;

			$customer_deposite->payment = $order->total;

			$customer_deposite = apply_filters('tm_customer_deposit_data', $customer_deposite, $order_data, $customer_internal_id, $order_internal_id);

			$request = new AddRequest();
			$request->record = $customer_deposite;

			try {
				$addResponse = $this->netsuiteService->add($request);
				return $this->handleAPIAddResponse($addResponse, 'customer deposite');
			} catch (SoapFault $e) {
				$object = 'customer deposite';
				$error_msg = "SOAP API Error occured on '" . ucfirst($object) . " Add' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
				$error_msg .= 'Error Message: ' . $e->getMessage();

				$this->handleLog(0, $this->object_id, $object, $error_msg);

				return 0;
			}

		}
		
	} 


	/**
	 * Delete Order
	 *
	*/ 
	public function deleteOrder( $nsOrderInternalId, $order_id ) {
		$this->object_id = $order_id;

		$record_type = tm_ns_get_post_meta($order_id, 'ns_record_type', true);
		if ('cashsale.nl' == $record_type) {
			$type = 'cashSale';
		} else {
			$type = 'salesOrder';
		}

		$nsSalesOrder = new RecordRef();
		$nsSalesOrder->type = $type;
		$nsSalesOrder->internalId = $nsOrderInternalId;

		$deleteOrder = new DeleteRequest();
		$deleteOrder->baseRef = $nsSalesOrder;

		try {
			$delResponse = $this->netsuiteService->delete($deleteOrder);
			$this->handleAPIAddResponse($delResponse, 'order Delete');
			return;
		} catch (SoapFault $e) {
			$object = 'order';
			$error_msg = "SOAP API Error occured on '" . ucfirst($object) . "Delete' operation failed for WooCommerce " . $object . ', ID = ' . $this->object_id . '. ';
			$error_msg .= 'Error Message: ' . $e->getMessage();
			$this->handleLog(0, $this->object_id, $object, $error_msg);
			return 0;
		}     
	}

	/**
	 * Create mapping Request
	 *
	*/ 
	public function createRequest( &$so, $order_data ) {
		foreach ($order_data['ns_salesorder_fields'] as $nsfield => $value) {

			if (isset($value['type']) && ( 'string' == $value['type'] || 'float' == $value['type'] || 'integer' == $value['type'] )) {
				if ('phone'==$nsfield && strlen($value['value']) > 6) {
					$so->$nsfield = $value['value'];
				} else {
					$so->$nsfield = $value['value'];

				}

			} elseif ( isset($value['type']) && 'dateTime' == $value['type']) {

				$so->$nsfield = gmdate(DATE_ATOM, strtotime($value['value']));

			} elseif ( isset($value['type']) && 'boolean' == $value['type']) {

				$so->$nsfield = $value['value'];

			} elseif ( isset($value['type']) && 'RecordRef' == $value['type']) {

				$so->$nsfield = new RecordRef();

				$so->$nsfield->internalId = $value['value'];

			} elseif ( isset($value['type']) && 'customboolean' == $value['type']) {

				$so->customFieldList = $this->customFieldboolean($nsfield, $value['value']);

			} elseif ( isset($value['type']) && 'customstringfield' == $value['type']) {

				$so->customFieldList = $this->customField($nsfield, $value['value']);

			} elseif ( isset($value['type']) && 'customselectfield' == $value['type']) {

				$so->customFieldList = $this->customFieldSelect($nsfield, $value['value']);

			} elseif ( isset($value['type']) && 'custommultiselectfield' == $value['type']) {

				$multiselectvalues = explode(',', $value['value']);

				$so->customFieldList = $this->customFieldMultiSelect($nsfield, $multiselectvalues);

			} elseif ( isset($value['type']) && 'customrecordref' == $value['type']) {

				$so->customFieldList = $this->customRecordRefField($nsfield, $value);

			} elseif ( isset($value['type']) && 'customcurrdatefield' == $value['type']) {

				$so->customFieldList = $this->customField($nsfield, gmdate('d/m/Y H:i:s'));

			} elseif ( isset($value['type']) &&  'customdateTime' == $value['type']) {

				$so->customFieldList = $this->customField($nsfield, $value['value']);

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
		return $this->customFieldList($custfield);
	}

	/**
	 * Creating custom string field instance.
	 */
	public function customRecordRefField( $scriptId, $value ) {
		$custfield = new StringCustomFieldRef();
		$custfield->scriptId = $scriptId;
		$custfield->value = new RecordRef();
		$custfield->value->internalId = $value;
		return $this->customFieldList($custfield);
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
		return $this->customFieldList($custfieldselect);
	}


	/**
	 * Creating custom multiselect field instance.
	 */
	public function customFieldMultiSelect( $scriptId, array $value ) {
		$multivalues = array();
		$custfieldmultiselect = new MultiSelectCustomFieldRef();
		$custfieldmultiselect->scriptId = $scriptId;
		foreach ($value as $key => $item) {
			$recref = new ListOrRecordRef();
			$recref->name = $item;
			$multivalues[] = $recref;
		}
		$custfieldmultiselect->value = $multivalues;
		return $this->customFieldList($custfieldmultiselect);
	}


	/**
	 * Creating custom boolean field instance.
	 */
	public function customFieldboolean( $scriptId, $value ) {
		$custfieldselect = new BooleanCustomFieldRef();
		$custfieldselect->scriptId = $scriptId;
		$custfieldselect->value = $value;
		return $this->customFieldList($custfieldselect);
	}


	/**
	 * Creating custom field list array.
	 */
	public function customSearchFieldList( $custfield ) {
		$customFieldList = new SearchCustomFieldList();
		$customFieldList->customField = array( $custfield );
		return $customFieldList;
	}


	/**
	 * Creating custom string field instance.
	 */
	public function customSearchField( $scriptId, $value ) {
		$custfield = new SearchStringCustomField();
		$custfield->scriptId = $scriptId;
		$custfield->searchValue = $value;
		$custfield->operator = 'is';
		return $this->customSearchFieldList($custfield);
	}

	/**
	 * Creating conditional mapping for orders.
	 */
	public function salesOrderConditionalMapping( &$order_data, $order, $order_internal_id = 0 ) {
		$order_data['ns_salesorder_fields']=array();
		if (!empty($order->get_user_id())) {
			$this->user_id = $order->get_user_id();
		}
		$cm_options = get_option('order_cm_options');
		if (!empty($cm_options)) {
			foreach ($cm_options as $key => $mapping) {
				if (( !empty($order_internal_id) && isset($mapping['exlcude_in_update']) && 'on' == $mapping['exlcude_in_update'] ) ) {
					continue;
				}
				$saved_value = '';
				switch ($mapping['operator']) {
					case 1:
						if ('' != $mapping['wc_field_key'] && '' != $mapping['wc_field_value'] && '' != $mapping ['ns_field_key'] && '' != $mapping['ns_field_value']) {
							if (1 == $mapping['type']) {
								if ('user_id' == $mapping['wc_field_key']) {
									$saved_value = $this->user_id;
								} elseif ('email' == $mapping['wc_field_key']) {
									$saved_value = $order_data['customer_email'];
								} else {
									//woo default  ////
									$saved_value = get_user_meta($this->user_id, ( false === strstr($mapping['wc_field_key'], 'shipping_') ? 'billing_' : '' ) . $mapping ['wc_field_key'], true);
								}
							
							} else if (2 == $mapping['type']) {
								$saved_value = get_user_meta($this->user_id, $mapping['wc_field_key'], true);
							} else if (3 == $mapping['type']) {
								if (!empty($order)) {
									$saved_value = tm_ns_get_order_data($order, $mapping);

								}
							} else if (4 == $mapping ['type']) {
								if (!empty($order)) {
									$saved_value = tm_ns_get_post_meta($order->get_id(), $mapping['wc_field_key']);
								}
							}
						

							if ('contains'  == $mapping['wc_where_op'] ) {
								if (false !== strpos(html_entity_decode(mb_strtolower($saved_value)), html_entity_decode(mb_strtolower($mapping['wc_field_value'])))) {
									$order_data['ns_salesorder_fields'][trim($mapping['ns_field_key'])] = array( 'type'=>trim($mapping['ns_field_type_value']), 'value'=>$mapping['ns_field_value'] );
								}
							} elseif ('doesnotcontain' == $mapping['wc_where_op']) {
								if (false === strpos(html_entity_decode(strtolower($saved_value)), html_entity_decode(strtolower($mapping['wc_field_value'])))) {
									$order_data['ns_salesorder_fields'][trim($mapping['ns_field_key'])] = array( 'type'=>trim($mapping['ns_field_type_value']), 'value'=>$mapping['ns_field_value'] );
								}
							} elseif ('is' == $mapping['wc_where_op']) {
								if ('null' == strtolower($mapping['wc_field_value'])) {
									if (empty($saved_value)) {
										$order_data['ns_salesorder_fields'][trim($mapping['ns_field_key'])] = array( 'type'=>trim($mapping['ns_field_type_value']), 'value'=>$mapping['ns_field_value'] );
									}
								} elseif (html_entity_decode(strtolower($mapping['wc_field_value'])) == html_entity_decode(strtolower($saved_value))) {
									$order_data['ns_salesorder_fields'][trim($mapping['ns_field_key'])] = array(
										'type' => trim($mapping['ns_field_type_value']),
										'value' => $mapping['ns_field_value']
									);
								}
							} elseif ('isnot' == $mapping['wc_where_op']) {
								if ('null' == strtolower($mapping['wc_field_value'])) {
									if (!empty($saved_value)) {
										$order_data['ns_salesorder_fields'][trim($mapping['ns_field_key'])] = array( 'type'=>trim($mapping['ns_field_type_value']), 'value'=>$mapping['ns_field_value'] );
									}
								} elseif ( html_entity_decode(strtolower($saved_value)) != html_entity_decode(strtolower($mapping['wc_field_value']))) {
									$order_data['ns_salesorder_fields'][trim($mapping['ns_field_key'])] = array( 'type'=>trim($mapping['ns_field_type_value']), 'value'=>$mapping['ns_field_value'] );
								}
							}
						}
						break;
					case 2:
						if ('' != $mapping['ns_field_key'] && '' != $mapping['ns_field_value']) {
							$order_data['ns_salesorder_fields'][trim($mapping['ns_field_key'])] = array( 'type'=>trim($mapping['ns_field_type_value']), 'value'=>$mapping['ns_field_value'] );
						}
						break;
					case 3:
						if ('' != $mapping['wc_field_key'] && '' != $mapping['ns_field_key']) {
							$prefix = isset($mapping['wc_field_value_prefix']) ? $mapping['wc_field_value_prefix'] : '';
							if (1 == $mapping['type']) {
								if ('user_id' == $mapping['wc_field_key']) {
									$saved_value = $this->user_id;
								} elseif ('email' == $mapping['wc_field_key']) {
									$saved_value = $order_data['customer_email'];
								} else {
									//woo default
									$saved_value = get_user_meta($this->user_id, ( false === strstr($mapping['wc_field_key'], 'shipping_') ? 'billing_' : '' ) . $mapping ['wc_field_key'], true);
								}
							} else if (2 == $mapping['type']) {
								$saved_value = get_user_meta($this->user_id, $mapping['wc_field_key'], true);
							} else if (3 == $mapping['type']) {
								if (!empty($order)) {
									$saved_value = tm_ns_get_order_data($order, $mapping);
								}
							} else if (4 == $mapping ['type']) {
								if (!empty($order)) {
									$saved_value = tm_ns_get_post_meta($order->get_id(), $mapping['wc_field_key']);
								}
							}

							if (isset($saved_value) && !empty($saved_value)) {
								$order_data['ns_salesorder_fields'][trim($mapping['ns_field_key'])] = array( 'type'=>trim($mapping['ns_field_type_value']), 'value'=>$prefix . $saved_value );
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


	public function sendEmailIfOrderFailed( $addResponse, $order_data, $order_id, $order_items ) {
		global $TMWNI_OPTIONS;
		$admin_email = $TMWNI_OPTIONS['ns_order_admin_email'];
		$subject = 'Order Failed on NetSuite';
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$Order = $order_data['order'];

		$error_msg = $addResponse->writeResponse->status->statusDetail[0]->message;


		$message = '<div class="container-fluid"><p style="font-size: 12px;text-transform:uppercase;">HEY ' .
		$order_data['billing_address']['first_name'] . '</p><h4>Order Failed : ' . $order_id . '</h4><p>Your Order' .
		$order_id . ' Add NetSuite operation  failed Due to reason ' . $error_msg . '</p>
		<table>
		<tr>
		<th>Billing Address</th>
		<th>Shipping Address</th>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['first_name'] . '</td>
		<td>' . $order_data['shipping_address']['first_name'] . '</td>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['last_name'] . '</td>
		<td>' . $order_data['shipping_address']['last_name'] . '</td>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['address_1'] . '</td>
		<td>' . $order_data['shipping_address']['address_1'] . '</td>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['city'] . '</td>
		<td>' . $order_data['shipping_address']['city'] . '</td>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['state'] . '</td>
		<td>' . $order_data['shipping_address']['state'] . '</td>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['postcode'] . '</td>
		<td>' . $order_data['shipping_address']['postcode'] . '</td>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['country'] . '</td>
		<td>' . $order_data['shipping_address']['country'] . '</td>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['email'] . '</td>
		</tr>
		<tr>
		<td>' . $order_data['billing_address']['phone'] . '</td>
		</tr>
		</table>	
		</div>
		<div>
		<h4>
		Item Information</h4>
		<table>
		<tr>
		<th>Product Id</th>
		<th>Quantity</th>
		<th>Price</th>
		</tr>';
		foreach ($order_items as $key => $item) {
			$price = $item['subtotal'] / $item['qty'];
			$message .= '<tr>
			<td>' . $item['productId'] . '</td>
			<td>' . $item['qty'] . '</td>
			<td>' . $price . '</td>			
			</tr>';
		}

		$message .= '</table>';


		wp_mail($admin_email, $subject, $message, $headers);
		/** 
			*Order failed  send hook.
		
			* @since 1.0.0
 
			**/
			do_action('tm_woocommerce_netsuite_order_failed', $addResponse, $order_data, $order_id, $order_items);
	}
}
