<?php

// This adds a hook to our action=.
function uPMs_hook_actionArray($actionArray)
{
	global $modSettings;

	// We will save the original action for use later.
	$modSettings['action_override_xmlhttp_uPMs'] = $actionArray['xmlhttp'];

	// We will overload the xmlhttp action for our needs.
	$actionArray['xmlhttp'] = array('UnreadPMs.php', 'UPMs_override_action_xmlhttp');
}

// Adds a hook to the end of loadTheme();
function uPMs_hook_load_theme()
{
	global $context, $settings, $modSettings;

	// Default the setting to 1 minute.
	if (!isset($modSettings['unreadPMstimeout']))
		$modSettings['unreadPMstimeout'] = '60';
	elseif (empty($modSettings['unreadPMstimeout']) || $context['user']['is_guest'])
		return;

	// Append the html headers.
	$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/tinycon.min.js?fnl1"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var funreadPMs = function ()
		{
			window.setTimeout(\'funreadPMs();\', ' . $modSettings['unreadPMstimeout'] * 600 . ');

			var oNewPms = getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + \'action=xmlhttp;sa=unreadpms\');

			if (oNewPms.responseXML)
			{
				var newCount = oNewPms.responseXML.getElementsByTagName(\'smf\')[0].getElementsByTagName(\'unreadpms\')[0].firstChild.nodeValue;
				Tinycon.setBubble(newCount);
			}
		}
		addLoadEvent(funreadPMs);
	// ]]></script>';
}


// Adds a hook to the general mod settings so we can manage the setting.
function uPMs_hook_general_mod_settings($config_vars)
{
	global $txt;

	$config_vars[] = array('int', 'unreadPMstimeout', 'postinput' => $txt['unreadPMstimeout_post']);
}

// Overrides the xmlhttp function.
function UPMs_override_action_xmlhttp()
{
	global $modSettings, $sourcedir;

	if (isset($_GET['sa']) && $_GET['sa'] == 'unreadpms')
		UnreadPMsXML();

	// Otherwise we will act just like index.php would have.
	require_once($sourcedir . '/' . $modSettings['action_override_xmlhttp_uPMs'][0]);
	return $modSettings['action_override_xmlhttp_uPMs'][1]();
}

// Provides the XML for our javascript to read.
function UnreadPMsXML()
{
	global $user_info, $context;

	header('Content-Type: text/xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	<unreadpms>', $user_info['unread_messages'], '</unreadpms>
</smf>';

	obExit(false, false, false);
}
