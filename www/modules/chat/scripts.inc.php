<?php


/**
 * Comment here for explanation of the options.
 *
 * Create a new XMPP Object with the required params
 *
 * @param string $jabberHost Jabber Server Host
 * @param string $boshUri    Full URI to the http-bind
 * @param string $resource   Resource identifier
 * @param bool   $useSsl     Use SSL (not working yet, TODO)
 * @param bool   $debug      Enable debug
 */


$GO_SCRIPTS_JS .= '
	
	var converseJs = Ext.DomHelper.append(Ext.getBody(),
		{
			tag: "div",
			id: "conversejs"
		},true);
				

	converse.initialize({
	allow_logout: false,
				allow_otr: true,
				bosh_service_url: "' . \GO\Chat\ChatModule::getBoshUri() . '", // Please use this connection manager only for testing purposes
//				debug: true ,
				i18n: locales["'.GO::language()->getLanguage().'"], // Refer to ./locale/locales.js to see which locales are supported
				authentication: "prebind",
				prebind_url: "'.GO::url('chat/prebind/get').'",
				xhr_user_search: false,
				"keepalive": true,
				jid:"'.\GO\Base\Util\StringHelper::escape_javascript(\GO::user()->username).'",
				fullname: "'.\GO\Base\Util\StringHelper::escape_javascript(GO::user()->name).'"
		});

		var name = converseJs.select("input.new-chatroom-name");
		name.value="conference.' . \GO\Chat\ChatModule::getXmppHost() . '";
//			
//		var nick = converseJs.select("input.new-chatroom-nick");
//		nick.value="'.\GO\Base\Util\StringHelper::escape_javascript(GO::user()->name).'";



';
