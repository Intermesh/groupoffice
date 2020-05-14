/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SearchField.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * @class GO.form.SearchField
 * @extends Ext.form.TriggerField
 * Search text field that will add a query parameter to a Datastore automatically
 * @constructor
 * Creates a new SearchField
 * @param {Object} config Configuration options
 */
GO.form.SearchField = Ext.extend(Ext.form.TwinTriggerField, {
	/**
	 * @cfg {Number} store The data store to add the query too
	 */
	store : false,
	initComponent : function(){
		GO.form.SearchField.superclass.initComponent.call(this);
		this.on('specialkey', function(f, e){
			if(e.getKey() == e.ENTER){
				this.onTrigger2Click();
			}
		}, this);
        
		this.on('focus', function(){
			this.focus(true);
		}, this);

		this.addEvents({reset:true,search:true});
	},
	spellCheck: false,
	validationEvent:false,
	validateOnBlur:false,
	trigger1Class:'x-form-clear-trigger',
	trigger2Class:'x-form-search-trigger',
	//hideTrigger1:true,
	width:180,
	hasSearch : false,
	paramName : 'query',
	emptyText: t("Search"),

	onTrigger1Click : function(){		
		if(this.hasSearch){
			this.store.baseParams[this.paramName]='';			
			this.el.dom.value = '';
			this.hasSearch = false;
			if(this.fireEvent('reset', this)!==false)
				this.store.load();
		}		
	},

	onTrigger2Click : function(){
		this.fireEvent('search', this);

		var v = this.getRawValue();
		if(v.length < 1){
			this.onTrigger1Click();
			return;
		}
		this.store.baseParams[this.paramName]=v;
		this.store.load();
		this.hasSearch = true;
	},
	afterRender:function(){
		GO.form.SearchField.superclass.afterRender.call(this);
		if(Ext.isIE8)this.el.setTop(0);
	},
	setValue : function(v){
		GO.form.SearchField.superclass.setValue.call(this, v);
		if(v!='')
		{
			this.hasSearch=true;
		}
	}
});

Ext.reg('searchfield', GO.form.SearchField);
