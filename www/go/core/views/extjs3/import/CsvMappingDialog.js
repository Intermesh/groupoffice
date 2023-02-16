go.import.CsvMappingDialog = Ext.extend(go.Window, {
	modal: true,
	entity: null,
	blobId: null,
	values: null,
	
	
	width: dp(800),
	height: dp(900),
	title: t("Import Comma Separated values"),
	autoScroll: true,

	newProfileRecord: null, // set and added to store on reload when new profile record needs to be created
	
	initComponent : function() {

		const renameWindow = new go.Window({
			title: t('Rename profile'),
			cls:'go-form-panel',
			layout:'form',
			width: 400,
			mode: 'r',
			listeners: {'show': () => this.nameField.focus()},
			items: [this.nameField = new Ext.form.TextField({
				//xtype:'textfield',
				anchor: '100%',
				fieldLabel: t('Name'),
				name: 'saveName',
				hintText: t('Enter a name to save the column mapping')
			})],
			buttons: ['->',{
				text: t('Ok'),
				handler: () => {
					switch(renameWindow.mode) {
						case 'r': this.renameCurrent(this.nameField.getValue()); break;
						case 'c': this.copyTo(this.nameField.getValue()); break;
					}
					renameWindow.hide();
				}
			}]
		}),
			profileMenu = new Ext.menu.Menu({
				items: [{
					text:t('Rename')+'…',
					itemId: 'rename',
					handler: () => {
						renameWindow.setTitle(t('Rename profile'));
						renameWindow.mode = 'r'; // rename
						renameWindow.show();
					}
				},{
					text: t('Copy as')+'…',
					itemId: 'copy',
					handler: () => {
						renameWindow.setTitle(t('Copy profile as'));
						renameWindow.mode = 'c'; // copy
						renameWindow.show();
					}
				},{
				// 	itemId: 'update',
				// 	text: t('Update'),
				// 	handler: () => {
				// 		const id = this.csvMappings.getValue();
				// 		this.csvMappings.store.entityStore.update(_[id]:_);
				// 	}
				// },{
					itemId: 'delete',
					text: t('Delete'),
					handler: () => {
						const id = this.csvMappings.getValue();
						this.csvMappings.store.entityStore.destroy(id).then(() => {
							this.csvMappings.store.reload();
						});
					}
				}]
			});

		this.formPanel = new Ext.form.FormPanel({
			
			items: [
				this.fieldSet = new Ext.form.FieldSet({
					labelWidth: dp(300),
					items: [{
						xtype: 'box',
						autoEl: 'p',
						html: t("Please match the CSV columns with the correct Group-Office columns and press 'Import' to continue.")+
							'<br>'+t('The column profile will be saved with the name provided field below.')
					},this.csvMappings= new go.form.ComboBoxReset({
						fieldLabel: t("Column profile"),
						anchor: '99%',
						pageSize: 50,
						trigger1Class: 'ic-edit',
						onTrigger1Click: function(ev,btn) {
							const isNew = (this.getValue()=='new');
							profileMenu.get('delete').setDisabled(isNew);
							profileMenu.get('copy').setDisabled(isNew);
							//profileMenu.get('rename').setDisabled(!isNew);
							profileMenu.show(btn);
						},
						valueField: 'id',
						submit:false,
						hiddenName: 'mappingId',
						displayField: 'name',
						triggerAction: 'all',
						editable: false,
						selectOnFocus: true,
						forceSelection: true,
						listeners: {
							'render' : me => me.store.load(),
							'setvalue' : (me,v) => {
								console.log(v);
								if(v) {
									if(typeof v === "number" || v === 'new') {
										const rec = me.store.getById(v);
										this.formPanel.form.setValues(rec.data.columnMapping); // do column mapping
										v = rec.data.name;
									}
									//todo: column mapping

									this.nameField.setValue(v);
								}
							}
							// 'select' : (me,rec) => {
							// 	this.nameField.setValue(rec.data.name);
							// }
						},
						store: {
							xtype: "gostore",
							fields: ['id', 'name', 'columnMapping'],
							entityStore: "ImportMapping",
							filters: {
								default: {entity: this.entity}
							},
							listeners: {
								'load': s => {
									if(this.foundId)
										this.csvMappings.setValue(this.foundId);
									if(this.newProfileRecord) {
										this.csvMappings.store.insert(0, this.newProfileRecord);
										this.csvMappings.setValue('new');
									}
									//this.csvMappings.setValue();
								}
							}
						}
					}),
					{html:'<hr>'},
					this.createLookupCombo()]
				})
			]
		});
		
		this.items = [this.formPanel];
		
		this.buttons = [
			// {
			// 	text: t("Save mapping"),
			// 	handler: function() {
			// 		const mapping = this.formPanel.form.getFieldValues();
			//
			// 		console.warn(mapping);
			// 	},
			// 	scope: this
			// },
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

				if(response.columnMapping) { // mapping found!
					this.foundId = response.id;
					this.formPanel.form.setValues(response.columnMapping);
					this.formPanel.form.setValues({updateBy: response.updateBy});
				} else { // columns unknown, generate
					var v = this.transformCsvHeadersToValues(response.goHeaders, this.fields);
					this.foundId = 0;
					this.newProfileRecord = new this.csvMappings.store.recordType({
						name: this.fileName,
						columnMapping:v,
						id:'new'
					},'new');
					Ext.apply(v, this.findAliases());
					this.formPanel.form.setValues(v);
				}
				
				this.doLayout();
			},
			scope: this
		});
	},

	copyTo(name) {
		const current = this.csvMappings.store.getById(this.csvMappings.getValue());
		this.newProfileRecord = new this.csvMappings.store.recordType({
			name: name,
			columnMapping:current.get('columnMapping'),
			id:'new'
		},'new');
		if(this.newProfileRecord) {
			this.csvMappings.store.insert(0, this.newProfileRecord);
			this.csvMappings.setValue('new');
		}
	},

	renameCurrent(name) {
		const rec = this.csvMappings.store.getById(this.csvMappings.getValue());
		rec.set('name',name);
		this.csvMappings.setRawValue(name);
		// Import action will save the rename so no SET action needed here
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

		return v;
	},


	/**
	 * This will generate the form values for the mapping dialog.
	 *
	 * @param goHeaders
	 * @param parent
	 * @returns {{}}
	 *
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
			var csvHeader = r.data.name.toLowerCase().replace(/[_\-\s]/g, '');
			var goHeader = (parent ? parent + "." + h.name : h.name).toLowerCase().replace(/[_\-\s]/g, '');

			return csvHeader == goHeader || "customfields." + csvHeader == goHeader;
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
					labelWidth: dp(300),
					hideLabel: true,
					defaults: {
						anchor: "100%"
					},
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
						anchor: "100%",
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
		var mappingId = this.csvMappings.getValue();
		var updateBy = mapping.updateBy;
		var saveName = this.nameField.getValue();

		delete mapping.updateBy;

		go.Jmap.request({
			method: this.entity + "/import",
			params: {
				blobId: this.blobId,
				values: this.values,
				mappingId,
				mapping,
				saveName,
				updateBy
			},
			callback: function (options, success, response) {
				this.getEl().unmask();
				if (!success) {
					if(response.errors) {
						Ext.MessageBox.alert(t("Error"), response.errors.join("<br />"));
					} else {
						Ext.MessageBox.alert(t("Error"), response.message);
					}
				} else {
					var msg = t("Imported {count} items").replace('{count}', response.count) + ". ";
					if(response.errors && response.errors.length) {
						msg += t("{count} items failed to import. A log follows: <br /><br />").replace('{count}', response.errors.length) + response.errors.join("<br />");
					}
						
					Ext.MessageBox.alert(t("Success"), msg);

					go.Db.store(this.entity).getUpdates();
				}

				this.close();
			},
			scope: this
		});
	}
});
