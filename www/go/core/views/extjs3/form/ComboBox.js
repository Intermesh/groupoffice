/**
 * Combo box
 * 
 * This will automatically load entities if a go.data.Store is used so it can 
 * display the text.
 * 
 * @example
 * go.modules.community.addressbook.AddresBookCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Address book"),
	hiddenName: 'addressBookId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	listeners: {

		//example of handling default value missing
		valuenotfound: function(cmp, id) {
			if(id == go.User.notesSettings.defaultNoteBookId) {

				GO.errorDialog.show("Your default notebook wasn't found. Please select a notebook and it will be set as default.");

				cmp.setValue(null);

				cmp.on('change', function(cmp, id) {
					go.Db.store("User").save({
						notesSettings: {defaultNoteBookId: id}
					}, go.User.id);
				}, {single: true});
			}
		},
		scope: this
	},
	store: {
		xtype: "gostore",
		fields: ['id', 'name'],
		entityStore: "AddressBook",
		filters: {
			default: {
					permissionLevel: go.permissionLevels.write
			}
		}
	}
});

Ext.reg("addressbookcombo", go.modules.community.addressbook.AddresBookCombo);
 */
go.form.ComboBox = Ext.extend(Ext.form.ComboBox, {
	value: null,

	minChars : 3,

	collapseOnSelect: true,

	/**
	 * Group on this field
	 */
	groupField: false,

	allowNew: undefined,

	createDialog: undefined,

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

	preInitComp : function() {
		if(!this.tpl) {
			this.tpl =
				'<tpl for=".">';
				if(this.groupField) {
					this.tpl += '<tpl>{[this.resetCurrentKey()]}</tpl>'+
					'<tpl for=".">'+
					'<tpl if="this.shouldShowHeader(go.util.Object.fetchPath(values, \'' + this.groupField + '\'))">' +
					'<div class="x-combo-list-group">{[this.showHeader(go.util.Object.fetchPath(values, \'' + this.groupField + '\'))]}</div>' +
					'</tpl>' ;
				}

			this.tpl +=	'<div class="x-combo-list-item" title="{[fm.htmlEncode(values[\'' + this.displayField + '\'] || \'\' )]}">';

			if(this.allowNew) {
				this.tpl += '<tpl if="!values.' + this.valueField + '"><b>' + t("Create new") + ':</b> </tpl>';
			}

			this.tpl += '{[fm.htmlEncode(values["' + this.displayField + '"] || "" )]}</div>';

			this.tpl +=	'</tpl>';


			if(this.groupField) {
				this.tpl = new Ext.XTemplate( this.tpl, {
					shouldShowHeader: function(header){
						console.warn(header);
						return this.currentKey !== header;
					},
					showHeader: function(header){
						this.currentKey = header;
						return this.currentKey;
					},
					resetCurrentKey: function() {
						this.currentKey=null;
						return '';
					}
				});
			}
		}
	},

	initComponent: function() {

		this.preInitComp();

		go.form.ComboBox.superclass.initComponent.call(this);

		this.postInitComp();

	},

	selectFirst : async function() {
		if(!this.store.loaded) {
			await this.store.load();
			return this.selectFirst();
		}
		if(!this.store.getCount()) {
			return null;
		}

		const firstRecord = this.store.getAt(0);
		const v = firstRecord.get(this.valueField)
		this.setValue(v);

		return v;

	},

	postInitComp : function() {
		//Set value promise can be used to do stuff after setvalue completed fetching the entity and loaded the store.
		this.setValuePromise = Promise.resolve(this);

		if(this.allowNew) {
			this.store.on("load", this.addCreateNewRecord, this);
			this.on('beforeselect', function(combo, record, index) {
				if(!record.data[this.valueField]) {

					this.createNew(record).then(function() {

						var record = combo.store.getAt(0);

						console.warn(record);
						combo.fireEvent('beforeselect', combo, record, 0);
						combo.fireEvent('select', combo, record, 0);


						// delay focus otherwise it will expand becuase store loads after creating new entity
						setTimeout(() => {
							combo.focus();
						}, 100)

						// combo.hasFocus = true;
					});

					//cancel select and fire it after create.
					return false;
				}
				return true;
			}, this);
		}
	},


	addCreateNewRecord: function() {
		if(!this.isExpanded()) {
			return;
		}
		var text =  this.getRawValue();
		if(!text) {
			return;
		}
		var def = Ext.data.Record.create([{
			name: this.valueField
		},{
			name: this.displayField
		}]);

		var recordData = {};
		recordData[this.displayField] = text;
		recordData[this.valueField] = null;
		var record = new def(recordData);
		this.store.insert(0, record);

		if(this.store.getCount() > 1) {
			this.select(0);
		}
	},

	// private
	assertValue : function(){

		var val = this.getRawValue(),
			rec;

		if(this.valueField && Ext.isDefined(this.value)){
			rec = this.findRecord(this.valueField, this.value);
		}
		if(!rec || rec.get(this.displayField) != val){
			rec = this.findRecord(this.displayField, val);
		}
		//filter out create new record
		if((!rec || rec.get(this.valueField)===null) && this.forceSelection){
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

	// expand : function() {
	// 	debugger;
	// 	return go.form.ComboBox.superclass.expand.call(this);
	// },

	createNew : function(record) {

		const entity = record.data;
		delete entity[this.valueField]; //remove -1 id.
		if(Ext.isObject(this.allowNew)) {
			Ext.apply(entity, this.allowNew);
		}

		if(this.fireEvent("beforecreatenew", this, entity) === false) {
			return;
		}


		this.collapse();
		this.store.loaded = false;
		this.store.removeAll();
		//Clear text input or it will recreate fake record.
		this.setRawValue("");

		if(this.createDialog) {

			this.hasFocus = false;

			const dlg = new this.createDialog;
			dlg.restoreFocusOnClose = false;
			dlg.redirectOnSave = false;
			dlg.setValues(entity);
			dlg.show();

			const p= new Promise((resolve, reject) => {

				let saved = false;
				dlg.on("save", () => {
					saved = true;
					this.setValue(dlg.currentId);
					resolve(dlg.getValues());
				}, {single: true})

				dlg.on("close", () => {
					if(!saved) {
						this.reset();
						reject();
					}
				}, {single: true})
			})

			return p.then(() => {
				return this.setValuePromise;
			})

		} else {
			return this.store.entityStore.save(entity).then((entity) => {

				this.setValue(entity.id);
				return this.setValuePromise;
			}).catch((error) => {
				GO.errorDialog.show(error.message);
				return Promise.reject(error.message);
			});
		}
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

			if(!value) {
				resolve(me);
				go.form.ComboBox.superclass.setValue.call(me, value);
				me.clearInvalid();

				return;
			}

			//create record from entity store if not exists
			if (me.store && me.store.entityStore && me.store.entityStore.entity && !me.findRecord(me.valueField, value)) {

				me.value = value;

				me.resolveEntity(value).then(function (entity) {
					//this prevents the list to expand on loading the value
					var origHasFocus = me.hasFocus;
					if(me.value != value) {
						// Abort another setValue() call was made in between. This can happen when fetching the default
						// value takes longer then the setValue() call of loading a dialog value.
						return;
					}

					me.store.on("load", function() {

						go.form.ComboBox.superclass.setValue.call(me, value);

						me.clearInvalid();

						me.hasFocus = origHasFocus;
						resolve(me);
					}, me, {single: true});

					me.hasFocus = false;
					me.store.loadData({records:[entity]}, false);

				}).catch(function(e) {

					if(me.isDestroyed) {
						resolve(me);
						return;

					}
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

						me.fireEvent("valuenotfound", this, value);
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

						 me.fireEvent("valuenotfound", this, value);
					 }
				}
				me.lastSelectionText = text;
				if(me.hiddenField){
					 me.hiddenField.value = Ext.value(value, '');
				}
				Ext.form.ComboBox.superclass.setValue.call(me, text);
				me.value = value;
				me.clearInvalid();

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