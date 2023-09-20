<?php

/**
 * Order statuses for CyberSource Online Gateway.
 *
 * @package Abzer
 */
defined('ABSPATH') || exit;

return array(
	array(
		'status' => 'wc-cs-pending',
		'label' => 'CyberSource Pending',
	),
	array(
		'status' => 'wc-cs-complete',
		'label' => 'CyberSource Complete',
	),
	array(
		'status' => 'wc-cs-error',
		'label' => 'CyberSource Error',
	),
	array(
		'status' => 'wc-cs-reject',
		'label' => 'CyberSource Reject',
	),
	array(
		'status' => 'wc-cs-review',
		'label' => 'CyberSource Review',
	),
	array(
		'status' => 'wc-cs-failed',
		'label' => 'CyberSource Failed',
	),
	array(
		'status' => 'wc-cs-declined',
		'label' => 'CyberSource Declined',
	),
);
