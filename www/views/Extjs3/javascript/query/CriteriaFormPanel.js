//CriteriaPanel

GO.query.CriteriaFormPanel = Ext.extend(Ext.form.FormPanel , {
	
	cls: 'go-form-panel',
	labelAlign: 'top',
	layout: 'hbox',
	
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
	
	initComponent: function(){
		
		var removeBtn = this.items[0];
		
		this.items = [removeBtn,new Ext.ux.form.XCheckbox({
			fieldLabel: t("Start group"),
			name: 'start_group'
		}),
		new GO.form.ComboBox({
			hiddenName: 'andor',
			store: this.andorStore,
			value: 'AND',
			valueField:'value',
			displayField:'value',
			width: dp(75),
			name:'query_operator',
			fieldLabel: t("And")+' / '+t("Or"),
			mode: 'local',
			triggerAction: 'all',
			editable: false,
			selectOnFocus:true,
			forceSelection:true
		}),
		this.fieldComboBox = new GO.form.ComboBox({
			store: this.fieldStore,
			fieldLabel: t("Field"),
			hiddenName: 'field',
			valueField:'name',
			displayField:'label',
			mode: 'local',
			triggerAction: 'all',
			editable: true,
			selectOnFocus:true,
			forceSelection:true,
			width: 360,
			listeners:{
				scope:this,
				select:function(combo,record){
					this.setValueField(record.get('gotype'), record.get('name'));
				}
			}
		}),
		new GO.form.ComboBox({
			hiddenName: 'comparator',
			store: this.operatorType,
			fieldLabel: t("Comparator"),
			value: 'LIKE',
			valueField:'value',
			displayField:'value',				
			width: 70,
			listWidth: 140,
			mode: 'local',
			triggerAction: 'all',
			editable: false,
			selectOnFocus:true,
			forceSelection:true
		}),
		this.fieldGotype = new Ext.form.Hidden({
			name: 'gotype'
		})];

		GO.query.CriteriaFormPanel.superclass.initComponent.call(this);
	},
	
	getValues: function() {
		
		var formValues = this.getForm( ).getFieldValues()
		if(typeof formValues.value == "undefined" && typeof this.currentValueEditor != "undefined") {
			formValues.value = this.currentValueEditor.getValue();			
		}
		
		if(Ext.isDate(formValues.value)) {
			formValues.value = formValues.value.format(GO.settings.date_format);
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

		this.currentValueEditor = Ext.create(GO.base.form.getFormFieldByType(gotype, colName, 
		{name: 'value', hiddenName: 'value', fieldLabel: t("Value"), flex:'1'}));

		// if it is a superboxselect / Multie select swites to combo!!!
		if(this.currentValueEditor.xtype == 'superboxselect') {
			
			var dotIndex = colName.indexOf('.');
			if(dotIndex){
				dotIndex++;
				colName = colName.substr(dotIndex,colName.length-dotIndex);
			}
			var customfield = GO.customfields.columnMap[colName]

			customfield.multiselect = 0;
			this.currentValueEditor = GO.customfields.dataTypes["GO\\Customfields\\Customfieldtype\\Select"].getFormField(customfield, {name: 'value',fieldLabel: t("Value"), anchor:'100%'});
			
		}
		
		this.fieldGotype.setValue(gotype);
		
		this.add(this.currentValueEditor);
		this.doLayout();
		return this.currentValueEditor;
	}
	
});
