/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PlainField.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * @class GO.form.PlainField
 * @extends Ext.Component
 * Base class to easily display simple text in the form layout.
 * @constructor
 * Creates a new PlainField Field 
 * @param {Object} config Configuration options
 */


GO.form.PlainField = Ext.extend(Ext.form.Field, {


	// private
	defaultAutoCreate: {
		tag: 'div',
		cls: 'x-form-plainfield'
	},

	//Doesn't send value by default!
	submit: false,

	// private
	initComponent: function() {
		GO.form.PlainField.superclass.initComponent.call(this);
		
		if(this.boxLabel && GO.util.empty(this.fieldLabel))
		{
			this.fieldLabel=this.boxLabel;
			this.hideLabel=false;
		}
		
		this.addEvents(
			/**
			 * @event load
			 * Fires when the content is loaded into the field
			 * @param {GO.form.PlainField} this
			 * @param {Object} file
			 */
			'load'
			);
	},
	
	getName: function(){
		return this.name || '';
	},

	// private
	initValue : function(){
		if(this.value !== undefined){
			this.setValue(this.value);
		}else if(this.el.dom.innerHTML.length > 0){
			this.setValue(this.el.dom.value);
		}
		// reference to original value for reset
    this.originalValue = this.getValue();
	},

	getValue : function(){
		return this.value;
	},

	focus : function() {
		//overridden because selectText is not supported
		GO.form.PlainField.superclass.focus.call(this);
	},
	
	setValue : function(v){
		
		if(this.boxLabel)
		{
			if(v=='1')
			{
				v = t("Yes");
			}else
			{
				v = t("No");
			}
		}
		
		this.value = v;
		if(this.rendered){
			this.el.update(v);
		}
	}

});
Ext.reg('plainfield', GO.form.PlainField);
