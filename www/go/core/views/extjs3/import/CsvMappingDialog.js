go.import.CsvMappingDialog = Ext.extend(go.Window, {
	modal: true,
	entity: null,
	blobId: null,
	values: null,
	
	
	width: dp(800),
	height: dp(900),
	title: t("Import Comma Separated values"),
	autoScroll: true,
	
	
	initComponent : function() {
		
		this.formPanel = new Ext.form.FormPanel({
			
			items: [
				this.fieldSet = new Ext.form.FieldSet({
					labelWidth: dp(200),
					items: [{
							xtype: 'box',
							autoEl: 'p',
							html: t("Please match the CSV columns with the correct Group-Office columns and press 'Import' to continue.")
					}]
				})
			]
		});
		
		this.items = [this.formPanel];
		
		this.buttons = [
			'->',
			this.importButton = new Ext.Button({
				cls: 'raised',
				text: t("Import"),
				handler: this.doImport,
				scope: this
			})
		];
		
		go.import.CsvMappingDialog.superclass.initComponent.call(this);
		
		
		go.Jmap.request({
			method: this.entity + '/importCSVMapping',
			params: {
				blobId: this.blobId
			},
			callback: function(options, success, response) {
				
				if(!success) {
					Ext.MessageBox.alert(t("Error"), response.message);
					return;
				}
				this.csvStore = this.createCsvHeaderStore(response.csvHeaders);
				this.csvHeaders = response.csvHeaders;
				// this.goHeaders = response.goHeaders;




				this.fieldSet.add(this.createMappingFields(response.goHeaders, this.labels));

				var v = this.transformCsvHeadersToValues(response.goHeaders);
				this.formPanel.form.setValues(v);
				
				this.doLayout();
			},
			scope: this
		});
	},

	transformCsvHeadersToValues : function(goHeaders, parent) {
		var v = {};

		for(var name in goHeaders) {
			var h = goHeaders[name];

			if(h.grouped) {
				if(h.many) {
					v[h.name] = [];
					var index = 1;
					for (index = 1; index < 10; index++) {
						var part = h.name + "[" + index + "]";
						part = parent ? parent + "." + part : part;

						var headerIndex = this.csvHeaders.findIndex(function (csvH) {
							return csvH.toLowerCase().indexOf(part.toLowerCase()) == 0;
						});

						if (headerIndex == -1) {
							break;
						}
						headerIndex--;
						v[h.name][headerIndex] = this.transformCsvHeadersToValues(h.properties, part);
					}
				}else
				{
					v[h.name] = this.transformCsvHeadersToValues(h.properties, h.name);
				}
			} else
			{
				v[h.name] = this.findSingleCsvIndex(h, parent);
			}
		};

		return v;
	},

	findSingleCsvIndex : function(h, parent) {
		var csvIndex = -2;
		var storeIndex = this.csvStore.findBy(function(r) {
			var find = r.data.name.toLowerCase();
			return find == (parent ? parent + "." + h.name : h.name).toLowerCase();
		});
		if(storeIndex > -1){
			csvIndex = this.csvStore.getAt(storeIndex).data.index;
		}

		return {csvIndex: csvIndex, fixed: null};
	},

	createMappingFields : function(headers, labels, parent) {
		// var index = 0;
		var items = [];
		// headers.forEach(function(h) {
		for(var name in headers) {
			var h = headers[name];
			if(!go.util.empty(h.properties)) {
				var formContainer = {
					xtype: "formcontainer",
					labelWidth: dp(200),
					hideLabel: true,
					items: this.createMappingFields(h.properties, labels[h.name] ? labels[h.name].properties : {}, parent ? parent + "." + h.name : h.name)
				};

				if(h.many) {


					var field = {
						name: h.name,
						xtype: "formgroup",
						hideLabel: true,
						title: labels[h.name] ? labels[h.name].label : h.label || h.name,
						itemCfg: formContainer
					};
				} else {
					formContainer.name = h.name;
					var field = {
						xtype: "fieldset",
						hideLabel: true,
						title: labels[h.name] ? labels[h.name].label : h.label || h.name,
						items: [formContainer]
					};
				}

			} else {

				var field = {
					xtype: "formcontainer",
					labelAlign: "top",
					layout: 'hbox',
					name: h.name,
					fieldLabel: this.labels[h.name] || h.label || h.name,
					items: [this.createCombo({
						store: this.csvStore,
						hiddenName: "csvIndex"
					}), {
						xtype: "textfield",
						name: "fixed",
						placeholder: t("Fixed value")
					}]
				}
			}

			items.push(field);
		};

		return items;
	},

	createCsvHeaderStore : function(headers) {
		var store = new Ext.data.ArrayStore({
			fields: ['index', 'name'],
			idField: 0,
			data: [[-2, t("Empty")], [-1, t("Fixed value")]].concat(headers.map(function(h) {
				// var label = me.labels[h.name] || h.label || h.name;
				return [headers.indexOf(h), h];
			}))
		});

		return store;
	},
	
	createCombo : function(config) {
		return Ext.apply(config,{
				xtype: "gocombo",
				displayField:'name',
				valueField:	'index',
				mode: 'local',
				triggerAction: 'all',
				editable:false
			});
	},
	getMapping : function() {
		var mapping = this.formPanel.form.getFieldValues();
		return mapping;
	},
	doImport: function() {
		this.getEl().mask(t("Importing..."));
		go.Jmap.request({
			method: this.entity + "/import",
			params: {
				blobId: this.blobId,
				values: this.values,
				mapping: this.getMapping()
			},
			callback: function (options, success, response) {
				this.getEl().unmask();
				if (!success) {
					Ext.MessageBox.alert(t("Error"), response.errors.join("<br />"));
				} else
				{
					if (!success) {
						Ext.MessageBox.alert(t("Error"), response.errors.join("<br />"));
					} else
					{
						var msg = t("Imported {count} items").replace('{count}', response.count) + ". ";

						if(response.errors && response.errors.length) {
							msg += t("{count} items failed to import. A log follows: <br /><br />").replace('{count}', response.errors.length) + response.errors.join("<br />");
						}
						
						Ext.MessageBox.alert(t("Success"), msg);
					}

					this.close();
				}

				// if (this.callback) {
				// 	this.callback.call(this.scope || this, response);
				// }
				
				this.close();
			},
			scope: this
		});
	}
});
