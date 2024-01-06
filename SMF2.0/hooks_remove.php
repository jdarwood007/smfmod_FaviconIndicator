<?php

error_reporting(E_ALL);

// Hopefully we have the goodies.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')) {
	$using_ssi = true;

	require_once dirname(__FILE__) . '/SSI.php';
} elseif (!defined('SMF')) {
	exit('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s index.php.');
}

remove_integration_function('integrate_pre_include', '$sourcedir/UnreadPMs.php');
remove_integration_function('integrate_actions', 'uPMs_hook_actionArray');
remove_integration_function('integrate_load_theme', 'uPMs_hook_load_theme');
remove_integration_function('integrate_general_mod_settings', 'uPMs_hook_general_mod_settings');

if(!empty($using_ssi)) {
	echo 'If no errors, Success!';
}
