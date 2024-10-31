<?php

require_once TMWNI_DIR . 'inc/NS_Toolkit/src/NetSuiteService.php';
require_once TMWNI_DIR . 'inc/common.php';
require_once TMWNI_DIR . 'inc/helper.php';
require_once TMWNI_DIR . 'inc/NS_Toolkit/src/includes/functions.php';

function tmNetSuiteAutoLoader($className) {
	$classNameParts = explode('\\', $className);
	$className = end($classNameParts);
	
	$baseDirectory = TMWNI_DIR . 'inc/NS_Toolkit/src/Classes/';
	
	$filePath = $baseDirectory . $className . '.php';
	
	$realFilePath = realpath($filePath);
	if ($realFilePath && strpos($realFilePath, $baseDirectory) === 0 && is_file($realFilePath) && file_exists($realFilePath)) {
		require_once $realFilePath;
	} 
}

// Register the autoload function
spl_autoload_register('tmNetSuiteAutoLoader');
