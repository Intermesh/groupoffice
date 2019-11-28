/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: FieldDialog.js 19873 2016-03-01 10:55:30Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 */


GO.customfields.FieldDialog = function(config){

	if(!config)
		config={};

	this.extendModel = false;

	this.nameField = new Ext.form.TextField({
		name: 'name',
		anchor:'-20',
		allowBlank:false,
		fieldLabel: GO.lang['strName']
	});


	this.categoryField = new GO.form.ComboBox({
		fieldLabel: GO.customfields.lang.category,
		hiddenName:'category_id',
		anchor:'-20',
		store: GO.customfields.categoriesStore,
		value:'text',
		valueField:'id',
		displayField:'name',
		mode: 'local',
		triggerAction: 'all',
		editable: false,
		selectOnFocus:true,
		forceSelection: true
	});

	this.typeField = new GO.form.ComboBox({
		fieldLabel: GO.lang.strType,
		hiddenName:'datatype',
		anchor:'-20',
		store: new GO.data.JsonStore({
			fields: ['className', 'type','hasLength'],
			sortInfo : {
				field:'text',
				direction:'ASC'
			},
			url:GO.url('customfields/field/types'),
			baseParams:{extend_model:this.extendModel}
		}),
		value:'GO\\Customfields\\Customfieldtype\\Text',
		valueField:'className',
		displayField:'type',
		allowBlank:false,
		typeAhead: true,
		mode: 'local',
		triggerAction: 'all',
		editable: false,
		selectOnFocus:true,
		forceSelection: true
	});

	this.typeField.on('GO\\Customfields\\Customfieldtype\\Select', function(combo, record, index){
		this.typeChange(combo, record.data.value);
	}, this);

	this.typeField.on('change', this.typeChange, this);


	this.maxLengthField = new GO.form.NumberField({
		name: 'max_length',
		value: 50,
		fieldLabel: GO.customfields.lang['maxLength'],
		minValue: 0,
//		maxValue: 255,
		decimals: 0,
		disabled: true
	});

	this.typeField.on('select', function(combo,record,index){
		this.maxLengthField.setDisabled(!record.data['hasLength']);
		this.maxLengthField.setVisible(record.data['hasLength']);
	}, this);

	this.prefixField = new Ext.form.TextField({
		name: 'prefix',
		anchor:'-20',
		allowBlank:true,
		maxLength: 32,
		fieldLabel: GO.customfields.lang.prefix
	});
	
	this.suffixField = new Ext.form.TextField({
		name: 'suffix',
		anchor:'-20',
		allowBlank:true,
		maxLength: 32,
		fieldLabel: GO.customfields.lang.suffix
	});

	this.functionField = new Ext.form.TextField({
		name: 'function',
		anchor:'-20',
		allowBlank:true,
		fieldLabel: GO.lang.strFunction
	});

	var textComponent = new GO.form.HtmlComponent({
		html: GO.customfields.lang.numberField+GO.customfields.lang.usableOperators
	});


	this.optionsGrid = new GO.customfields.SelectOptionsGrid();
	this.optionsGrid.setVisible(false);


	this.treeSelectOptions = new GO.customfields.TreeSelectOptions();
	this.treeSelectOptions.setVisible(false);


	this.functionPanel = new Ext.form.FieldSet({
		title: GO.customfields.lang.functionProperties,
		autoHeight: true,
		border: true,
		items: [textComponent, this.functionField]
	});

	this.functionPanel.setVisible(false);

	//See Elite/views/extjs3/Customfield.js
	this.extraOptions = new Ext.Panel({layout:'form'});
	this.phpExtraOptions = new Ext.Panel({layout:'form'});

	this.formPanel = new Ext.FormPanel({
		labelWidth:140,
		//	autoHeight:true,
		anchor:'100%',
		autoScroll:true,
		waitMsgTarget:true,
		bodyStyle:'padding:5px;',
		items: [
		this.nameField,
		this.typeField,
		this.extraOptions,
		this.phpExtraOptions,
		this.maxLengthField,
		this.prefixField,
		this.suffixField,
		this.multiSelectCB = new Ext.ux.form.XCheckbox({
			name:'multiselect',
			fieldLabel:GO.customfields.lang.multiselect,
			listeners:{
				check:function(cb, check){
					this.max.getEl().up('.x-form-item').setDisplayed(check);
				},
				scope:this
			},
			plugins:[new Ext.ux.FieldHelp(GO.customfields.lang.multiselectForLastSlaveOnly)]
		}),
		this.heightField = new GO.form.NumberField({
			name:'height',
			decimals:0,
			width:40,
			value:100,
			fieldLabel:GO.customfields.lang.height
		}),
		this.max = new GO.form.NumberField({
			name:'max',
			decimals:0,
			width:40,
			value:0,
			fieldLabel:GO.customfields.lang.maxOptions,
			plugins:[new Ext.ux.FieldHelp(GO.customfields.lang.zeroMeansUnlimited)]
		}),
		this.masterTree = new GO.form.PlainField({
			name:'master_tree',
			fieldLabel:'Master select'
		}),
		this.categoryField,
		this.decimalsField = new GO.form.NumberField({
			name:'number_decimals',
			decimals:0,
			width:40,
			value:2,
			fieldLabel:GO.customfields.lang.nDecimals
		}),
		this.addressbookIdsField = new Ext.form.TextField({
			name:'addressbook_ids',
			maxLength:255,
			fieldLabel:GO.customfields.lang.addressbookIds,
			hidden: true,
			disabled: true,
			anchor: '-20'
		}),
		this.requiredCB = new Ext.ux.form.XCheckbox({
			xtype:'xcheckbox',
			name:'required',
			fieldLabel:GO.customfields.lang.required,
			listeners: {
				check: function(cb, value) {
					this.requiredConditionField.setDisabled(value);
				},
				scope: this
			}
		}),
		this.requiredConditionField = new Ext.form.TextField({
			name:'required_condition',
			fieldLabel:GO.customfields.lang.required_condition,
			anchor:'-20'
		}),
		this.hideInGridCB = new Ext.ux.form.XCheckbox({
			xtype:'xcheckbox',
			name:'exclude_from_grid',
			fieldLabel:GO.customfields.lang.excludeFromGrid
		}),
		this.uniqueCB = new Ext.ux.form.XCheckbox({
			xtype:'xcheckbox',
			name:'unique_values',
			fieldLabel:GO.customfields.lang['uniqueValues']
		}),
		this.regexField = new Ext.form.TextField({
			disabled:true,
			name:'validation_regex',
			fieldLabel:GO.customfields.lang.validationRegexp,
			anchor:'-20'
		}),this.helptextField = new Ext.form.TextField({
			xtype:'textfield',
			anchor:'-20',
			name:'helptext',
			fieldLabel:GO.customfields.lang.helpText
		}),
		this.functionPanel,
		this.optionsGrid,
		this.treeSelectOptions
		],
		baseParams:{
			field_id:0
		}
	});


	var focusName = function(){
		this.nameField.focus();
	};

	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=true;
	config.width=500;
	config.height=520;
	//config.autoHeight=true;
	config.closeAction='hide';
	config.title= GO.lang.field;
	config.items= this.formPanel;
	config.focus= focusName.createDelegate(this);
	config.buttons=[{
		text: GO.lang['cmdOk'],
		handler: function(){
			this.submitForm(true);
		},
		scope: this
	},{
		text: GO.lang['cmdApply'],
		handler: function(){
			this.submitForm();
		},
		scope:this
	},{
		text: GO.lang['cmdClose'],
		handler: function(){
			this.hide();
		},
		scope:this
	}
	];

	config.listeners={
		render:function(){
			this.typeField.store.load();
		},
		scope:this
	}


	GO.customfields.FieldDialog.superclass.constructor.call(this, config);


	this.addEvents({
		'save' : true
	});
}

