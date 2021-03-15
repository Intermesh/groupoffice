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
					}, this.createLookupCombo()]
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

		this.fieldLabelsToAliases();
		
		
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

				this.fieldSet.add(this.createMappingFields(response.goHeaders, this.fields));

				var v = this.transformCsvHeadersToValues(response.goHeaders, this.fields);
				Ext.apply(v, this.findAliases());
				this.formPanel.form.setValues(v);
				
				this.doLayout();
			},
			scope: this
		});
	},

	fieldLabelsToAliases : function() {
		for(var propName in this.fields) {
			if(!this.aliases[this.fields[propName].label]) {
				this.aliases[this.fields[propName].label] = propName;
			}
		}
	},

	createLookupCombo: function() {
		var storeData = [[null, t("Don't update")]];

		for(var field in this.lookupFields) {
			storeData.push([field, this.lookupFields[field]]);
		}

		var store = new Ext.data.ArrayStore({
			fields: ['field', 'label'],
			data: storeData
		});

		return new go.form.ComboBox({
			hiddenName: "updateBy",
			store: store,
			valueField: "field",
			displayField: "label",
			mode:"local",
			triggerAction: "all",
			fieldLabel: t("Update existing items by"),
			value: null
		});
	},


	findAliases: function() {

		var v = {};

		for(var a in this.aliases) {
			var index = this.csvHeaders.findIndex(function(h) {
				return h.toLowerCase() == a.toLowerCase();
			});
			if(index == -1) {
				continue;
			}
			var aliasCfg = this.aliases[a];
			if(Ext.isString(aliasCfg)) {
				v[aliasCfg] = {csvIndex: index, fixed: null}
				go.util.Object.applyPath(v, aliasCfg,{csvIndex: index, fixed: null});
				continue;
			}

			var child = go.util.Object.applyPath(v, aliasCfg.field,{csvIndex: index, fixed: null});

			if(aliasCfg.fixed) {
				for(var field in aliasCfg.fixed) {
					child[field] = {csvIndex: -1, fixed: aliasCfg.fixed[field]};
				}
			}

			if(aliasCfg.related) {
				for(var field in aliasCfg.related) {
					var index = this.csvHeaders.findIndex(function(h) {
						return h.toLowerCase() == aliasCfg.related[field].toLowerCase();
					});
					if(index > -1) {
						child[field] = {csvIndex: index, fixed: null};
					}
				}
			}
		}

		console.warn(v);

		return v;
	},


	/**
	 * This will generate the form values for the mapping dialog.
	 *
	 * @param goHeaders
	 * @param parent
	 * @returns {{}}
	 *
	 *
	 * BROKEN!
	 */
	transformCsvHeadersToValues : function(goHeaders, fields, parent) {
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
						v[h.name][headerIndex] = this.transformCsvHeadersToValues(h.properties, fields, part);
					}
				}else
				{
					v[h.name] = this.transformCsvHeadersToValues(h.properties, fields, h.name);
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

	createMappingFields : function(headers, fields, parent) {
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
					items: this.createMappingFields(h.properties, fields[h.name] ? fields[h.name].properties : {}, parent ? parent + "." + h.name : h.name)
				};

				if(h.many) {


					var field = {
						name: h.name,
						xtype: "formgroup",
						hideLabel: true,
						title: fields[h.name] ? fields[h.name].label : h.label || h.name,
						itemCfg: formContainer
					};
				} else {
					formContainer.name = h.name;
					var field = {
						xtype: "fieldset",
						hideLabel: true,
						title: fields[h.name] ? fields[h.name].label : h.label || h.name,
						items: [formContainer]
					};
				}

			} else {

				var field = {
					xtype: "formcontainer",
					labelAlign: "top",
					layout: 'hbox',
					name: h.name,
					fieldLabel: fields[h.name] ? fields[h.name].label : (h.label || h.name),
					items: [this.createCombo({
						store: this.csvStore,
						hiddenName: "csvIndex",
						setValue: function(v) {
							this.ownerCt.items.itemAt(1).items.itemAt(0).setVisible(v == -1);
							return go.form.ComboBox.prototype.setValue.call(this, v);
						},
						listeners: {
							change: function(combo, v) {
								combo.ownerCt.items.itemAt(1).items.itemAt(0).setVisible(v == -1);
							}
						}
					}),{
						xtype:"container",
						items: [
							{
								xtype: "textfield",
								name: "fixed",
								placeholder: t("Fixed value"),
								hideMode: "visibility"
							}
						]
					} ]
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
			data: [[-2, ""], [-1, t("Fixed value")]].concat(headers.map(function(h) {
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
			editable:true,
			forceSelecton : true,
			typeAhead: true
			});
	},
	doImport: function() {
		this.getEl().mask(t("Importing..."));
		var mapping = this.formPanel.form.getFieldValues();
		var updateBy = mapping.updateBy;
		delete mapping.updateBy;

		go.Jmap.request({
			method: this.entity + "/import",
			params: {
				blobId: this.blobId,
				values: this.values,
				mapping: mapping,
				updateBy: updateBy
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
