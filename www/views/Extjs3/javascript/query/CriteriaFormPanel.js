//CriteriaPanel

GO.query.CriteriaFormPanel = Ext.extend(Ext.form.FormPanel , {
	
	style: {
		border: '1px solid black',
		marginTop: '5px',
		marginBottom: '0px',
		marginRight: '0px',
		marginLeft: '5px'
	},

	andorStore: new Ext.data.ArrayStore({
		idIndex:0,
		fields: ['value'],
		data : [
			['AND'],
			['OR']
		]
	}),
	
	fieldStore: new Ext.data.ArrayStore({
		idIndex:0,
		fields: ['name','label','gotype'],
		data : [
			['test', 'Test', ''],
			['test2', 'Test2', '']
		]
	}),
	
	operatorType: new Ext.data.ArrayStore({
		idIndex:0,
		fields: ['value'],
		data : [
			['LIKE'],
			['NOT LIKE'],
			['='],
			['!='],
			['>'],
			['<']
		]
	}),
	
	constructor: function (config) {
		if(!config) {
			config = {};
		}
		if(!config.items) {
			config.items = [];
		}
		
		GO.query.CriteriaFormPanel.superclass.constructor.call(this, config);
	},
	
	initComponent: function(){
		
		this.items.push(new Ext.ux.form.XCheckbox({
			fieldLabel: GO.lang.queryStartGroup,
			name: 'start_group'
		}));
		
		this.items.push(new GO.form.ComboBox({
				hiddenName: 'andor',
				store: this.andorStore,
				value: 'AND',
				anchor:'100%',
				valueField:'value',
				displayField:'value',
				name:'query_operator',
				fieldLabel: GO.lang.queryAnd+' / '+GO.lang.queryOr,
				mode: 'local',
				triggerAction: 'all',
				editable: false,
				selectOnFocus:true,
				forceSelection:true
			}));

		this.items.push(this.fieldComboBox = new GO.form.ComboBox({
					store: this.fieldStore,
					fieldLabel: GO.lang.queryField,
					hiddenName: 'field',
//					dataIndex: 'comparator',
					valueField:'name',
					anchor:'100%',
					displayField:'label',
					mode: 'local',
					triggerAction: 'all',
					editable: true,
					selectOnFocus:true,
					forceSelection:true,
					listeners:{
						scope:this,
						select:function(combo,record){
							this.setValueField(record.get('gotype'), record.get('name'));
						}
					}
				}));	
				
		this.items.push(new GO.form.ComboBox({
			hiddenName: 'comparator',
			store: this.operatorType,
			fieldLabel: GO.lang.queryComparator,
			value: 'LIKE',
			valueField:'value',
			displayField:'value',				
			width: 60,
			mode: 'local',
			triggerAction: 'all',
			editable: false,
			selectOnFocus:true,
			forceSelection:true
			}));	
			
			this.items.push(this.fieldGotype = new Ext.form.Hidden({
				name: 'gotype'
			}));

		GO.query.CriteriaFormPanel.superclass.initComponent.call(this);
	},
	
	getValues: function() {
		
		var formValues = this.getForm( ).getFieldValues()
		if(typeof formValues.value == "undefined" && typeof this.currentValueEditor != "undefined") {
			formValues.value = this.currentValueEditor.getValue();
		}
		
		return formValues;
	},
	
	setValues: function(values) {
		this.getForm().setValues(values);
		
		if(this.fieldComboBox.getValue()) {			
			
				var records = this.fieldComboBox.store.query(this.fieldComboBox.valueField, new RegExp('^' + Ext.escapeRe(String(this.fieldComboBox.getValue())) + '$'));
				var record = records.items[0]
				
				var field = this.setValueField(record.get('gotype'), record.get('name'));
				field.setValue(values.value);
			}
		
	},

	setValueField : function(gotype, colName){
		
		if(this.currentValueEditor) {
			this.remove(this.currentValueEditor);
		}
		
		
		this.currentValueEditor = Ext.create(GO.base.form.getFormFieldByType(gotype, colName, {name: 'value', hiddenName: 'value', fieldLabel: GO.lang.queryValue, anchor:'100%'}));
		
		
		// if it is a superboxselect / Multie select swites to combo!!!
		if(this.currentValueEditor.xtype == 'superboxselect') {
			
			var dotIndex = colName.indexOf('.');
			if(dotIndex){
				dotIndex++;
				colName = colName.substr(dotIndex,colName.length-dotIndex);
			}
			var customfield = GO.customfields.columnMap[colName]
			
			
			customfield.multiselect = 0;
			this.currentValueEditor = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Select"].getFormField(customfield, {name: 'value',fieldLabel: GO.lang.queryValue, anchor:'100%'});
			
			
		}
		
		this.fieldGotype.setValue(gotype);
		
		this.add(this.currentValueEditor);
		this.doLayout();
		return this.currentValueEditor;
	}
	
});
