<?php
/**
 * This file is part of the netsuitephp/netsuite-php library
 * AND originally from the NetSuite PHP Toolkit.
 *
 * New content:
 *
 * Package    ryanwinchester/netsuite-php
 * Copyright  Copyright (c) Ryan Winchester
 * License    http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * Link       https://github.com/netsuitephp/netsuite-php
 *
 * Original content:
 * Copyright  Copyright (c) NetSuite Inc.
 * License    https://raw.githubusercontent.com/netsuitephp/netsuite-php/master/original/NetSuite%20Application%20Developer%20License%20Agreement.txt
 * Link       http://www.netsuite.com/portal/developers/resources/suitetalk-sample-applications.shtml
 */

namespace NetSuite\Classes;

class SalesOrderOrderStatus {
	public static $paramtypesmap = array();
	const _PENDINGAPPROVAL = '_pendingApproval';
	const _PENDINGFULFILLMENT = '_pendingFulfillment';
	const _CANCELLED = '_cancelled';
	const _PARTIALLYFULFILLED = '_partiallyFulfilled';
	const _PENDINGBILLINGPARTFULFILLED = '_pendingBillingPartFulfilled';
	const _PENDINGBILLING = '_pendingBilling';
	const _FULLYBILLED = '_fullyBilled';
	const _CLOSED = '_closed';
	const _UNDEFINED = '_undefined';
}
