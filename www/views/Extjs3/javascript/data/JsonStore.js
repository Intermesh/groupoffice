/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: JsonStore.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */


/**
 * @class GO.data.JsonStore
 * @extends Ext.data.JsonStore
 * 
 * Extends the Ext JsonStore class to handle Group-Office authentication automatically. <br/>
<pre><code>
var store = new GO.data.JsonStore({
    url: 'get-images.php',
    root: 'images',
    fields: ['name', 'url', {name:'size', type: 'float'}, {name:'lastmod', type:'date'}]
});
</code></pre>
 * This would consume a returned object of the form:
<pre><code>
{
    images: [
        {name: 'Image one', url:'/GetImage.php?id=1', size:46.5, lastmod: new Date(2007, 10, 29)},
        {name: 'Image Two', url:'/GetImage.php?id=2', size:43.2, lastmod: new Date(2007, 10, 30)}
    ]
}
</code></pre>
 * An object literal of this form could also be used as the {@link #data} config option.
 * <b>Note: Although they are not listed, this class inherits all of the config options of Store,
 * JsonReader.</b>
 * @cfg {String} url  The URL from which to load data through an HttpProxy. Either this
 * option, or the {@link #data} option must be specified.
 * @cfg {Object} data  A data object readable this object's JsonReader. Either this
 * option, or the {@link #url} option must be specified.
 * @cfg {Array} fields  Either an Array of field definition objects as passed to
 * {@link Ext.data.Record#create}, or a Record constructor object created using {@link Ext.data.Record#create}.
 * @constructor
 * @param {Object} config
 */

GO.data.JsonStore = function(config) {

	if((config.url || config.api) && !config.proxy){
		config.proxy = new GO.data.PrefetchProxy({url: config.url, api: config.api, fields: config.fields ? config.fields : config.reader.meta.fields});
	}


	Ext.applyIf(config,{
		root: 'results',	
		id: 'id',
		totalProperty:'total',
		remoteSort: true
	});
	
	if(GO.customfields && config.model)
	{
		if(GO.customfields.columns[config.model])
		{
			for(var i=0;i<GO.customfields.columns[config.model].length;i++)
			{
				if(GO.customfields.nonGridTypes.indexOf(GO.customfields.columns[config.model][i].datatype)==-1){
					if(GO.customfields.columns[config.model][i].exclude_from_grid != 'true')
					{
						config.fields.push(GO.customfields.columns[config.model][i].dataIndex);
																					
					}
				}
			}		
		}	
	}
	
	GO.data.JsonStore.superclass.constructor.call (this, config);

	this.on('load', function(){
		this.loaded=true;

		if(this.reader.jsonData.exportVariables){					
			Object.assign(window,this.reader.jsonData.exportVariables);
		}
		
		if(!this.suppressError && this.reader.jsonData.feedback){
			GO.errorDialog.show(this.reader.jsonData.feedback);
		}
		
	}, this);


	
	this.on('exception',		
		function( store, type, action, options, response){

			if(response.isAbort || this.suppressError) {
				//ignore aborts.
			} else if(response.isTimeout || response.status == 0){
				console.warn("Connection timeout", response, options);
				if(document.visibilityState === "visible") {
					GO.errorDialog.show(t("The connection to the server timed out. Please check your internet connection."), t("Request error"));
				}
			}else	if(!this.reader.jsonData || GO.jsonAuthHandler(this.reader.jsonData, this.load, this))
			{
				var msg;

				if(!GO.errorDialog.isVisible()){
					if(this.reader.jsonData && this.reader.jsonData.feedback)
					{
						msg = this.reader.jsonData.feedback;
						GO.errorDialog.show(msg);
					}else
					{
						msg = t("An error occurred on the webserver. Contact your system administrator and supply the detailed error.");
						msg += '<br /><br />JsonStore load exception occurred';
						GO.errorDialog.show(msg);
					}
				}					
			}else
			{
				console.error(response);

				GO.errorDialog.show(t("Failed to send the request to the server. Please check your internet connection."));
			}
		}
		,this);
};

Ext.extend(GO.data.JsonStore, Ext.data.JsonStore, {
	loaded : false	,
	
//	//useul to debug if you need to find out where a load was called from
//	load: function(options) {
//		
//		if(this.url.indexOf('addressbook/addressbook/store') != -1){
//			console.trace();	
//		}
//		GO.data.JsonStore.superclass.load.call(this, options);
//	},
	
	reload : function(options){
		
		if(this.lastOptions && this.lastOptions.params && this.lastOptions.params.add){
			delete this.lastOptions.params.add;
		}
		
		GO.data.JsonStore.superclass.reload.call(this, options);
	}
});
	
