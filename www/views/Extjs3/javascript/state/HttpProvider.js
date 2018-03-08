/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: HttpProvider.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
/**
 * @class Ext.state.HttpProvider
 * @extends Ext.state.Provider
 * The default Provider implementation which saves state via XmlHttpRequest calls to save it in 
 * a database.
 * <br />Usage:
 <pre><code>
   var cp = new Ext.state.HttpProvider({
      url: state.php
   });
   Ext.state.Manager.setProvider(cp);
   
   A global variable ExtState must be created!
   this variable holds all the all the values like this:
   
   var state { name: value };
   
   For example if you have a PHP array of settings you can do this in the document head:
   
   var ExtState = Ext.decode('<?php echo json_encode($state); ?>'); 
   
    
   The $state PHP variable holds all the values that you can pull from a database
  
   
 </code></pre>
 * @cfg {String} url The server page that will handle the request to save the state
 * when a value changes it will send 'name' and 'value' to that page. 
 * 
 * @constructor
 * Create a new HttpProvider
 * @param {Object} config The configuration object
 */
GO.state.HttpProvider = function(config){
	GO.state.HttpProvider.superclass.constructor.call(this);
	this.url = GO.url("core/saveState"); 
	/*
    if(!config.jsonState)
    {
    	config.jsonState={};
    }
    
    if(!config.index)
    {
    	config.index='';
    }
    this.index=config.index;*/
    
	Ext.apply(this, config);
	this.state = this.readValues();
};

Ext.extend(GO.state.HttpProvider, Ext.state.Provider, {

	changedValues : false,
	// private
	//only works when a component as an assigned ID passed to the constructor
	set : function(name, value, callback, scope){
    	
		if(name.substr(0,4)!='ext-')
		{
			if(typeof value == "undefined" || value === null){
				this.clear(name);
				return;
			}
				
			this.setValue(name, value, callback, scope);
			GO.state.HttpProvider.superclass.set.call(this, name, value);
		}
	},

	// private
	clear : function(name){
		this.clearValue(name);
		GO.state.HttpProvider.superclass.clear.call(this, name);
	},

	// private
	readValues : function(){
		var state = {};
	      
		for (var name in GO.settings.state)
		{
			if(name!='remove')
			{
				state[name] = this.decodeValue(GO.settings.state[name]);
			}
		}
		return state;
	},

	// private
	setValue : function(name, value, callback, scope){
		if(!this.changedValues){
			var defer=3000;
			if(GO.settings.config.debug){
				defer=100;
			}
			this.sendRequest.defer(defer, this, [callback, scope]);
			this.changedValues={};
		}
		this.changedValues[name]=this.encodeValue(value);
	},

	sendRequest : function(callback, scope){
			
		Ext.Ajax.request({
			url: this.url,
			params: {
				values:Ext.encode(this.changedValues)
			},
			callback : callback,
			scope:scope
		});
		this.changedValues=false;
	},

	// private
	clearValue : function(name){
		Ext.Ajax.request({
			url: this.url,
			params: {
				'name': name,
				'value': 'null'
			}
		});
	}
});