<?php
/**
 * The main class for Favicon Indicator customization
 * @package AntiSpamLinks
 * @author SleePy https://sleepycode.com
 * @copyright 2024
 * @license 3-Clause BSD https://opensource.org/licenses/BSD-3-Clause
 * @version 2.0
 */

#namespace SMF\Mod\ErrorPopup;

use SMF\Config;
use SMF\Lang;
use SMF\Theme;
use SMF\User;
use SMF\Utils;

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
	 */
	public static function CheckNotifications(): void
	{
		header('Content-Type: text/xml; charset=UTF-8');

		echo '<', '?xml version="1.0" encoding="UTF-8"?', '>
	<smf>
		<unreadpms>', User::$me->unread_messages, '</unreadpms>
		<alerts>', User::$me->alerts, '</alerts>
	</smf>';

		Utils::obExit(false, false, false);
	}

	/**
	 * Adds the javascript needed to make things work.
	 *
	 * @version 2.0
	 * @since 1.0
	 * @uses integrate_load_theme - Hook SMF2.1
	 */
	public static function hook_load_theme(): void
	{
		// Default the setting to 1 minute.
		if (!isset(Config::$modSettings['faviconIndicatorTimeout'])) {
			Config::$modSettings['faviconIndicatorTimeout'] = 60;
		} elseif (empty(Config::$modSettings['faviconIndicatorTimeout']) || User::$me->is_guest) {
			return;
		}

		// Append the html headers.
		Theme::loadJavaScriptFile('tinycon.min.js', [
			'defer' => true,
		]);

		Theme::addInlineJavaScript('
	fFaviconNotification = function ()
	{
		window.setTimeout(\'fFaviconNotification();\', ' . ((int) Config::$modSettings['faviconIndicatorTimeout']) * 600 . ');

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
	 */
	public static function hook_general_mod_settings(array &$config_vars): void
	{
		Lang::load('FaviconIndicator');
		$config_vars[] = ['int', 'faviconIndicatorTimeout', 'postinput' => Lang::$txt['faviconIndicatorTimeout_post']];
	}
}
