<?php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $modSettings;

if (isset($modSettings['unreadPMstimeout']) && !isset($modSettings['faviconIndicatorTimeout']))
	updateSettings(array(
		'faviconIndicatorTimeout' => (int) $modSettings['unreadPMstimeout'],
		'unreadPMstimeout' => null
	), true);