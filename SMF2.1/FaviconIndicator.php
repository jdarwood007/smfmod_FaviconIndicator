<?php

/**
 * The main class for Favicon Indicator customization
 * @package AntiSpamLinks
 * @author SleePy https://sleepycode.com
 * @copyright 2022
 * @license 3-Clause BSD https://opensource.org/licenses/BSD-3-Clause
 * @version 2.0
 */
class FaviconIndicator
{
	/**
	 * Creates the hook to the class for the xml sub actions.
	 *
	 * @param array $subActions A associate array of all the valid sub actions.
	 *
	 * @version 2.0
	 * @since 1.0
	 * @uses integrate_XMLhttpMain_subActions - Hook SMF2.1
	 * @return void No return is generated
	 */
	public static function hook_XMLhttpMain_subActions(array &$subActions): void
	{
		$subActions['faviconnotify'] = 'FaviconIndicator::CheckNotifications';
	}

	/**
	 * Prepares some XML data to send backt o the client.  This contains the unread pm and alerts counts.
	 *
	 * @version 2.0
	 * @since 1.0
	 * @return void No return is generated, it is sent to output
	 */
	public static function CheckNotifications(): void
	{
		global $user_info, $context;

		header('Content-Type: text/xml; charset=UTF-8');

		echo '<', '?xml version="1.0" encoding="UTF-8"?', '>
	<smf>
		<unreadpms>', $user_info['unread_messages'], '</unreadpms>
		<alerts>', $user_info['alerts'], '</alerts>
	</smf>';

		obExit(false, false, false);
	}

	/**
	 * Adds the javascript needed to make things work.
	 *
	 * @version 2.0
	 * @since 1.0
	 * @uses integrate_load_theme - Hook SMF2.1
	 * @return void No return is generated
	 */
	public static function hook_load_theme(): void
	{
		global $context, $settings, $modSettings;

		// Default the setting to 1 minute.
		if (!isset($modSettings['faviconIndicatorTimeout']))
			$modSettings['faviconIndicatorTimeout'] = 60;
		elseif (empty($modSettings['faviconIndicatorTimeout']) || $context['user']['is_guest'])
			return;

		// Append the html headers.
		loadJavaScriptFile('tinycon.min.js', [
			'defer' => true,
		]);

		addInlineJavaScript('
	fFaviconNotification = function ()
	{
		window.setTimeout(\'fFaviconNotification();\', ' . ((int) $modSettings['faviconIndicatorTimeout']) * 600 . ');

		getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + \'action=xmlhttp;sa=faviconnotify\', function(responseXML){
			var unreadPMs = responseXML.getElementsByTagName(\'smf\')[0].getElementsByTagName(\'unreadpms\')[0].firstChild.nodeValue;
			var alerts = responseXML.getElementsByTagName(\'smf\')[0].getElementsByTagName(\'alerts\')[0].firstChild.nodeValue;
			Tinycon.setBubble(parseInt(unreadPMs) + parseInt(alerts));
		});
	}
	addLoadEvent(fFaviconNotification);', true);
	}

	/**
	 * Creates the hook to the class for the general mod settings.
	 *
	 * @param array $config_vars A associate array of all the valid configuration variables.
	 *
	 * @version 2.0
	 * @since 1.0
	 * @uses integrate_general_mod_settings - Hook SMF2.1
	 * @return void No return is generated
	 */
	public static function hook_general_mod_settings(array &$config_vars): void
	{
		global $txt;

		loadLanguage('FaviconIndicator');
		$config_vars[] = array('int', 'faviconIndicatorTimeout', 'postinput' => $txt['faviconIndicatorTimeout_post']);
	}
}