Ext.extend(GO.customfields.FieldDialog, Ext.Window,{

	loadData: {}, // will save the loaded data when form shows

	typeChange : function(combo, newValue)
	{
		this.addressbookIdsField.setVisible(newValue=='GO\\Addressbook\\Customfieldtype\\Contact' || newValue=='GO\\Addressbook\\Customfieldtype\\Company');
		this.addressbookIdsField.setDisabled(newValue!='GO\\Addressbook\\Customfieldtype\\Contact' && newValue!='GO\\Addressbook\\Customfieldtype\\Company');

		var useSuffixPrefix = newValue=='GO\\Customfields\\Customfieldtype\\Text' || newValue=='GO\\Customfields\\Customfieldtype\\Number' || newValue=='GO\\Customfields\\Customfieldtype\\FunctionField';
		this.prefixField.setVisible(useSuffixPrefix);
		this.prefixField.setDisabled(!useSuffixPrefix);
		this.suffixField.setVisible(useSuffixPrefix);
		this.suffixField.setDisabled(!useSuffixPrefix);

		this.helptextField.setDisabled(newValue=='GO\\Customfields\\Customfieldtype\\Infotext');
		this.requiredCB.setDisabled(newValue=='GO\\Customfields\\Customfieldtype\\Infotext');
		this.requiredConditionField.setDisabled(newValue=='GO\\Customfields\\Customfieldtype\\Infotext');
		this.decimalsField.setDisabled(newValue!='GO\\Customfields\\Customfieldtype\\Number');
		this.decimalsField.setVisible(newValue=='GO\\Customfields\\Customfieldtype\\Number');

		this.nameField.setHeight(newValue=='GO\\Customfields\\Customfieldtype\\Infotext' ? 120 : 22);

		this.treeSelectOptions.setVisible(newValue=='GO\\Customfields\\Customfieldtype\\Treeselect');
		if(newValue=='GO\\Customfields\\Customfieldtype\\Treeselect')
		{
			this.treeSelectOptions.setFieldId(this.field_id);
		}

		this.masterTree.container.up('div.x-form-item').setDisplayed(newValue=='GO\\Customfields\\Customfieldtype\\TreeselectSlave');

		this.functionPanel.setVisible(newValue=='GO\\Customfields\\Customfieldtype\\FunctionField');
		if(newValue=='GO\\Customfields\\Customfieldtype\\FunctionField')
		{
			this.functionPanel.doLayout();
		}

		this.multiSelectCB.container.up('div.x-form-item').setDisplayed(newValue=='GO\\Customfields\\Customfieldtype\\Select' || newValue=='GO\\Customfields\\Customfieldtype\\TreeselectSlave');

		this.multiSelectCB.helpTextEl.setDisplayed(newValue=='GO\\Customfields\\Customfieldtype\\TreeselectSlave');

		this.heightField.container.up('div.x-form-item').setDisplayed(newValue=='GO\\Customfields\\Customfieldtype\\Textarea');

		this.optionsGrid.setVisible(newValue=='GO\\Customfields\\Customfieldtype\\Select');
		if(newValue=='GO\\Customfields\\Customfieldtype\\Select')
		{
			this.optionsGrid.setFieldId(this.field_id);
		}

		this.regexField.setDisabled(newValue!='GO\\Customfields\\Customfieldtype\\Text');

		// Select deselect mother in Datatype to customize dialog (implementation in Php-Customfield datatype)
		if(GO.customfields.dataTypes[this.oldValue] && GO.customfields.dataTypes[this.oldValue].onDeselect) {
			GO.customfields.dataTypes[this.oldValue].onDeselect(this);
		}
		if(GO.customfields.dataTypes[newValue] && GO.customfields.dataTypes[newValue].onSelect) {
			GO.customfields.dataTypes[newValue].onSelect(this);
		}

		this.syncShadow();
		this.center();

		this.oldValue = newValue;
	},
	oldValue : 'GO\\Customfields\\Customfieldtype\\Text',

	show : function (field_id) {

		if(!this.typeField.store.loaded){
			this.typeField.store.load({
				callback:function(){
					this.typeField.setValue("GO\\Customfields\\Customfieldtype\\Text");
					this.show(field_id);
				},
				scope:this
			});
			return;
		}

		if(!this.rendered){
			this.render(Ext.getBody());
			this.max.getEl().up('.x-form-item').setDisplayed(false);
		}
		//this.formPanel.form.reset();

		this.setFieldId(field_id);

		if(field_id>0)
		{
			this.formPanel.load({
				url:GO.url('customfields/field/load'),
				success:function(form, action)
				{
					var response = Ext.decode(action.response.responseText);
					this.loadData = response.data;
					this.typeChange(this.typeField, this.typeField.getValue());

					GO.customfields.FieldDialog.superclass.show.call(this);

					this.maxLengthField.setDisabled(!response.data['hasLength']);
					this.maxLengthField.setVisible(response.data['hasLength']);
				},
				failure:function(form, action)
				{
					GO.errorDialog.show(action.result.feedback)
				},
				scope: this

			});
		}else
		{
			this.loadData = {};
			this.formPanel.form.reset();
			if(!this.lastCategoryId)
				this.lastCategoryId=GO.customfields.categoriesStore.data.items[0].id;

			if(GO.customfields.categoriesStore.getById(this.lastCategoryId))
				this.categoryField.setValue(this.lastCategoryId);
			else
				this.categoryField.selectFirst();

			this.typeChange(this.typeField, 'GO\\Customfields\\Customfieldtype\\Text');
			this.maxLengthField.setDisabled(false);
			this.maxLengthField.setVisible(true);
			GO.customfields.FieldDialog.superclass.show.call(this);
		}
	},

	setCategoryId : function(category_id)
	{
		this.formPanel.baseParams['category_id']=category_id;

	},

	setExtendModel : function(extend_model){

		if(extend_model!=this.extendModel){
			this.typeField.store.loaded=false;
			this.extendModel = extend_model;
			this.typeField.store.baseParams.extend_model = this.extendModel;
		}
	},

	setFieldId : function(field_id)
	{
		this.formPanel.form.baseParams['id']=field_id;
		this.field_id=field_id;
		if(this.typeField.getValue()=='GO\\Customfields\\Customfieldtype\\Select')
			this.optionsGrid.setFieldId(field_id);

		if(this.typeField.getValue()=='GO\\Customfields\\Customfieldtype\\Treeselect')
			this.treeSelectOptions.setFieldId(field_id);
	},

//	submitForm : function(hide){
//
//		if (this.uniqueCB.getValue()==true) {
//			Ext.Msg.show({
//				title: GO.customfields.lang['makeUnique'],
//				icon: Ext.MessageBox.WARNING,
//				msg: GO.customfields.lang['makeUniqueRUSure'],
//				buttons: Ext.Msg.YESNO,
//				scope:this,
//				fn: function(btn) {
//					if (btn=='yes') {
//						this._submitForm(hide);
//					}
//				}
//			});
//		} else {
//			this._submitForm(hide);
//		}
//
//	},

	submitForm : function(hide) {
		this.formPanel.form.submit(
		{
			submitEmptyText: false,
			//url:GO.settings.modules.customfields.url+'action.php',
			url:GO.url('customfields/field/submit'),
			params: {
				//'task' : 'save_field',
				'select_options' : Ext.encode(this.optionsGrid.getGridData())
			},
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){

				this.fireEvent('save', this);

				if(action.result.id)
				{
					this.setFieldId(action.result.id);
				}

				if(hide)
				{
					this.hide();
				}

				this.lastCategoryId=this.categoryField.getValue();

				this.optionsGrid.store.commitChanges();

			},
			failure: function(form, action) {
				if(action.failureType == 'client')
				{
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);
				} else {
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
				}
			},
			scope: this
		});
	}
});
