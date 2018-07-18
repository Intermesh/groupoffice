(function () {
	var splitKeyValue = function (value, values) {
		if (!value) {
			return "";
		}
		var parts = value.split(':');
		parts.shift();

		return parts.join(':');
	};

	GO.customfields.dataTypes = {
		"GO\\Customfields\\Customfieldtype\\User": {
			icon: 'account_box',
			label: t("User"),
			getFormField: function (customfield, config) {
				var f = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply({
					xtype: 'selectuser',
					idValuePair: true,
					startBlank: true,
					forceSelection: true,
					hiddenName: 'customFields.' + customfield.databaseName,
					name: null,
					anchor: '-20',
					valueField: 'cf'
				}, config));

				return GO.customfields.dataTypes.applySuffix(customfield, f);
			}
		},
		"GO\\Customfields\\Customfieldtype\\Datetime": {
			icon: 'scheduler',
			label: 'Date time',
			render: function (value, data) {
				
				if(!value) {
					return "";
				}
				
				var date = Date.parseDate(value, 'c');
				if(date) {
					return date.format(GO.settings.date_format + " " + GO.settings.time_format)
				} else
				{
					//old framework that has already formatted the date on the server.
					return value;
				}
				
			},
			getFormField: function (customfield, config) {

				config = config || {};
				if (!config.serverFormats) {
					config.hiddenFormat = "c";
				}

				var f = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply({
					xtype: 'datetime',
					width: 300,
					anchor: null
				}, config));

				return GO.customfields.dataTypes.applySuffix(customfield, f);

			}
		},

		"GO\\Customfields\\Customfieldtype\\Date": {
			icon: 'scheduler',
			label: 'Date',
			render: function (value, data) {
				if(!value) {
					return "";
				}
				
				var date = Date.parseDate(value, 'c');
				if(date) {
					return date.format(GO.settings.date_format + " " + GO.settings.time_format)
				} else
				{
					//old framework that has already formatted the date on the server.
					return value;
				}
			},
			getFormField: function (customfield, config) {

				var f = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply({
					xtype: 'datefield',
					format: GO.settings['date_format'],
					anchor: null,
					width: 120
				}, config));

				return GO.customfields.dataTypes.applySuffix(customfield, f);
			}
		},

		"GO\\Customfields\\Customfieldtype\\Number": {
			icon: 'format_list_numbered',
			label: 'Number',
			getFormField: function (customfield, config) {


				var f = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply({
					xtype: 'numberfield',
					decimals: customfield.options.numberDecimals,
					width: 120,
					name: 'customFields.' + customfield.databaseName,
					anchor: null
				}, config));

				return GO.customfields.dataTypes.applySuffix(customfield, f);
			}
		},
		"GO\\Customfields\\Customfieldtype\\Checkbox": {
			icon: 'check',
			label: 'Checkbox',
			getFormField: function (customfield, config) {


				var f = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply({
					xtype: 'xcheckbox',
					boxLabel: customfield.name,
					hideLabel: true,
					fieldLabel: ""
				}, config));

				return GO.customfields.dataTypes.applySuffix(customfield, f);
			},
			render: function (value, data) {
				return value ? window.t('Yes') : window.t('No');
			}
		},
		"GO\\Customfields\\Customfieldtype\\BinaryCombobox": {
			icon: 'check',
			label: 'Binary Combobox',
			getFormField: function (customfield, config) {

				var f = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply({
					xtype: 'combo',
					fieldLabel: customfield.name,
					hiddenName: 'customFields.' + customfield.databaseName,
					store: new Ext.data.ArrayStore({
						storeId: 'binaryStore',
						idIndex: 0,
						fields: ['value', 'label'],
						data: [
							['0', t("No")],
							['1', t("Yes")]
						]
					}),
					valueField: 'value',
					displayField: 'label',
					mode: 'local',
					allowBlank: false,
					triggerAction: 'all'
				}, config));

				return GO.customfields.dataTypes.applySuffix(customfield, f);
			}
		},
		"GO\\Customfields\\Customfieldtype\\Textarea": {
			icon: 'description',
			label: 'Textarea',
			getFormField: function (customfield, config) {
				if (!customfield.options.height)
				{
					customfield.options.height = 40;
				}

				var f = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply(config, {
					xtype: 'textarea',
					height: parseInt(customfield.options.height),
					maxLength: Number.MAX_VALUE
				}));

				return GO.customfields.dataTypes.applySuffix(customfield, f);
			}
		},
		"GO\\Customfields\\Customfieldtype\\Html": {
			icon: 'description',
			label: 'HTML',
			getFormField: function (customfield, config) {
				var f = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply(config, {
					xtype: 'xhtmleditor',
					height: 200,
					maxLength: Number.MAX_VALUE
				}));

				return GO.customfields.dataTypes.applySuffix(customfield, f);
			}
		},
		"GO\\Customfields\\Customfieldtype\\Select": {
			icon: 'list',
			label: 'Select',
			getFormField: function (customfield, config) {
				var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);
				var store = new GO.data.JsonStore({
					//url: GO.settings.modules.customfields.url+'json.php',
					url: GO.url('customfields/field/selectOptions'),
					baseParams: {
						//'task': 'field_options',
						'field_id': customfield.id//customfield.id.replace("col_","")
					},
					root: 'results',
					totalProperty: 'total',
					id: 'id',
					fields: ['id', 'text'],
					remoteSort: true
				});

				if (GO.util.empty(customfield.options.multiselect)) {
					return Ext.apply(f, {
						xtype: 'combo',
						store: store,
						valueField: 'text',
						displayField: 'text',
						mode: 'remote',
						triggerAction: 'all',
						editable: true,
						selectOnFocus: true,
						forceSelection: true
					}, config);
				} else
				{
					return Ext.apply(f, {
						max: parseInt(customfield.options.maxSelectOtions),
						allowAddNewData: true, //otherwise every value will be looked up at the server. We don't want that.
						xtype: 'superboxselect',
						resizable: true,
						store: store,
						mode: 'remote',
						displayField: 'text',
						displayFieldTpl: '{text}',
						valueField: 'text',
						forceSelection: true,
						valueDelimiter: '|',
						hiddenName:'customFields.' + customfield.databaseName,
						anchor: '-20',
						allowBlank: GO.util.empty(customfield.required),
						queryDelay: 0,
						triggerAction: 'all'
					});
				}
			}
		},
		/*
		 * A GO\Customfields\Customfieldtype\Treeselectconsists of one master and one or more slave comboboxes.
		 * The slave is loaded with data depending on the selection of it's parent.
		 * The last slave can be a multiselect combo (superboxselect).
		 */
		"GO\\Customfields\\Customfieldtype\\Treeselect": {
			icon: 'list',
			label: 'Tree select',
			render: splitKeyValue,
			getFormField: function (customfield, config) {

				//store the slaves of this GO\Customfields\Customfieldtype\Treeselectin an array
				if (!GO.customfields.slaves)
					GO.customfields.slaves = {};

				var treeMasterFieldId = !GO.util.empty(customfield.options.treeMasterFieldId) ? customfield.options.treeMasterFieldId : customfield.id;

				if (!GO.customfields.slaves[treeMasterFieldId])
					GO.customfields.slaves[treeMasterFieldId] = {};

				var isMaster = GO.util.empty(customfield.options.nestingLevel);

				if (isMaster) {
					customfield.options.nestingLevel = 0;
				}

				GO.customfields.slaves[treeMasterFieldId][parseInt(customfield.options.nestingLevel)] = customfield.databaseName;

				var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);


				var store = new GO.data.JsonStore({
					//url: GO.settings.modules.customfields.url+'json.php',
					url: GO.url('customfields/fieldTreeSelectOption/store'),
					baseParams: {
						//'task': 'tree_select_options_combo',
						'field_id': treeMasterFieldId,
						parent_id: (isMaster) ? 0 : -1
					},
					root: 'results',
					totalProperty: 'total',
					id: 'id',
					fields: ['id', 'name', 'name_with_id'],
					remoteSort: true
				});

				delete f.name;

				if (GO.util.empty(customfield.options.multiselect)) {
					return Ext.apply(f, {
						treeMasterFieldId: treeMasterFieldId,
						nestingLevel: parseInt(customfield.options.nestingLevel),
						xtype: 'combo',
						store: store,
						hiddenName: 'customFields.' + customfield.databaseName,
						valueField: 'name_with_id',
						displayField: 'name',
						mode: 'remote',
						triggerAction: 'all',
						editable: true,
						selectOnFocus: true,
						forceSelection: true,
						listeners: {
							scope: this,
							select: function (combo, record, index) {
								var nextNestingLevel = combo.nestingLevel + 1;
								var formPanel = combo.findParentByType('form');

								console.log(nextNestingLevel, GO.customfields.slaves);

								while (GO.customfields.slaves[combo.treeMasterFieldId][nextNestingLevel]) {

									var field = formPanel.form.findField(GO.customfields.slaves[combo.treeMasterFieldId][nextNestingLevel]);
									if (!field)
										field = formPanel.form.findField(GO.customfields.slaves[combo.treeMasterFieldId][nextNestingLevel] + '[]');
									console.log(nextNestingLevel, combo.nestingLevel, field);
									if (nextNestingLevel == combo.nestingLevel + 1) //is first upcoming slave
										field.store.baseParams.parent_id = record.id;
									else
										field.store.baseParams.parent_id = -1;
									field.lastQuery = null;
									field.clearValue();

									nextNestingLevel++;
								}
							},
							render: function (combo) {
								//var formPanel = combo.findParentByType("form");
								//
								var formPanel = combo.findParentBy(function (p) {
									if (p.form)
										return true;
								});
								//add listener to form to set the correct form values and store parameters
								if (!GO.util.empty(formPanel) && !formPanel["GO\\Customfields\\Customfieldtype\\TreeselectListenerAdded"]) {
									formPanel["GO\\Customfields\\Customfieldtype\\TreeselectListenerAdded"] = true;

									formPanel.on('actioncomplete', function (form, action) {
										if (action.type == 'load') {
											form.items.each(function (field) {
												//check if this field is a tree select
												if (field.treeMasterFieldId) {

													var nextField = false;
													var nextNestingLevel = field.nestingLevel + 1;
													if (GO.customfields.slaves[field.treeMasterFieldId][nextNestingLevel]) {

														nextField = formPanel.form.findField(GO.customfields.slaves[field.treeMasterFieldId][nextNestingLevel]);
														if (!nextField)
															nextField = formPanel.form.findField(GO.customfields.slaves[field.treeMasterFieldId][nextNestingLevel] + '[]');
													}
													var v = field.getValue();

													if (v) {
														if (!field.valueDelimiter) {
															//normal combo

															v = v.split(':');
															if (v.length > 1) {

																if (nextField)
																	nextField.store.baseParams.parent_id = v[0];

																// Check if the value has colons in it, then put them back
																var vl = v[1];
																if (v.length > 2) {
																	for (var i = 2; i < v.length; i++) {
																		vl = vl + ':' + v[i];
																	}
																}

																field.setRawValue(vl);
															}
														}
													} else
													{
														//empty value
														if (GO.util.empty(field.nestingLevel)) {// is master
															field.store.baseParams.parent_id = 0;

														} else {
															field.store.baseParams.parent_id = -1;
														}
														field.clearValue();
													}
													field.lastQuery = null;

												}

											});
										}
									});
								}
							}
						}
					}, config);
				} else
				{
					//if (combo.nestingLevel!=0)
					store.baseParams.parent_id = -1;
					//only the last slave can be a multiselect combo
					return Ext.apply(f, {
						allowAddNewData: true,
						//itemId:customfield.dataname,
						max: parseInt(customfield.max),
						treeMasterFieldId: treeMasterFieldId,
						nestingLevel: parseInt(customfield.options.nestingLevel),
						xtype: 'superboxselect',
						resizable: true,
						store: store,
						mode: 'remote',
						displayField: 'name',
						displayFieldTpl: '{name}',
						valueField: 'name_with_id',
						forceSelection: true,
						valueDelimiter: '|',
						hiddenName: 'customFields.' + customfield.databaseName,
						anchor: '-20',
						allowBlank: GO.util.empty(customfield.required),
						queryDelay: 0,
						triggerAction: 'all'
					}, config);
				}

			}
		},
		"GO\\Customfields\\Customfieldtype\\TreeselectSlave": {
			icon: 'list',
			render: splitKeyValue,
			label: 'Tree select slave',
			getFormField: function (customfield, config) {
				return GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Treeselect"].getFormField(customfield, config);
			}
		},
		"GO\\Customfields\\Customfieldtype\\Heading": {
			label: 'Heading',
			getFormField: function (customfield, config) {
				return new GO.form.HtmlComponent(Ext.apply({
					html: "<h1 class=\"cf-heading\">" + customfield.name + "</h1>"
				}, config));
			}
		},
		"GO\\Customfields\\Customfieldtype\\FunctionField": {
			icon: 'functions',
			label: 'Function',
			getFormField: function (customfield, config) {
				return new Ext.form.Hidden(Ext.apply({
					name: 'customFields.' + customfield.databaseName
				}, config));
//			return false;
			}
		},

		getBaseField: function (customfield, config) {

			config = config || {};

			if (!GO.util.empty(customfield.options.validationRegex)) {

				if (!GO.util.empty(customfield.validation_modifiers))
					config.regex = new RegExp(customfield.options.validationRegex, customfield.validation_modifiers);
				else
					config.regex = new RegExp(customfield.options.validationRegex);
			}

			if (!GO.util.empty(customfield.helptext))
				config.plugins = new Ext.ux.FieldHelp(customfield.helptext);

			var fieldLabel = customfield.name;

			if (!GO.util.empty(customfield.prefix))
				fieldLabel = fieldLabel + ' (' + customfield.prefix + ')';

			if (!GO.util.empty(customfield.required))
				fieldLabel += '*';

			if (customfield.options.maxLength) {
				config.maxLength = customfield.options.maxLength;
			}

			return Ext.apply({
				xtype: 'textfield',
				name: 'customFields.' + customfield.databaseName,
				fieldLabel: fieldLabel,
				anchor: '-20',
				allowBlank: GO.util.empty(customfield.required)
			}, config);
		},

		applySuffix: function (customfield, field) {
			if (!GO.util.empty(customfield.suffix)) {
				return {
					anchor: '-20',
					xtype: 'compositefield',
					fieldLabel: field.fieldLabel,
					items: [field, {
							xtype: 'label',
							text: customfield.suffix,
//							hideLabel: true,
							columnWidth: '.1'
						}]
				};
			} else {
				return field;
			}
		},

		"GO\\Customfields\\Customfieldtype\\Text": {
			icon: 'short_text',
			label: 'Text',
			getFormField: function (customfield, config) {

				var field = GO.customfields.dataTypes.getBaseField(customfield, config);

				return GO.customfields.dataTypes.applySuffix(customfield, field);

			}
		},
		"GO\\Customfields\\Customfieldtype\\ReadonlyText": {
			icon: 'short_text',
			label: 'Text (Read only)',
			getFormField: function (customfield, config) {

				var field = GO.customfields.dataTypes.getBaseField(customfield, Ext.apply({disabled: true}, config));

				return GO.customfields.dataTypes.applySuffix(customfield, field);

			}
		},
		"GO\\Customfields\\Customfieldtype\\EncryptedText": {
			icon: 'lock',
			label: 'Encrypted text',
			getFormField: function (customfield, config) {
				var field = GO.customfields.dataTypes.getBaseField(customfield, config);

				return GO.customfields.dataTypes.applySuffix(customfield, field);
			}
		},
		"GO\\Customfields\\Customfieldtype\\Infotext": {
			icon: 'info',
			label: 'Info text',
			getFormField: function (customfield, config) {

				config = config || {};

				return Ext.apply({
					xtype: 'htmlcomponent',
					html: customfield.name,
					style: 'font-size:12px;margin-bottom:15px;'
				}, config);
			}
		},
		"GO\\Customfields\\Customfieldtype\\Yesno": {
			icon: 'check',
			label: 'Yes No Field',
			getFormField: function (customfield, config) {
				var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

				var store = new Ext.data.SimpleStore({
					id: 'id',
					fields: ['id', 'text'],
					data: [['0', t("undef", "customfields")],
						['1', t("Yes")],
						['-1', t("No")]],
					remoteSort: false
				});

				delete f.name;

				return GO.customfields.dataTypes.applySuffix(customfield, Ext.apply(f, {
					xtype: 'combo',
					store: store,
					valueField: 'id',
					displayField: 'text',
					hiddenName: 'customFields.' + customfield.databaseName,
					mode: 'local',
					editable: false,
					triggerAction: 'all',
					selectOnFocus: true,
					forceSelection: true
				}, config));
			}
		},
		"GO\\Customfields\\Customfieldtype\\UserGroup": {
			icon: 'group',
			label: t("strGroup"),
			getFormField: function (customfield, config) {

				var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

				delete f.name;

				return GO.customfields.dataTypes.applySuffix(customfield, Ext.apply(f, {
					xtype: 'selectgroup',
					idValuePair: true,
					hiddenName: 'customFields.' + customfield.databaseName,
					forceSelection: true,
					valueField: 'cf',
					customfieldId: customfield.id
				}));
			}
		}
	};

})();
