<?php

require_once TMWNI_DIR . 'inc/NS_Toolkit/src/NetSuiteService.php';
require_once TMWNI_DIR . 'inc/common.php';
require_once TMWNI_DIR . 'inc/helper.php';
require_once TMWNI_DIR . 'inc/NS_Toolkit/src/includes/functions.php';

function tmNetSuiteAutoLoader( $className ) {
	$classNameParts = explode( '\\', $className );
	$className = end( $classNameParts );

	// Define the base directory for the class files
	$baseDirectory = TMWNI_DIR . 'inc/NS_Toolkit/src/Classes/';

	// Construct the file path
	$filePath = $baseDirectory . $className . '.php';

	// Normalize paths to use forward slashes for compatibility
	$normalizedBaseDirectory = str_replace( '\\', '/', $baseDirectory );
	$normalizedFilePath = str_replace( '\\', '/', realpath( $filePath ) );

	// Check if the file exists within the plugin directory
	if ( $normalizedFilePath && strpos( $normalizedFilePath, $normalizedBaseDirectory ) === 0 && is_file( $normalizedFilePath ) ) {
		require_once $normalizedFilePath;
	}
}

// Register the autoload function
spl_autoload_register( 'tmNetSuiteAutoLoader' );
