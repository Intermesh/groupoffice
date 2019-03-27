/**
 * Combo box
 * 
 * This will automatically load entities if a go.data.Store is used so it can 
 * display the text.
 * 
 * @type |||
 */
go.form.ComboBox = Ext.extend(Ext.form.ComboBox, {
	setValue: function (value) {
		var me = this;

		//create record from entity store if not exists
		if (value && this.store.entityStore && this.store.entityStore.entity && !this.findRecord(me.valueField, value)) {
			this.store.entityStore.get([value], function (entities) {

				var data = {};
				if (!entities[0]) {
					//console.warn("Invalid entity ID '" + value + "' for entity store '" + this.store.entityStore.entity.name + "'");
					//Set all record keys to prevent errors in XTemplates
					this.store.fields.keys.forEach(function(key) {
						data[key] = null;
					});
					data[me.valueField] = value;
					data[me.displayField] = t("Not found or no access!");
				} else
				{
					data = entities[0];
				}

				if(Ext.isFunction(me.displayRenderer)) {
					data[me.displayField] = Ext.util.Format.htmlDecode(me.displayRenderer(data));
				}
				//var displayValue = Ext.isFunction(me.valueField) ? me.valueField(data) : data[me.valueField];
		
				var comboRecord = Ext.data.Record.create(this.store.fields);
				var currentRecord = new comboRecord(data, data[me.valueField]);

				me.store.add(currentRecord);

				go.form.ComboBox.superclass.setValue.call(me, value);
			}, this);
		} else
		{
			var text = value;
			if(this.valueField){
				 var r = this.findRecord(this.valueField, value);
				 if(r){
					 if(Ext.isFunction(this.displayRenderer)) {
							r.data[this.displayField] = this.displayRenderer(r.data);
						}
					  text = r.data[this.displayField];
				 }else if(Ext.isDefined(this.valueNotFoundText)){
					  text = this.valueNotFoundText;
				 }
			}
			this.lastSelectionText = text;
			if(this.hiddenField){
				 this.hiddenField.value = Ext.value(value, '');
			}
			Ext.form.ComboBox.superclass.setValue.call(this, text);
			this.value = value;
			return this;
		}
	},
	/**
	 * Clears any text/value currently set in the field
	 */
	clearValue: function () {
		go.form.ComboBox.superclass.clearValue.call(this);
		this.value = null;
	},

	getParams: function (q) {
		//override to add q filter for JMAP API
		this.store.baseParams.filter = this.store.baseParams.filter || {};
		this.store.baseParams.filter.q = q;

		var p = go.form.ComboBox.superclass.getParams.call(this, q);
		delete p[this.queryParam];

		return p;
	}
});
