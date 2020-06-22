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

	minChars : 3,

	collapseOnSelect: true,

	// private
	onSelect : function(record, index){
		if(this.fireEvent('beforeselect', this, record, index) !== false){
			this.setValue(record.data[this.valueField || this.displayField]);
			if(this.collapseOnSelect) {
				this.collapse();
			}
			this.fireEvent('select', this, record, index);
		}
	},

	initComponent: function() {
		go.form.ComboBox.superclass.initComponent.call(this);

		//Set value promise can be used to do stuff after setvalue completed fetching the entity and loaded the store.
		this.setValuePromise = Promise.resolve(this);
	},
	
	resolveEntity : function(value) {
		return this.store.entityStore.single(value);
	},
	
	setValue: function (value) {

		var me = this;

		this.setValuePromise = new Promise(function(resolve, reject) {

			//hack for old framework where relations are "0" instead of null.
			if(value == "0" && me.store.entityStore) {
				value = null;
			}

			if(!me.hiddenName || !value) {
				resolve(me);
				return go.form.ComboBox.superclass.setValue.call(me, value);
			}

			//create record from entity store if not exists
			if (me.store.entityStore && me.store.entityStore.entity && !me.findRecord(me.valueField, value)) {

				me.value = value;

				me.resolveEntity(value).then(function (entity) {
					me.store.on("load", function() {
						resolve(me);
						go.form.ComboBox.superclass.setValue.call(me, value);
					}, me, {single: true});
					me.store.loadData({records:[entity]}, true);

				}).catch(function(e) {
					console.error(e);
					var data = {};
					//console.warn("Invalid entity ID '" + value + "' for entity store '" + me.store.entityStore.entity.name + "'");
					//Set all record keys to prevent errors in XTemplates
					me.store.fields.keys.forEach(function(key) {
						data[key] = null;
					});
					data[me.valueField] = value;
					data['customFields'] = {}; //to avoid errors in templates
					data[me.displayField] = t("Not found or no access!");

					me.store.on("load", function() {
						go.form.ComboBox.superclass.setValue.call(me, value);
					}, me, {single: true});
					me.store.loadData({records:[data]}, true);
					//go.form.ComboBox.superclass.setValue.call(me, value);
					resolve(me);
				});
			} else
			{
				var text = value;
				if(me.valueField){
					 var r = me.findRecord(me.valueField, value);
					 if(r){
						 if(Ext.isFunction(me.renderer)) {
								r.data[me.displayField] = me.renderer(r.data);
							}
							text = r.data[me.displayField];
					 }else if(Ext.isDefined(me.valueNotFoundText)){
							text = me.valueNotFoundText;
					 }
				}
				me.lastSelectionText = text;
				if(me.hiddenField){
					 me.hiddenField.value = Ext.value(value, '');
				}
				Ext.form.ComboBox.superclass.setValue.call(me, text);
				me.value = value;

				resolve(me);
				return me;
			}
		});

		return this;
	},
	/**
	 * Clears any text/value currently set in the field
	 */
	clearValue: function () {
		go.form.ComboBox.superclass.clearValue.call(this);
		this.value = null;
	},

	getParams: function (text) {
		//override to add 'text' filter for JMAP API
		this.store.setFilter('combotext', {text: text});

		if(this.pageSize > 0){
			this.store.baseParams.calculateTotal = true;
		} else {
			delete(this.store.baseParams.calculateTotal);
		}

		var p = go.form.ComboBox.superclass.getParams.call(this, text);
		delete p[this.queryParam];

		return p;
	}
});
Ext.reg('gocombo', go.form.ComboBox);