GO.customfields.dataTypes={
	"GO\\Customfields\\Customfieldtype\\User":{
		label : GO.lang.strUser,
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.name;

			f=Ext.apply(f, {
				xtype: 'selectuser',
				idValuePair:true,
				startBlank:true,
				forceSelection:true,
				hiddenName:customfield.dataname,
				anchor:'-20',
				valueField:'cf'
			});

			return f;
		}
    },
	"GO\\Customfields\\Customfieldtype\\Datetime":{
		label:'Date time',
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.anchor;

			return Ext.apply(f, {
				xtype:'datetime',
				width : 300
//				timeFormat: GO.settings['time_format'],
//				dateFormat:GO.settings['date_format'],
//				hiddenFormat: GO.settings['date_format']+" "+GO.settings['time_format']
			});
		}
	},

	"GO\\Customfields\\Customfieldtype\\Date" : {
		label:'Date',
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.anchor;

			return Ext.apply(f, {
				xtype:'datefield',
				format: GO.settings['date_format'],
				width : 120
			});
		}
	},

	"GO\\Customfields\\Customfieldtype\\Number" : {
		label : 'Number',
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);
			delete f.anchor;

			if (!GO.util.empty(customfield.prefix) || !GO.util.empty(customfield.suffix)) {				
				return {
					anchor:'-20',
					xtype: 'compositefield',
					fieldLabel: f.fieldLabel,
					items: [
						Ext.apply({
							xtype:'numberfield',
							decimals: customfield.number_decimals,
							width:120,
							name: customfield.dataname,
							allowBlank: GO.util.empty(customfield.required)
						}, config),
						{
							xtype: 'plainfield',
							value: customfield.suffix,
							hideLabel: true,
							columnWidth: '.1'
						}
					]
				}
			} else {
				return Ext.apply(f, {
					xtype:'numberfield',
					decimals: customfield.number_decimals,
					width:120
				});
			}
		}
	},
	"GO\\Customfields\\Customfieldtype\\Checkbox" :{
		label: 'Checkbox',
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.fieldLabel;

			return Ext.apply(f, {
				xtype:'xcheckbox',
				boxLabel: customfield.name,
				hideLabel: true
			});
		}
	},
	"GO\\Customfields\\Customfieldtype\\BinaryCombobox" :{
		label: 'Binary Combobox',
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.fieldLabel;

			return Ext.apply(f, {
				xtype:'combo',
				fieldLabel: customfield.name,
				hiddenName: customfield.dataname,
				store: new Ext.data.ArrayStore({
						storeId: 'binaryStore',
						idIndex: 0,
						fields:['value','label'],
						data: [
							['0',GO.lang['cmdNo']],
							['1',GO.lang['cmdYes']]
						]
					}),
				valueField:'value',
				displayField:'label',
				mode:'local',
				allowBlank: false,
				triggerAction: 'all'
			});
		}
	},
	"GO\\Customfields\\Customfieldtype\\Textarea" : {
		label : 'Textarea',
		getFormField : function(customfield, config){
			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

                        if(!customfield.height)
                        {
                               customfield.height = 40;
                        }

			return Ext.apply(f, {
				xtype:'textarea',
				height:parseInt(customfield.height),
				maxLength: Number.MAX_VALUE
			}, config);
		}
	},
	"GO\\Customfields\\Customfieldtype\\Html" : {
		label : 'HTML',
		getFormField : function(customfield, config){
			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			return Ext.apply(f, {
				xtype:'xhtmleditor',
				height:200,
				maxLength: Number.MAX_VALUE
			}, config);
		}
	},
	"GO\\Customfields\\Customfieldtype\\Select" : {
		label : 'Select',
		getFormField : function(customfield, config){
			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);
			var store = new GO.data.JsonStore({
				//url: GO.settings.modules.customfields.url+'json.php',
				url:GO.url('customfields/field/selectOptions'),
				baseParams: {
					//'task': 'field_options',
					'field_id' : customfield.customfield_id//customfield.id.replace("col_","")
				},
				root: 'results',
				totalProperty: 'total',
				id: 'id',
				fields:['id','text'],
				remoteSort:true
			});

			if(GO.util.empty(customfield.multiselect)){
				return Ext.apply(f, {
					xtype:'combo',
					store: store,
					valueField:'text',
					displayField:'text',
					mode: 'remote',
					triggerAction: 'all',
					editable: true,
					selectOnFocus:true,
					forceSelection:true
				}, config);
			}else
			{
				return Ext.apply(f, {
					max:parseInt(customfield.max),
					allowAddNewData:true, //otherwise every value will be looked up at the server. We don't want that.
					xtype:'superboxselect',
					resizable: true,
					store: store,
					mode: 'remote',
					displayField: 'text',
					displayFieldTpl: '{text}',
					valueField: 'text',
					forceSelection : true,
					valueDelimiter:'|',
					hiddenName:customfield.dataname,
					anchor:'-20',
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
	"GO\\Customfields\\Customfieldtype\\Treeselect" : {
		label : 'Tree select',
		getFormField : function(customfield, config){

			//store the slaves of this GO\Customfields\Customfieldtype\Treeselectin an array
			if(!GO.customfields.slaves)
				GO.customfields.slaves={};

			var treemaster_field_id = !GO.util.empty(customfield.treemaster_field_id) ? customfield.treemaster_field_id : customfield.id;

			if(!GO.customfields.slaves[treemaster_field_id])
				GO.customfields.slaves[treemaster_field_id]={};

			GO.customfields.slaves[treemaster_field_id][parseInt(customfield.nesting_level)]=customfield.dataname;

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			var isMaster = customfield.nesting_level==0;

			var store = new GO.data.JsonStore({
				//url: GO.settings.modules.customfields.url+'json.php',
				url: GO.url('customfields/fieldTreeSelectOption/store'),
				baseParams: {
					//'task': 'tree_select_options_combo',
					'field_id' : treemaster_field_id,
					parent_id: (isMaster) ? 0 : -1
				},
				root: 'results',
				totalProperty: 'total',
				id: 'id',
				fields:['id','name','name_with_id'],
				remoteSort:true
			});

			delete f.name;

			if(GO.util.empty(customfield.multiselect)){
				return Ext.apply(f, {
					treemaster_field_id:treemaster_field_id,
					nesting_level:parseInt(customfield.nesting_level),
					xtype:'combo',
					store: store,
					hiddenName:customfield.dataname,
					valueField:'name_with_id',
					displayField:'name',
					mode: 'remote',
					triggerAction: 'all',
					editable: true,
					selectOnFocus:true,
					forceSelection:true,
					listeners:{
						scope:this,
						select:function(combo, record, index){
							var nextNestingLevel=combo.nesting_level+1;
							var formPanel = combo.findParentByType('form');
							while(GO.customfields.slaves[combo.treemaster_field_id][nextNestingLevel]){

								var field = formPanel.form.findField(GO.customfields.slaves[combo.treemaster_field_id][nextNestingLevel]);
								if(!field)
									field = formPanel.form.findField(GO.customfields.slaves[combo.treemaster_field_id][nextNestingLevel]+'[]');

								if(nextNestingLevel==combo.nesting_level+1) //is first upcoming slave
									field.store.baseParams.parent_id=record.id;
								else
									field.store.baseParams.parent_id = -1;
								field.lastQuery = null;
								field.clearValue();

								nextNestingLevel++;
							}
						},
						render:function(combo){
							//var formPanel = combo.findParentByType("form");
							//
							var formPanel = combo.findParentBy(function(p){
								if(p.form)
									return true;
							});
							//add listener to form to set the correct form values and store parameters
							if(!GO.util.empty(formPanel) && !formPanel["GO\\Customfields\\Customfieldtype\\TreeselectListenerAdded"]){
								formPanel["GO\\Customfields\\Customfieldtype\\TreeselectListenerAdded"]=true;

								formPanel.on('actioncomplete', function(form, action){
									if(action.type=='load'){
										form.items.each(function(field){
											//check if this field is a tree select
											if(field.treemaster_field_id){

												var nextField=false;
												var nextNestingLevel=field.nesting_level+1;
												if(GO.customfields.slaves[field.treemaster_field_id][nextNestingLevel]){

													nextField = formPanel.form.findField(GO.customfields.slaves[field.treemaster_field_id][nextNestingLevel]);
													if(!nextField)
														nextField = formPanel.form.findField(GO.customfields.slaves[field.treemaster_field_id][nextNestingLevel]+'[]');
												}
												var v = field.getValue();

												if(v){
													if(!field.valueDelimiter){
														//normal combo

														v=v.split(':');
														if(v.length>1){

															if(nextField)
																nextField.store.baseParams.parent_id=v[0];

															// Check if the value has colons in it, then put them back
															var vl = v[1];
															if(v.length>2){
																for(var i=2;i<v.length;i++){
																	vl = vl+':'+v[i];
																}
															}

															field.setRawValue(vl);
														}
													}
												}else
												{
													//empty value
													if(field.nesting_level==0) // is master
														field.store.baseParams.parent_id=0;
													else
														field.store.baseParams.parent_id= -1;
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
			}else
			{
				//if (combo.nesting_level!=0)
					store.baseParams.parent_id=-1;
				//only the last slave can be a multiselect combo
				return Ext.apply(f, {
					allowAddNewData:true,
					//itemId:customfield.dataname,
					max:parseInt(customfield.max),
					treemaster_field_id:treemaster_field_id,
					nesting_level:parseInt(customfield.nesting_level),
					xtype:'superboxselect',
					resizable: true,
					store: store,
					mode: 'remote',
					displayField:'name',
					displayFieldTpl: '{name}',
					valueField: 'name_with_id',
					forceSelection : true,
					valueDelimiter:'|',
					hiddenName:customfield.dataname,
					anchor:'-20',
					allowBlank: GO.util.empty(customfield.required),
					queryDelay: 0,
					triggerAction: 'all'
				}, config);
			}

		}
	},
	"GO\\Customfields\\Customfieldtype\\TreeselectSlave" : {
		label:'Tree select slave',
		getFormField : function(customfield, config){
			return GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Treeselect"].getFormField(customfield, config);
		}
	},
	"GO\\Customfields\\Customfieldtype\\Heading": {
		label : 'Heading',
		getFormField : function(customfield, config){
			return new GO.form.HtmlComponent(Ext.apply({
				html: "<h1 class=\"cf-heading\">"+customfield.name+"</h1>"
			}, config));
		}
	},
	"GO\\Customfields\\Customfieldtype\\FunctionField" : {
		label : 'Function',
		getFormField : function(customfield, config){
			return new Ext.form.Hidden(Ext.apply({
				name: customfield.dataname
			}, config));
//			return false;
		}
	},
	"GO\\Customfields\\Customfieldtype\\Text": {
		label : 'Text',
		getFormField : function(customfield, config){

			config = config || {};

			if(!GO.util.empty(customfield.validation_regex)){

				if(!GO.util.empty(customfield.validation_modifiers))
					config.regex=new RegExp(customfield.validation_regex, customfield.validation_modifiers);
				else
					config.regex=new RegExp(customfield.validation_regex);
			}

			if(!GO.util.empty(customfield.helptext))
				config.plugins=new Ext.ux.FieldHelp(customfield.helptext);

			var fieldLabel = customfield.name;
			if(!GO.util.empty(customfield.required))
				fieldLabel+='*';

			if(customfield.max_length){
				config.maxLength=customfield.max_length;
			}

			if (customfield.required_condition) {
				config.validate = function () {
					var condition = customfield.required_condition,
 						modelPrefix = customfield.dataname.split('.')[0],
						form = this.findParentByType('form').getForm(),
						conditionParts,
						isEmptyCondition = false,
						isNotEmptyCondition = false,
						field, operator,
						value, fieldValue;

					if (condition.includes('is empty')) {
						isEmptyCondition = true;
						condition = condition.replace('is empty', '');
						field = condition.trim(' ');
						field = form.findField(modelPrefix + '.' + field);
					} else if (condition.includes('is not empty')) {
						isNotEmptyCondition = true;
						condition = condition.replace('is not empty', '');
						field = condition.trim(' ');
						field = form.findField(modelPrefix + '.' + field);
					} else {
						conditionParts = condition.split(' ');
						if (conditionParts.length === 3) { //valid condition
							operator = conditionParts[1];
							field = form.findField(modelPrefix + '.' + conditionParts[0]);
							value = conditionParts[2];
							if (!field) {
								field = form.findField(modelPrefix + '.' + conditionParts[2]);
								value = conditionParts[0];
							}
						}
					}

					if (field) {
						fieldValue = field.getValue();

						if (isEmptyCondition) {
							this.allowBlank = !Ext.isEmpty(fieldValue);
						} else if (isNotEmptyCondition) {
							this.allowBlank = Ext.isEmpty(fieldValue);
						} else {
							switch (operator) {
								case '=':
								case '==':
									this.allowBlank = !(fieldValue == value);
									break;
								case '>':
									this.allowBlank = !(fieldValue > value);
									break;
								case '<':
									this.allowBlank = !(fieldValue < value);
									break;
							}
						}
					}

					return Ext.form.Field.prototype.validate.apply(this);
				};
			}

			if (!GO.util.empty(customfield.prefix) || !GO.util.empty(customfield.suffix)) {
				
				if (!GO.util.empty(customfield.prefix))
					fieldLabel = fieldLabel+' ('+customfield.prefix+')';
				
				var compositeItems = [
						Ext.apply({
							xtype:'textfield',
							name: customfield.dataname,
							anchor:'-20',
							allowBlank: GO.util.empty(customfield.required)
						}, config)]
				
				if (!GO.util.empty(customfield.suffix))
					compositeItems.push(						{
							xtype: 'plainfield',
							value: customfield.suffix,
							hideLabel: true,
							columnWidth: '.1'
						});
				
				return {
					anchor:'-20',
					xtype: 'compositefield',
					fieldLabel: fieldLabel,
					items: compositeItems
				}
			} else {
				return Ext.apply({
					xtype:'textfield',
					name: customfield.dataname,
					fieldLabel: fieldLabel,
					anchor:'-20',
					allowBlank: GO.util.empty(customfield.required)
				}, config);
			}

		}
	},
	"GO\\Customfields\\Customfieldtype\\ReadonlyText": {
		label : 'Text (Read only)',
		getFormField : function(customfield, config){

			config = config || {};

			if(!GO.util.empty(customfield.validation_regex)){

				if(!GO.util.empty(customfield.validation_modifiers))
					config.regex=new RegExp(customfield.validation_regex, customfield.validation_modifiers);
				else
					config.regex=new RegExp(customfield.validation_regex);
			}

			if(!GO.util.empty(customfield.helptext))
				config.plugins=new Ext.ux.FieldHelp(customfield.helptext);

			var fieldLabel = customfield.name;
			if(!GO.util.empty(customfield.required))
				fieldLabel+='*';

			if(customfield.max_length){
				config.maxLength=customfield.max_length;
			}

			if (!GO.util.empty(customfield.prefix) || !GO.util.empty(customfield.suffix)) {
				
				if (!GO.util.empty(customfield.prefix))
					fieldLabel = fieldLabel+' ('+customfield.prefix+')';
				
				var compositeItems = [
						Ext.apply({
							xtype:'textfield',
							name: customfield.dataname,
							anchor:'-20',
							allowBlank: GO.util.empty(customfield.required)
						}, config)]
				
				if (!GO.util.empty(customfield.suffix))
					compositeItems.push(						{
							xtype: 'plainfield',
							value: customfield.suffix,
							hideLabel: true,
							columnWidth: '.1'
						});
				
				return {
					anchor:'-20',
					xtype: 'compositefield',
					fieldLabel: fieldLabel,
					items: compositeItems
				}
			} else {
				return Ext.apply({
					xtype:'textfield',
					name: customfield.dataname,
					fieldLabel: fieldLabel,
					anchor:'-20',
					allowBlank: GO.util.empty(customfield.required),
					disabled:true
				}, config);
			}

		}
	},
	"GO\\Customfields\\Customfieldtype\\EncryptedText": {
		label : 'Encrypted text',
		getFormField : function(customfield, config) {
			config = config || {};

			if(!GO.util.empty(customfield.validation_regex))
				config.regex=new RegExp(customfield.validation_regex);

			if(!GO.util.empty(customfield.helptext))
				config.plugins=new Ext.ux.FieldHelp(customfield.helptext);

			var fieldLabel = customfield.name;
			if(!GO.util.empty(customfield.required))
				fieldLabel+='*';

			return Ext.apply({
				xtype:'textfield',
				name: customfield.dataname,
				fieldLabel: fieldLabel,
				anchor:'-20',
				allowBlank: GO.util.empty(customfield.required)
			}, config);
		}
	},
	"GO\\Customfields\\Customfieldtype\\Infotext": {
		label : 'Info text',
		getFormField : function(customfield, config){

			config = config || {};

			return Ext.apply({
				xtype:'htmlcomponent',
				html: customfield.name,
				style:'font-size:12px;margin-bottom:15px;'
			}, config);
		}
	},
	"GO\\Customfields\\Customfieldtype\\Yesno" : {
		label : 'Yes No Field',
		getFormField : function(customfield, config){
			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			var store = new Ext.data.SimpleStore({
				id: 'id',
				fields:['id','text'],
				data: [['0',GO.customfields.lang.undef],
							['1',GO.lang.cmdYes],
							['-1',GO.lang.cmdNo]],
				remoteSort:false
			});

			delete f.name;

			return Ext.apply(f, {
				xtype:'combo',
				store: store,
				valueField:'id',
				displayField:'text',
				hiddenName:customfield.dataname,
				mode: 'local',
				editable: false,
				triggerAction : 'all',
				selectOnFocus:true,
				forceSelection:true
			}, config);
		}
	},
	"GO\\Customfields\\Customfieldtype\\UserGroup" : {
		label : GO.lang.strGroup,
		getFormField : function(customfield, config){

			var f = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Text"].getFormField(customfield, config);

			delete f.name;
			
			return Ext.apply(f, {
				xtype: 'selectgroup',
				idValuePair:true,
				hiddenName:customfield.dataname,
				forceSelection:true,				
				valueField:'cf',
				customfieldId: customfield.dataname
			});
		}
	}
};
