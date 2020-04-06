/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: common.js 22456 2018-03-06 15:42:05Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
Ext.namespace('GO.util');

Ext.override(Ext.data.Connection, {
	timeout: 120000
});

Ext.Ajax.on('requestexception', function(conn, response, options) {
	if(response.isAbort) {
		console.warn("Connection aborted", conn, response, options);
	} else if(response.isTimeout) {
		Ext.MessageBox.alert(t("Request error"), t("The connection to the server timed out. Please check your internet connection."))
	} else
	{
		console.warn("Request exception", conn, response, options);
	}
});

GO.permissionLevels = go.permissionLevels = {
		read: 10,
		create: 20,
		write: 30,
		writeAndDelete: 40,
		manage: 50
	};
	
	
GO.util.isMobileOrTablet = function() {
	var check = false;
	(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
	return check;
};

GO.util.isTabletScreenSize = function() {
	return window.innerWidth < 1200;
}

GO.util.isMobileScreenSize = function() {
	return window.innerWidth < 1000;
}
	
GO.util.stringToFunction = function(str) {
  var arr = str.split(".");

  var fn = (window || this);
  for (var i = 0, len = arr.length; i < len; i++) {
    fn = fn[arr[i]];
  }

  if (typeof fn !== "function") {
    throw new Error("function not found");
  }

  return  fn;
};

/**
 * Translate a string
 * 
 * Module and package can be omitted in most cases. It will auto detect these.
 * 
 * go.module and go.package are set at:
 * 
 * 1. Before each module scripts are loaded
 * 2. An override on Ext.extend() will set "module" and "package" on each 
 *    components. A second override on Ext.Component will set 
 *    go.Translate.module and package on getId() (getId() was the only way to 
 *    make it happen always and on time) This override was made on ext-all-debug.js
 *    because it had to do something before and after initcomponent and 
 *    overriding constructors is not possible.
 * 
 * @param {string} str
 * @param {string} module
 * @param {string} package
 * @returns {t.l|GO..lang}
 */
function t(str, module, package, dontFallBack) {
	
	if(module && !package) {
		package = "legacy";
	}
		
	if(!module) {
		module = go.Translate.module;		
	}
	if(!package) {
		package = go.Translate.package;
	}
	
	if(!GO.lang[package] || !GO.lang[package][module]) {
		if(dontFallBack) {
			return str;
		}
		return t(str, "core", "core");
	}
	
	var l = GO.lang[package][module];
  
  if(l[str]) {
    return l[str]
	}
  
  if((module != "core" || package != "core") && !dontFallBack){
    return t(str, "core", "core");
  } else
  {
		str = str.replace("GroupOffice", GO.settings.config.product_name);
		str = str.replace("Group-Office", GO.settings.config.product_name);
		str = str.replace("{product_name}", GO.settings.config.product_name);
    return str;
  }
};
/**
 * Strpos function for js 
 */
GO.util.strpos=function(haystack, needle, offset) {
	var i = haystack.indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
}


GO.util.isIpad=function(){
	return navigator.userAgent.match(/iPad/i) != null;
}

GO.util.isAndroid=function(){
	var ua = navigator.userAgent.toLowerCase();
	var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
	
	return isAndroid;
}

//GO.log is a namespace for the log module!

//GO.log = function(v){
//	if(console)
//		console.log(v);
//}

GO.openHelp = function(page){

	var language = GO.settings.language;
	var baseUrl = false;
	
	if(typeof GO.settings.config.help_link == 'string'){
		baseUrl = GO.settings.config.help_link;
	}else if(typeof GO.settings.config.help_link[language] == 'undefined'){
		baseUrl = GO.settings.config.help_link.en;
	}else{
		baseUrl = GO.settings.config.help_link[language];
	}

	GO.util.popup({width:1024,height:768,focus:true,url:baseUrl+page,toolbar:"yes",location:"yes",status:"yes",menubar:"yes",target:'gohelp'})
}


GO.util.callToLink = function(phone){
		return '<a onclick="GO.util.callToHandler(\''+phone+'\');">'+phone+'</a>';	
}

GO.util.callToHandler = function(phone) {	
	var url = GO.calltoTemplate.replace('{phone}', phone.replace('(0)','').replace(/[^0-9+]/g,''));
	if(GO.calltoOpenWindow) {
		window.open(url);
	} else
	{
		window.location.replace(url);
	}
	return false;
}

GO.url = function(relativeUrl, params){
	if(!relativeUrl && !params)
		return BaseHref;
	
	var url = BaseHref+'index.php?r='+relativeUrl+'&security_token='+GO.securityToken;
	if(params){
		for(var name in params){
			url += '&'+name+'='+encodeURIComponent(params[name]);
		}
	}
	return url;
}


/**
 * Generic request function. Must handle exportVariables in responses.
 * 
 * exportVariables = {
 * varName: mixed
 * }
 * 
 */
GO.request = function(config){
	
//	Ext.Ajax.timeout=180000;

	var url = GO.url(config.url);
	delete config.url;
	
	if(!config.scope)
		config.scope=this;
	
	
	
	
	if(config.maskEl){
		if(!config.maskText)
			config.maskText=t("Loading...");
	
		config.maskEl.mask(config.maskText);
	}
	
	var origSuccess=config.success;
	delete config.success;
	
	var p = Ext.apply({
		url:url,
		callback:function(options, success, response){
			
//			console.log(response);
//
			if(config.maskEl)
				config.maskEl.unmask();

			if(!success) {
				if(response.isTimeout){
					GO.errorDialog.show(t("The request timed out. The server took too long to respond. Please try again."));
				}

				if (config.fail) {
					config.fail.call(config.scope, response, options);
				} else {
					console.error(response, options);
					Ext.Msg.alert(t("Error"), "Failed to send request to the server. Please check your internet connection.");
				}
			}
		},
		success: function(response, options)
		{
			var result = Ext.decode(response.responseText);
			if(!result.success)
			{
				if(config.fail){
					config.fail.call(config.scope, response, options, result);
				} else {
					Ext.Msg.alert(t("Error"), result.feedback);
				}
			}else 
			{
				//the same happens in GO.data.JSonStore.
				if(result.exportVariables){					
					GO.util.mergeObjects(window, result.exportVariables);				
				}
				
				if(origSuccess)					
					origSuccess.call(config.scope, response, options, result);				
			}
			
		}
	}, config);
	
	Ext.Ajax.request(p)
}

GO.util.mergeObjects = function(a, b) {
    for(var item in b){
        if(a[item]){
            if(typeof b[item] === 'object' && !b[item].length){
                GO.util.mergeObjects (a[item], b[item]);
            } else {
                if(typeof a[item] === 'object' || typeof b[item] === 'object') {
                    a[item] = [].concat(a[item],b[item]);
                } else {
                    a[item] = [a[item],b[item]];  // assumes that merged members that are common should become an array.
                }
            }
        } else {
            a[item] = b[item];
        }
    }
    return a;
}

//Ext.Ajax.on('requestcomplete', function(){
//	
//}, this);


GO.util.empty = function(v)
{
	return go.util.empty(v);
}

GO.mailTo = function(email){
	return '<a href="mailto:'+email+'">'+email+'</a>';	
}

GO.util.getFileExtension = function(filename)
{
	var lastIndex = filename.lastIndexOf('.');
	var extension = '';
	if(lastIndex)
	{
		extension = filename.substr(lastIndex+1);
	}
	return extension.toLowerCase();
}

GO.util.nl2br = function (v)
{
	v+="";
	return v.replace(/\n/g, '<br />');
}

GO.util.clone = function (obj){
    if(obj == null || typeof(obj) != 'object')
        return obj;
    var temp = new obj.constructor(); // changed (twice)

    //var temp = {};

    for(var key in obj)
        temp[key] = obj[key];

    return temp;

}
/**
 * Handles default error messages from the Group-Office server. It checks for the 
 * precense of UNAUTHORIZED or NOTLOGGEDIN as error message. It will present a 
 * login dialog if the user needs to login
 * 
 * @param {Object} json JSON object returned from the GO server. 
 * @param (Function} callback Callback function to call after successful login
 * @param {Object} scope	Scope the function to this object
 * 
 * @returns {Boolean} True if no errors have been returned.
 */
 
GO.jsonAuthHandler = function(json, callback, scope)
{
	if(json.authError)
	{
		switch(json.authError)
		{
			case 'UNAUTHORIZED':
				alert(t("You don't have permission to perform this action"));
				return false;
			
			case 'NOTLOGGEDIN':			
				
				if(callback)
				{
					GO.loginDialog.addCallback(callback, scope);
				}
							
				GO.loginDialog.show();
				return false;
		}
	}
	return true;
}



//url, params, count, callback, success, failure, scope ( success & failure are callbacks)
//store. If you pass a store it will automatically reload it with the params
//it will reload with a callback that will check for deleteSuccess in the json reponse. If it
//failed it will display deleteFeedback
GO.deleteItems = function(config)
{	
	config.extraWarning=config.extraWarning || "";
	switch(config.count)
	{
		case 0:
			alert(t("You didn't select an item."));
			return false;
		
		case 1:
			var strConfirm = config.extraWarning+t("Are you sure you want to delete the selected item?");
		break;
		
		default:
			var template = new Ext.Template(
		    	config.extraWarning+t("Are you sure you want to delete the {count} items?")
			);
			var strConfirm = template.applyTemplate({'count': config.count});						
		break;						
	}

	if(config.noConfirmation || confirm(strConfirm)){
		
		if(config.maskEl){
			config.maskEl.mask(t("Delete"));
		}
		
		if(config.store)
		{
			//add the parameters
			for(var param in config.params)
			{
				config.store.baseParams[param]=config.params[param];
			}
			
			var params = {};
			
			if(config.store.lastOptions && config.store.lastOptions.params && config.store.lastOptions.params.start)
				params.start=config.store.lastOptions.params.start;
			
			
						
			config.store.load({
				params: params,
				callback: function(){
					
					if(config.maskEl)
						config.maskEl.unmask();	
					
					var callback;
					if(!this.reader.jsonData.deleteSuccess)
					{
						if(config.failure)
						{
							callback = config.failure.createDelegate(config.scope);
							callback.call(config.scope, config);
						}
						Ext.MessageBox.alert(t("Error"),this.reader.jsonData.deleteFeedback);
//						alert( this.reader.jsonData.deleteFeedback);
					}else
					{
						if(config.success)
						{
							callback = config.success.createDelegate(config.scope);
							callback.call(config.scope, config);
						}
					}
					
					if(config.callback)
					{
						callback = config.callback.createDelegate(config.scope);
						callback.call(this, config);
					}	
					
					
					if(config.grid && typeof(config.grid.selectNextAfterDelete)=="function"){
	
						config.grid.selectNextAfterDelete(config.selectRecordAfterDelete);
						
//						if(!GO.util.empty(config.selectRecordAfterDelete)){
//							
//						} else {
//							config.grid.selectNextAfterDelete();
//						}
					}
					
				}
			}
			);
			
			//remove the delete params
			for(var param in config.params)
			{					
				delete config.store.baseParams[param];					
			}
			
			
		}else
		{

			Ext.Ajax.request({
				url: config.url,
				params: config.params,
				callback: function(options, success, response)
				{
					if(config.maskEl)
						config.maskEl.unmask();	
					
					var callback;
					
					var responseParams = Ext.decode(response.responseText);
					if(!responseParams.success)
					{
						if(config.failure)
						{
							callback = config.failure.createDelegate(config.scope);
							callback.call(this, responseParams);
						}
//						alert( responseParams.feedback);
						Ext.MessageBox.alert(t("Error"),responseParams.feedback);
					}else
					{
						if(config.success)
						{
							callback = config.success.createDelegate(config.scope);
							callback.call(this, responseParams);
						}
					}
					
					if(config.callback)
					{
						callback = config.callback.createDelegate(config.scope);
						callback.call(this, responseParams);
					}
				}
							
			});
		}	
	}
	
}

GO.util.unlocalizeNumber = function (number, decimal_separator, thousands_separator)
{
	if(GO.util.empty(number)){
		return 0;
	}
	if(!decimal_separator)
	{
		decimal_separator=GO.settings.decimal_separator;
	}
	
	if(!thousands_separator)
	{
		thousands_separator=GO.settings.thousands_separator;
	}
	
	number = number+"";

	if(thousands_separator!=""){
		var re = new RegExp('['+thousands_separator+']', 'g');
		number = number.replace(re, "");
	}
	
	number = parseFloat(number.replace(decimal_separator, "."));
	
	if(isNaN(number))
		number=0;
	
	return number;
}

String.prototype.regexpEscape = function() {
  var specials = [
    '/', '.', '*', '+', '?', '|',
    '(', ')', '[', ']', '{', '}', '\\'
  ];
  var re = new RegExp(
    '(\\' + specials.join('|\\') + ')', 'g'
  );

  return this.replace(re, '\\$1');
}



GO.util.numberFormat = function (number, decimals, decimal_separator, thousands_separator)
{
	if(typeof(decimals)=='undefined')
	{
		decimals=2;
	}
	
	if(!decimal_separator)
	{
		decimal_separator=GO.settings.decimal_separator;
	}
	
	if(!thousands_separator)
	{
		thousands_separator=GO.settings.thousands_separator;
	}

	if(number=='')
	{
		number='0';
	}
	
/*	if(localized)
	{
		var internal_number = number.replace(thousands_separator, "");
		internal_number = internal_number.replace(decimal_separator, ".");
	}else
	{
		var internal_number=number;
	}*/
	
	var numberFloat = parseFloat(number);
	
	numberFloat = numberFloat.toFixed(decimals);
		
	
	if(decimals>0)
	{
		var dotIndex = numberFloat.indexOf(".");	
		if(!dotIndex)
		{
			numberFloat = numberFloat+".";
			dotIndex = numberFloat.indexOf(".");	
		}
		
		var presentDecimals = numberFloat.length-dotIndex;
		
		for(var i=presentDecimals;i<=decimals;i++)
		{
			numberFloat = numberFloat+"0";
		}
		var formattedNumber = decimal_separator+numberFloat.substring(dotIndex+1);
		
		var dec = decimals;
		while(formattedNumber.substring(formattedNumber.length-1)=='0' && dec>decimals)
		{
			dec--;
			formattedNumber = formattedNumber.substring(0,formattedNumber.length-1);
		}
		
	}else
	{
		
		var formattedNumber = "";
		var dotIndex = numberFloat.length;
	}

	var counter=0;
	for(var i=dotIndex-1;i>=0;i--)
	{
		if(counter==3 && numberFloat.substr(i,1)!='-')
		{
			formattedNumber= thousands_separator+formattedNumber;
			counter=0;
		}
		formattedNumber = numberFloat.substr(i,1)+formattedNumber;
		counter++;		
	}
	if(formattedNumber==',NaN')
	{
		formattedNumber = GO.util.numberFormat('0', decimals, decimal_separator, thousands_separator);
	}
	return formattedNumber;
}

GO.util.round = function(value, roundInterval, roundDown){
	roundInterval = parseFloat(roundInterval);
	value= parseFloat(value);
	if(roundInterval>0){

		var divided = value/roundInterval;

		divided = roundDown ? Math.floor(divided) : Math.ceil(divided);
		value = divided*roundInterval;
	}

	return value;
}

GO.util.popup = function (c)
{
	var config = {
		scrollbars:"1",
		resizable:"1",
		location:"0",
		status:"0",
		target:'_blank'
	}

	Ext.apply(config, c);

	if(!config.width)
	{
		config.width = screen.availWidth;
		config.height = screen.availHeight;
	}

	if (typeof(config.left)=='undefined' || typeof(config.top)=='undefined'){
		config.position=config.position || 'center';

		if(config.position=='center'){
			config.left = (screen.availWidth - config.width) / 2;
			config.top = (screen.availHeight - config.height) / 2;
		}else
		{
			config.left = screen.availWidth - config.width;
			config.top = screen.availHeight - config.height;
		}
	}

	var noFeatures = ['url', 'position', 'focus', 'closeOnFocus','target'];

	var options = '';
	for(var key in config){
		if(noFeatures.indexOf(key)==-1)
			options+=','+key+'='+config[key];
	}
	options=options.substring(1, options.length);
	
//	console.log(options);

	var popup = window.open(config.url, config.target, options);
	
	if(!popup)
	{
		alert(t("Your browser is blocking a popup from Group-Office. Please disable the popup blocker for this site"));
		return false;
	}
	
  if (!popup.opener) popup.opener = self;

	if(config.focus)
		popup.focus();

	if(config.closeOnFocus)
		GO.mainLayout.on('focus', function(){popup.close();}, {single:true});
	
	if(config.allwaysOnTop) // Not working??
		GO.mainLayout.on('focus', function(){popup.focus();}, {single:true});
	
	return popup;
}



GO.util.get_html_translation_table = function(table, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js. Meaning the constants are not
    // %          note: real constants, but strings instead. integers are also supported if someone
    // %          note: chooses to create the constants themselves.
    // %          note: Table from http://www.the-art-of-web.com/html/character-codes/
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
    
    var entities = {}, histogram = {}, decimal = 0, symbol = '';
    var constMappingTable = {}, constMappingQuoteStyle = {};
    var useTable = {}, useQuoteStyle = {};
    
    useTable      = (table ? table.toUpperCase() : 'HTML_SPECIALCHARS');
    useQuoteStyle = (quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT');
    
    // Translate arguments
    constMappingTable[0]      = 'HTML_SPECIALCHARS';
    constMappingTable[1]      = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';
    
    // Map numbers to strings for compatibilty with PHP constants
    if (!isNaN(useTable)) {
        useTable = constMappingTable[useTable];
    }
    if (!isNaN(useQuoteStyle)) {
        useQuoteStyle = constMappingQuoteStyle[useQuoteStyle];
    }
    
    if (useTable == 'HTML_SPECIALCHARS') {
        // ascii decimals for better compatibility
        entities['38'] = '&amp;';
        entities['60'] = '&lt;';
        entities['62'] = '&gt;';
    } else if (useTable == 'HTML_ENTITIES') {
        // ascii decimals for better compatibility
      entities['38'] = '&amp;';
      entities['60'] = '&lt;';
      entities['62'] = '&gt;';
      entities['160'] = '&nbsp;';
      entities['161'] = '&iexcl;';
      entities['162'] = '&cent;';
      entities['163'] = '&pound;';
      entities['164'] = '&curren;';
      entities['165'] = '&yen;';
      entities['166'] = '&brvbar;';
      entities['167'] = '&sect;';
      entities['168'] = '&uml;';
      entities['169'] = '&copy;';
      entities['170'] = '&ordf;';
      entities['171'] = '&laquo;';
      entities['172'] = '&not;';
      entities['173'] = '&shy;';
      entities['174'] = '&reg;';
      entities['175'] = '&macr;';
      entities['176'] = '&deg;';
      entities['177'] = '&plusmn;';
      entities['178'] = '&sup2;';
      entities['179'] = '&sup3;';
      entities['180'] = '&acute;';
      entities['181'] = '&micro;';
      entities['182'] = '&para;';
      entities['183'] = '&middot;';
      entities['184'] = '&cedil;';
      entities['185'] = '&sup1;';
      entities['186'] = '&ordm;';
      entities['187'] = '&raquo;';
      entities['188'] = '&frac14;';
      entities['189'] = '&frac12;';
      entities['190'] = '&frac34;';
      entities['191'] = '&iquest;';
      entities['192'] = '&Agrave;';
      entities['193'] = '&Aacute;';
      entities['194'] = '&Acirc;';
      entities['195'] = '&Atilde;';
      entities['196'] = '&Auml;';
      entities['197'] = '&Aring;';
      entities['198'] = '&AElig;';
      entities['199'] = '&Ccedil;';
      entities['200'] = '&Egrave;';
      entities['201'] = '&Eacute;';
      entities['202'] = '&Ecirc;';
      entities['203'] = '&Euml;';
      entities['204'] = '&Igrave;';
      entities['205'] = '&Iacute;';
      entities['206'] = '&Icirc;';
      entities['207'] = '&Iuml;';
      entities['208'] = '&ETH;';
      entities['209'] = '&Ntilde;';
      entities['210'] = '&Ograve;';
      entities['211'] = '&Oacute;';
      entities['212'] = '&Ocirc;';
      entities['213'] = '&Otilde;';
      entities['214'] = '&Ouml;';
      entities['215'] = '&times;';
      entities['216'] = '&Oslash;';
      entities['217'] = '&Ugrave;';
      entities['218'] = '&Uacute;';
      entities['219'] = '&Ucirc;';
      entities['220'] = '&Uuml;';
      entities['221'] = '&Yacute;';
      entities['222'] = '&THORN;';
      entities['223'] = '&szlig;';
      entities['224'] = '&agrave;';
      entities['225'] = '&aacute;';
      entities['226'] = '&acirc;';
      entities['227'] = '&atilde;';
      entities['228'] = '&auml;';
      entities['229'] = '&aring;';
      entities['230'] = '&aelig;';
      entities['231'] = '&ccedil;';
      entities['232'] = '&egrave;';
      entities['233'] = '&eacute;';
      entities['234'] = '&ecirc;';
      entities['235'] = '&euml;';
      entities['236'] = '&igrave;';
      entities['237'] = '&iacute;';
      entities['238'] = '&icirc;';
      entities['239'] = '&iuml;';
      entities['240'] = '&eth;';
      entities['241'] = '&ntilde;';
      entities['242'] = '&ograve;';
      entities['243'] = '&oacute;';
      entities['244'] = '&ocirc;';
      entities['245'] = '&otilde;';
      entities['246'] = '&ouml;';
      entities['247'] = '&divide;';
      entities['248'] = '&oslash;';
      entities['249'] = '&ugrave;';
      entities['250'] = '&uacute;';
      entities['251'] = '&ucirc;';
      entities['252'] = '&uuml;';
      entities['253'] = '&yacute;';
      entities['254'] = '&thorn;';
      entities['255'] = '&yuml;';
    } else {
        throw Error("Table: "+useTable+' not supported');
        return false;
    }
    
    if (useQuoteStyle != 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
    
    if (useQuoteStyle == 'ENT_QUOTES') {
        entities['39'] = '&#039;';
    }
    
    // ascii decimals to real symbols
    for (decimal in entities) {
        symbol = String.fromCharCode(decimal)
        histogram[symbol] = entities[decimal];
    }
    
    return histogram;
}


GO.util.html_entity_decode = function (string, quote_style ) {
    // http://kevin.vanzonneveld.net
    // +   original by: john (http://www.jd-tech.net)
    // +      input by: ger
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: marc andreu
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: get_html_translation_table
    // *     example 1: html_entity_decode('Kevin &amp; van Zonneveld');
    // *     returns 1: 'Kevin & van Zonneveld'
 
	string+="";
    var histogram = {}, symbol = '', tmp_str = '', i = 0;
    tmp_str = string.toString();
    
    if (false === (histogram = GO.util.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }
    var entity;
    for (symbol in histogram) {
        entity = histogram[symbol];
        tmp_str = tmp_str.split(entity).join(symbol);
    }
    
    return tmp_str;
};

GO.util.add_slashes = function(str)
{
	return (str+'').replace(/([\\"'])/g, "\\$1").replace(/\0/g, "\\0");
};

GO.util.addParamToUrl = function(url, param, value){
	var splitter = url.indexOf('?')!=-1 ? '&' : '?';
	return url+splitter+param+'='+encodeURIComponent(value);
};

GO.util.basename = function(path)
{
	var pos = path.lastIndexOf('/');
	if(pos)
	{
		path = path.substring(pos+1);
	}
	return path;
};

GO.util.dirname = function(path)
{
	var pos = path.lastIndexOf('/');
	if(pos)
	{
		path = path.substring(0, pos);
	}
	return path;
};


GO.util.logExtEvents = function() {
    var o = Ext.util.Observable.prototype;
    o.fireEvent = o.fireEvent.createInterceptor(function(evt) {
        var a = arguments;
        console.log(this, ' fired event ',evt,' with args ',Array.prototype.slice.call(a, 1, a.length));
    });
};

if(GO.settings && GO.settings.time_format){
	GO.date = {
		hours:[],
		minutes:[]
	};

	if (GO.settings.time_format.substr(0, 1) == 'H' || GO.settings.time_format.substr(0, 1) == 'h') {
			var timeformat = 'H';
	} else {			
			var timeformat = 'g a';
	}

	for (var i = 0; i < 24; i++) {
			var h = Date.parseDate(i, "G");
			GO.date.hours.push([h.format('G'), h.format(timeformat)]);
	}

	GO.date.minutes = [['00', '00'], ['05', '05'], ['10', '10'], ['15', '15'],
					['20', '20'], ['25', '25'], ['30', '30'], ['35', '35'],
					['40', '40'], ['45', '45'], ['50', '50'], ['55', '55']];
}


GO.util.HtmlDecode = function HtmlDecode(s) {
	return s.replace(/&[a-z]+;/gi, function (entity) {
		switch (entity) {
			case '&quot;':
				return String.fromCharCode(0x0022);
			case '&amp;':
				return String.fromCharCode(0x0026);
			case '&lt;':
				return String.fromCharCode(0x003c);
			case '&gt;':
				return String.fromCharCode(0x003e);
			case '&nbsp;':
				return String.fromCharCode(0x00a0);
			case '&iexcl;':
				return String.fromCharCode(0x00a1);
			case '&cent;':
				return String.fromCharCode(0x00a2);
			case '&pound;':
				return String.fromCharCode(0x00a3);
			case '&curren;':
				return String.fromCharCode(0x00a4);
			case '&yen;':
				return String.fromCharCode(0x00a5);
			case '&brvbar;':
				return String.fromCharCode(0x00a6);
			case '&sect;':
				return String.fromCharCode(0x00a7);
			case '&uml;':
				return String.fromCharCode(0x00a8);
			case '&copy;':
				return String.fromCharCode(0x00a9);
			case '&ordf;':
				return String.fromCharCode(0x00aa);
			case '&laquo;':
				return String.fromCharCode(0x00ab);
			case '&not;':
				return String.fromCharCode(0x00ac);
			case '&shy;':
				return String.fromCharCode(0x00ad);
			case '&reg;':
				return String.fromCharCode(0x00ae);
			case '&macr;':
				return String.fromCharCode(0x00af);
			case '&deg;':
				return String.fromCharCode(0x00b0);
			case '&plusmn;':
				return String.fromCharCode(0x00b1);
			case '&sup2;':
				return String.fromCharCode(0x00b2);
			case '&sup3;':
				return String.fromCharCode(0x00b3);
			case '&acute;':
				return String.fromCharCode(0x00b4);
			case '&micro;':
				return String.fromCharCode(0x00b5);
			case '&para;':
				return String.fromCharCode(0x00b6);
			case '&middot;':
				return String.fromCharCode(0x00b7);
			case '&cedil;':
				return String.fromCharCode(0x00b8);
			case '&sup1;':
				return String.fromCharCode(0x00b9);
			case '&ordm;':
				return String.fromCharCode(0x00ba);
			case '&raquo;':
				return String.fromCharCode(0x00bb);
			case '&frac14;':
				return String.fromCharCode(0x00bc);
			case '&frac12;':
				return String.fromCharCode(0x00bd);
			case '&frac34;':
				return String.fromCharCode(0x00be);
			case '&iquest;':
				return String.fromCharCode(0x00bf);
			case '&Agrave;':
				return String.fromCharCode(0x00c0);
			case '&Aacute;':
				return String.fromCharCode(0x00c1);
			case '&Acirc;':
				return String.fromCharCode(0x00c2);
			case '&Atilde;':
				return String.fromCharCode(0x00c3);
			case '&Auml;':
				return String.fromCharCode(0x00c4);
			case '&Aring;':
				return String.fromCharCode(0x00c5);
			case '&AElig;':
				return String.fromCharCode(0x00c6);
			case '&Ccedil;':
				return String.fromCharCode(0x00c7);
			case '&Egrave;':
				return String.fromCharCode(0x00c8);
			case '&Eacute;':
				return String.fromCharCode(0x00c9);
			case '&Ecirc;':
				return String.fromCharCode(0x00ca);
			case '&Euml;':
				return String.fromCharCode(0x00cb);
			case '&Igrave;':
				return String.fromCharCode(0x00cc);
			case '&Iacute;':
				return String.fromCharCode(0x00cd);
			case '&Icirc;':
				return String.fromCharCode(0x00ce);
			case '&Iuml;':
				return String.fromCharCode(0x00cf);
			case '&ETH;':
				return String.fromCharCode(0x00d0);
			case '&Ntilde;':
				return String.fromCharCode(0x00d1);
			case '&Ograve;':
				return String.fromCharCode(0x00d2);
			case '&Oacute;':
				return String.fromCharCode(0x00d3);
			case '&Ocirc;':
				return String.fromCharCode(0x00d4);
			case '&Otilde;':
				return String.fromCharCode(0x00d5);
			case '&Ouml;':
				return String.fromCharCode(0x00d6);
			case '&times;':
				return String.fromCharCode(0x00d7);
			case '&Oslash;':
				return String.fromCharCode(0x00d8);
			case '&Ugrave;':
				return String.fromCharCode(0x00d9);
			case '&Uacute;':
				return String.fromCharCode(0x00da);
			case '&Ucirc;':
				return String.fromCharCode(0x00db);
			case '&Uuml;':
				return String.fromCharCode(0x00dc);
			case '&Yacute;':
				return String.fromCharCode(0x00dd);
			case '&THORN;':
				return String.fromCharCode(0x00de);
			case '&szlig;':
				return String.fromCharCode(0x00df);
			case '&agrave;':
				return String.fromCharCode(0x00e0);
			case '&aacute;':
				return String.fromCharCode(0x00e1);
			case '&acirc;':
				return String.fromCharCode(0x00e2);
			case '&atilde;':
				return String.fromCharCode(0x00e3);
			case '&auml;':
				return String.fromCharCode(0x00e4);
			case '&aring;':
				return String.fromCharCode(0x00e5);
			case '&aelig;':
				return String.fromCharCode(0x00e6);
			case '&ccedil;':
				return String.fromCharCode(0x00e7);
			case '&egrave;':
				return String.fromCharCode(0x00e8);
			case '&eacute;':
				return String.fromCharCode(0x00e9);
			case '&ecirc;':
				return String.fromCharCode(0x00ea);
			case '&euml;':
				return String.fromCharCode(0x00eb);
			case '&igrave;':
				return String.fromCharCode(0x00ec);
			case '&iacute;':
				return String.fromCharCode(0x00ed);
			case '&icirc;':
				return String.fromCharCode(0x00ee);
			case '&iuml;':
				return String.fromCharCode(0x00ef);
			case '&eth;':
				return String.fromCharCode(0x00f0);
			case '&ntilde;':
				return String.fromCharCode(0x00f1);
			case '&ograve;':
				return String.fromCharCode(0x00f2);
			case '&oacute;':
				return String.fromCharCode(0x00f3);
			case '&ocirc;':
				return String.fromCharCode(0x00f4);
			case '&otilde;':
				return String.fromCharCode(0x00f5);
			case '&ouml;':
				return String.fromCharCode(0x00f6);
			case '&divide;':
				return String.fromCharCode(0x00f7);
			case '&oslash;':
				return String.fromCharCode(0x00f8);
			case '&ugrave;':
				return String.fromCharCode(0x00f9);
			case '&uacute;':
				return String.fromCharCode(0x00fa);
			case '&ucirc;':
				return String.fromCharCode(0x00fb);
			case '&uuml;':
				return String.fromCharCode(0x00fc);
			case '&yacute;':
				return String.fromCharCode(0x00fd);
			case '&thorn;':
				return String.fromCharCode(0x00fe);
			case '&yuml;':
				return String.fromCharCode(0x00ff);
			case '&OElig;':
				return String.fromCharCode(0x0152);
			case '&oelig;':
				return String.fromCharCode(0x0153);
			case '&Scaron;':
				return String.fromCharCode(0x0160);
			case '&scaron;':
				return String.fromCharCode(0x0161);
			case '&Yuml;':
				return String.fromCharCode(0x0178);
			case '&fnof;':
				return String.fromCharCode(0x0192);
			case '&circ;':
				return String.fromCharCode(0x02c6);
			case '&tilde;':
				return String.fromCharCode(0x02dc);
			case '&Alpha;':
				return String.fromCharCode(0x0391);
			case '&Beta;':
				return String.fromCharCode(0x0392);
			case '&Gamma;':
				return String.fromCharCode(0x0393);
			case '&Delta;':
				return String.fromCharCode(0x0394);
			case '&Epsilon;':
				return String.fromCharCode(0x0395);
			case '&Zeta;':
				return String.fromCharCode(0x0396);
			case '&Eta;':
				return String.fromCharCode(0x0397);
			case '&Theta;':
				return String.fromCharCode(0x0398);
			case '&Iota;':
				return String.fromCharCode(0x0399);
			case '&Kappa;':
				return String.fromCharCode(0x039a);
			case '&Lambda;':
				return String.fromCharCode(0x039b);
			case '&Mu;':
				return String.fromCharCode(0x039c);
			case '&Nu;':
				return String.fromCharCode(0x039d);
			case '&Xi;':
				return String.fromCharCode(0x039e);
			case '&Omicron;':
				return String.fromCharCode(0x039f);
			case '&Pi;':
				return String.fromCharCode(0x03a0);
			case '& Rho ;':
				return String.fromCharCode(0x03a1);
			case '&Sigma;':
				return String.fromCharCode(0x03a3);
			case '&Tau;':
				return String.fromCharCode(0x03a4);
			case '&Upsilon;':
				return String.fromCharCode(0x03a5);
			case '&Phi;':
				return String.fromCharCode(0x03a6);
			case '&Chi;':
				return String.fromCharCode(0x03a7);
			case '&Psi;':
				return String.fromCharCode(0x03a8);
			case '&Omega;':
				return String.fromCharCode(0x03a9);
			case '&alpha;':
				return String.fromCharCode(0x03b1);
			case '&beta;':
				return String.fromCharCode(0x03b2);
			case '&gamma;':
				return String.fromCharCode(0x03b3);
			case '&delta;':
				return String.fromCharCode(0x03b4);
			case '&epsilon;':
				return String.fromCharCode(0x03b5);
			case '&zeta;':
				return String.fromCharCode(0x03b6);
			case '&eta;':
				return String.fromCharCode(0x03b7);
			case '&theta;':
				return String.fromCharCode(0x03b8);
			case '&iota;':
				return String.fromCharCode(0x03b9);
			case '&kappa;':
				return String.fromCharCode(0x03ba);
			case '&lambda;':
				return String.fromCharCode(0x03bb);
			case '&mu;':
				return String.fromCharCode(0x03bc);
			case '&nu;':
				return String.fromCharCode(0x03bd);
			case '&xi;':
				return String.fromCharCode(0x03be);
			case '&omicron;':
				return String.fromCharCode(0x03bf);
			case '&pi;':
				return String.fromCharCode(0x03c0);
			case '&rho;':
				return String.fromCharCode(0x03c1);
			case '&sigmaf;':
				return String.fromCharCode(0x03c2);
			case '&sigma;':
				return String.fromCharCode(0x03c3);
			case '&tau;':
				return String.fromCharCode(0x03c4);
			case '&upsilon;':
				return String.fromCharCode(0x03c5);
			case '&phi;':
				return String.fromCharCode(0x03c6);
			case '&chi;':
				return String.fromCharCode(0x03c7);
			case '&psi;':
				return String.fromCharCode(0x03c8);
			case '&omega;':
				return String.fromCharCode(0x03c9);
			case '&thetasym;':
				return String.fromCharCode(0x03d1);
			case '&upsih;':
				return String.fromCharCode(0x03d2);
			case '&piv;':
				return String.fromCharCode(0x03d6);
			case '&ensp;':
				return String.fromCharCode(0x2002);
			case '&emsp;':
				return String.fromCharCode(0x2003);
			case '&thinsp;':
				return String.fromCharCode(0x2009);
			case '&zwnj;':
				return String.fromCharCode(0x200c);
			case '&zwj;':
				return String.fromCharCode(0x200d);
			case '&lrm;':
				return String.fromCharCode(0x200e);
			case '&rlm;':
				return String.fromCharCode(0x200f);
			case '&ndash;':
				return String.fromCharCode(0x2013);
			case '&mdash;':
				return String.fromCharCode(0x2014);
			case '&lsquo;':
				return String.fromCharCode(0x2018);
			case '&rsquo;':
				return String.fromCharCode(0x2019);
			case '&sbquo;':
				return String.fromCharCode(0x201a);
			case '&ldquo;':
				return String.fromCharCode(0x201c);
			case '&rdquo;':
				return String.fromCharCode(0x201d);
			case '&bdquo;':
				return String.fromCharCode(0x201e);
			case '&dagger;':
				return String.fromCharCode(0x2020);
			case '&Dagger;':
				return String.fromCharCode(0x2021);
			case '&bull;':
				return String.fromCharCode(0x2022);
			case '&hellip;':
				return String.fromCharCode(0x2026);
			case '&permil;':
				return String.fromCharCode(0x2030);
			case '&prime;':
				return String.fromCharCode(0x2032);
			case '&Prime;':
				return String.fromCharCode(0x2033);
			case '&lsaquo;':
				return String.fromCharCode(0x2039);
			case '&rsaquo;':
				return String.fromCharCode(0x203a);
			case '&oline;':
				return String.fromCharCode(0x203e);
			case '&frasl;':
				return String.fromCharCode(0x2044);
			case '&euro;':
				return String.fromCharCode(0x20ac);
			case '&image;':
				return String.fromCharCode(0x2111);
			case '&weierp;':
				return String.fromCharCode(0x2118);
			case '&real;':
				return String.fromCharCode(0x211c);
			case '&trade;':
				return String.fromCharCode(0x2122);
			case '&alefsym;':
				return String.fromCharCode(0x2135);
			case '&larr;':
				return String.fromCharCode(0x2190);
			case '&uarr;':
				return String.fromCharCode(0x2191);
			case '&rarr;':
				return String.fromCharCode(0x2192);
			case '&darr;':
				return String.fromCharCode(0x2193);
			case '&harr;':
				return String.fromCharCode(0x2194);
			case '&crarr;':
				return String.fromCharCode(0x21b5);
			case '&lArr;':
				return String.fromCharCode(0x21d0);
			case '&uArr;':
				return String.fromCharCode(0x21d1);
			case '&rArr;':
				return String.fromCharCode(0x21d2);
			case '&dArr;':
				return String.fromCharCode(0x21d3);
			case '&hArr;':
				return String.fromCharCode(0x21d4);
			case '&forall;':
				return String.fromCharCode(0x2200);
			case '&part;':
				return String.fromCharCode(0x2202);
			case '&exist;':
				return String.fromCharCode(0x2203);
			case '&empty;':
				return String.fromCharCode(0x2205);
			case '&nabla;':
				return String.fromCharCode(0x2207);
			case '&isin;':
				return String.fromCharCode(0x2208);
			case '&notin;':
				return String.fromCharCode(0x2209);
			case '&ni;':
				return String.fromCharCode(0x220b);
			case '&prod;':
				return String.fromCharCode(0x220f);
			case '&sum;':
				return String.fromCharCode(0x2211);
			case '&minus;':
				return String.fromCharCode(0x2212);
			case '&lowast;':
				return String.fromCharCode(0x2217);
			case '&radic;':
				return String.fromCharCode(0x221a);
			case '&prop;':
				return String.fromCharCode(0x221d);
			case '&infin;':
				return String.fromCharCode(0x221e);
			case '&ang;':
				return String.fromCharCode(0x2220);
			case '&and;':
				return String.fromCharCode(0x2227);
			case '&or;':
				return String.fromCharCode(0x2228);
			case '&cap;':
				return String.fromCharCode(0x2229);
			case '&cup;':
				return String.fromCharCode(0x222a);
			case '&int;':
				return String.fromCharCode(0x222b);
			case '&there4;':
				return String.fromCharCode(0x2234);
			case '&sim;':
				return String.fromCharCode(0x223c);
			case '&cong;':
				return String.fromCharCode(0x2245);
			case '&asymp;':
				return String.fromCharCode(0x2248);
			case '&ne;':
				return String.fromCharCode(0x2260);
			case '&equiv;':
				return String.fromCharCode(0x2261);
			case '&le;':
				return String.fromCharCode(0x2264);
			case '&ge;':
				return String.fromCharCode(0x2265);
			case '&sub;':
				return String.fromCharCode(0x2282);
			case '&sup;':
				return String.fromCharCode(0x2283);
			case '&nsub;':
				return String.fromCharCode(0x2284);
			case '&sube;':
				return String.fromCharCode(0x2286);
			case '&supe;':
				return String.fromCharCode(0x2287);
			case '&oplus;':
				return String.fromCharCode(0x2295);
			case '&otimes;':
				return String.fromCharCode(0x2297);
			case '&perp;':
				return String.fromCharCode(0x22a5);
			case '&sdot;':
				return String.fromCharCode(0x22c5);
			case '&lceil;':
				return String.fromCharCode(0x2308);
			case '&rceil;':
				return String.fromCharCode(0x2309);
			case '&lfloor;':
				return String.fromCharCode(0x230a);
			case '&rfloor;':
				return String.fromCharCode(0x230b);
			case '&lang;':
				return String.fromCharCode(0x2329);
			case '&rang;':
				return String.fromCharCode(0x232a);
			case '&loz;':
				return String.fromCharCode(0x25ca);
			case '&spades;':
				return String.fromCharCode(0x2660);
			case '&clubs;':
				return String.fromCharCode(0x2663);
			case '&hearts;':
				return String.fromCharCode(0x2665);
			case '&diams;':
				return String.fromCharCode(0x2666);
			default:
				return '';
		}
	});
}

GO.util.dateFormat = function(v) {	
	return go.util.Format.dateTime(v);	
};
