/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Combo.js 16895 2014-02-21 15:05:23Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */


/**
 * @class GO.form.ComboBox
 * @extends Ext.form.ComboBox
 * A combobox control with support for autocomplete, remote-loading, paging and many other features.
 * @constructor
 * Create a new ComboBox.
 * @param {Object} config Configuration options
 */
GO.form.ComboBox = Ext.extend(Ext.form.ComboBox, {

	minChars : 3,
	reloadOnExpand : false,


	initComponent : function(){



		GO.form.ComboBox.superclass.initComponent.call(this);

		if(this.reloadOnExpand){
			this.on('expand',function(field){
				field.store.reload();
			}, this);
		}

		if(this.remoteText){
			this.setRemoteText(this.remoteText);
		}
	},

	/**
	 * A combobox is often loaded remotely on demand. But you want to display the
	 * correct text even before the store is loaded. When a form loads I also
	 * supply the text and call this function to display it when the record is not
	 * available.
	 *
	 * @param {String} remote text
	 */
	setRemoteText : function(text)
	{
		
		
		//console.log(this.value);
		if(text && (this.value || this.value === 0) ){
			var r = this.findRecord(this.valueField, this.value);

			if(!r)
			{
				var comboRecord = Ext.data.Record.create([{
					name: this.valueField
				},{
					name: this.displayField
				}]);

				var recordData = {};

				if(this.store.fields && this.store.fields.keys){
					for(var i=0;i<this.store.fields.keys.length;i++){
						recordData[this.store.fields.keys[i]]="";
					}
				}

				recordData[this.valueField]=this.value;
				recordData[this.displayField]=text;

				var currentRecord = new comboRecord(recordData);
				this.store.add(currentRecord);


			}else
			{
				r.set(this.displayField,text);
			}
			this.setValue(this.value);
		}else{
			this.clearValue();
		}
	},

	/*
	 * Small override to help the setRemoteText value when it is called before
	 * rendering.
	 */

	initValue : function(){
		GO.form.ComboBox.superclass.initValue.call(this);
		
		if(!GO.util.empty(this.lastSelectionText))
			this.setRawValue(this.lastSelectionText);
	},
	
	setValue: function(v) {

		var text = v;
		
		if(this.valueField){
			var r = this.findRecord(this.valueField, v);
			if(r){
					text = Ext.util.Format.htmlDecode(r.data[this.displayField]);
			}else if(Ext.isDefined(this.valueNotFoundText)){
					text = this.valueNotFoundText;
			}
		}
		this.lastSelectionText = text;
		if(this.hiddenField){
				this.hiddenField.value = Ext.value(v, '');
		}
		Ext.form.ComboBox.superclass.setValue.call(this, text);

		this.value = v;
//		GO.form.ComboBox.superclass.setValue.call(this, v);
		
		if(this.idValuePair){
			if(!GO.util.empty(v)){
				v=v.split(':');
				if(v.length==2){
					this.setRawValue(v[1]);					
				}
			}
		} 
		
		return this;
	},
	
	assertValue : function(){
			var val = this.getRawValue(),
					rec;

			if(this.valueField && Ext.isDefined(this.value)){
					rec = this.findRecord(this.valueField, this.value);
			}
			if(!rec || Ext.util.Format.htmlDecode(rec.get(this.displayField)) != val){
					rec = this.findRecord(this.displayField, val);
			}
			if(!rec && this.forceSelection){
					if(val.length > 0 && val != this.emptyText){
							this.el.dom.value = Ext.value(this.lastSelectionText, '');
							this.applyEmptyText();
					}else{
							this.clearValue();
					}
			}else{
					if(rec && this.valueField){
							// onSelect may have already set the value and by doing so
							// set the display field properly.  Let's not wipe out the
							// valueField here by just sending the displayField.
							if (this.value == val){
									return;
							}
							val = rec.get(this.valueField || this.displayField);
					}
					this.setValue(val);
			}
	},
		
	/**
	 * Selects the first record of the associated store
	 */
	
	selectFirst : function(){
		if(this.store.getCount())
		{
			var records = this.store.getRange(0,1);
			this.setValue(records[0].get(this.valueField));
		}
	},

	/**
	 * Clears the last search action. Usefull when you change a baseParam of the
	 * combo store and the cache prevents you searching the server.
	 *
	 */
	clearLastSearch : function(){
		this.lastQuery=false;
		this.hasSearch=false;
	},
	
	setBoxLabel: function(boxLabel){
		this.boxLabel = boxLabel;
		if(this.rendered){
			this.wrap.child('.x-form-cb-label').update(boxLabel);
		}
	},
	
	setLabel: function(label){
		if(this.rendered){
			this.label.update(label+':');
		} else {
			this.label = label;
		}
	}		
});

Ext.reg('combo', GO.form.ComboBox);
