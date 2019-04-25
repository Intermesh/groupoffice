/**
 * Combo box
 * 
 * This will automatically load entities if a go.data.Store is used so it can 
 * display the text.
 * 
 * @type |||
 */
go.form.ComboBox = Ext.extend(Ext.form.ComboBox, {
	value: null,
	
	resolveEntity : function(value) {
		return this.store.entityStore.get([value]).then(function (entities) {
			if(entities[0]) {
				return entities[0];
			}else
			{
				return Promise.reject();
			}
		});
	},
	
	
	setValue: function (value) {
		
		var me = this, createRecord = function(data) {
			var comboRecord = Ext.data.Record.create(me.store.fields);
			var currentRecord = new comboRecord(data, data[me.valueField]);
			me.store.add(currentRecord);

			go.form.ComboBox.superclass.setValue.call(me, value);
		};

		//create record from entity store if not exists
		if (value && this.store.entityStore && this.store.entityStore.entity && !this.findRecord(me.valueField, value)) {

			this.value = value;
			
			this.resolveEntity(value).then(function (entity) {			

				if(Ext.isFunction(me.renderer)) {
					entity[me.displayField] = Ext.util.Format.htmlDecode(me.renderer(entity));
				}
				//var displayValue = Ext.isFunction(me.valueField) ? me.valueField(data) : data[me.valueField];
				createRecord(entity);
				
			}).catch(function(e) {
				console.error(e);
				var data = {};
				//console.warn("Invalid entity ID '" + value + "' for entity store '" + this.store.entityStore.entity.name + "'");
				//Set all record keys to prevent errors in XTemplates
				me.store.fields.keys.forEach(function(key) {
					data[key] = null;
				});
				data[me.valueField] = value;
				data[me.displayField] = t("Not found or no access!");
				createRecord(data);
			});
		} else
		{
			var text = value;
			if(this.valueField){
				 var r = this.findRecord(this.valueField, value);
				 if(r){
					 if(Ext.isFunction(this.renderer)) {
							r.data[this.displayField] = this.renderer(r.data);
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

	getParams: function (text) {
		//override to add q filter for JMAP API
		this.store.baseParams.filter = this.store.baseParams.filter || {};
		this.store.baseParams.filter.text = text;

		var p = go.form.ComboBox.superclass.getParams.call(this, text);
		delete p[this.queryParam];

		return p;
	}
});
