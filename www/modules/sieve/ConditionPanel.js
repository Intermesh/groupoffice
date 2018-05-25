/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ConditionPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.sieve.ConditionPanel = function(config){
config = config || {};

	this.cmbField = new GO.form.ComboBox({
		hiddenName:'arg1',
		valueField:'value',
		displayField:'field',
		value:'Subject',
		store: GO.sieve.cmbFieldStore,
		mode:'local',
		triggerAction:'all',
		editable:false,
		forceSelection:true,
		allowBlank:false,
		width:90,
		emptyText:t("Field", "sieve")
	});

	this.cmbOperator = new GO.form.ComboBox({
		hiddenName:'type',
		value:'contains',
		valueField:'value',
		displayField:'field',
		store: GO.sieve.cmbOperatorStore,
		mode:'local',
		triggerAction:'all',
		editable:false,
		selectOnFocus:true,
		forceSelection:true,
		allowBlank:false,
		width: dp(140),
		emptyText:t("Operator", "sieve")
	});

	this.txtCondition = new Ext.form.TextField({
		name: 'arg2' ,
		emptyText: '...',
		allowBlank:false,
		width:150
	});

	this.txtCustom = new Ext.form.TextField({
		name: 'custom' ,
		emptyText: t("Custom", "sieve"),
		fieldLabel:t("Custom", "sieve"),
		allowBlank:false,
		width:140,
		hidden: true
	});

	this.cmbUnderOver = new GO.form.ComboBox({
		hiddenName:'underover',
		hidden: true,
		value:'under',
		valueField:'value',
		displayField:'field',
		store: GO.sieve.cmbUnderOverStore,
		mode:'local',
		triggerAction:'all',
		editable:false,
		selectOnFocus:true,
		forceSelection:true,
		allowBlank:false,
		width:70
	});

	this.rgSize = new Ext.form.RadioGroup({
		columns: 4,
		width:150,
		hidden: true,
		items: [
		{
			items:{boxLabel: 'B', name: 'size', inputValue: 'B', style: 'margin-left: 4px; margin-right: -2px;'}
		},
		{
			items:{boxLabel: 'KB', name: 'size', inputValue: 'K', style: 'margin-left: 4px; margin-right: -2px;', checked: true}
		},
		{
			items:{boxLabel: 'MB', name: 'size', inputValue: 'M', style: 'margin-left: 4px; margin-right: -2px;'}
		},
		{
			items:{boxLabel: 'GB', name: 'size', inputValue: 'G', style: 'margin-left: 4px; margin-right: -2px;'}
		}],
		hideLabel:true
	})

	this.cmbField.on('select', function(combo, record){
		this.setVisibleFields();
	},this);

	this.cmbOperator.on('select', function(combo,record){
		this.setVisibleFields();
	},this);

	var rule_add = Ext.data.Record.create([
		{
			name: 'test',
			type: 'string'
		},
		{
			name: 'not',
			type: 'string'
		},
		{
			name: 'type',
			type: 'string'
		},
		{
			name: 'arg1',
			type: 'string'
		},
		{
			name: 'arg2',
			type: 'string'
		}]);


	this.btnAddRule = new Ext.Button({
		text: t("Add"),
		handler : function() {
			// Build up the data before adding the data to the grid.
			var _test = '';
			var _not = true;
			var _type = '';
			var _arg = '';
			var _arg1 = this.cmbField.getValue();
			var _arg2 = this.txtCondition.getValue();

			// Workaround for _arg2 check
			if(this.cmbOperator.getValue() == 'exists' || this.cmbOperator.getValue() == 'notexists' || this.cmbField.getValue() == 'X-Spam-Flag')
				_arg2 = 'sometext';
			
			// Check the input value of the txtBox
			if(_arg2 != '')
			{
				if(this.cmbField.getValue() == 'custom')
				{
					_test = 'header';
					_arg = '';
					_arg1 = this.txtCustom.getValue();
					_arg2 = this.txtCondition.getValue();

					if(this.cmbOperator.getValue() == 'exists' || this.cmbOperator.getValue() == 'notexists')
					{
						_test = 'exists';
						_arg = this.txtCustom.getValue();
						_arg1 = '';
						_arg2 = '';
					}

					if(this.cmbOperator.getValue() == 'notcontains' || this.cmbOperator.getValue() == 'notis' || this.cmbOperator.getValue() == 'notexists')
						_not = true;
					else
						_not = false;

					if(this.cmbOperator.getValue() == 'contains' ||this.cmbOperator.getValue() == 'notcontains')
						_type = 'contains';
					else if(this.cmbOperator.getValue() == 'is' ||this.cmbOperator.getValue() == 'notis')
						_type = 'is';
				}
				else if(this.cmbField.getValue() == 'X-Spam-Flag')
				{
					_test = 'header';
					_not = false;
					_type	= this.cmbOperator.getValue();
					_arg = '';
					_arg1 = 'X-Spam-Flag';
					_arg2 = 'YES';
				}
				else if(this.cmbField.getValue() == 'size')
				{
					_test = 'size';
					_not = false;
					_type	= this.cmbUnderOver.getValue();
					_arg1 = '';
					_arg2 = '';
					
					if(this.rgSize.getValue().inputValue == 'B')
						_arg = this.txtCondition.getValue();
					else
						_arg = this.txtCondition.getValue() + this.rgSize.getValue().inputValue;
				}
				else
				{
					if(this.cmbOperator.getValue() == 'exists' || this.cmbOperator.getValue() == 'notexists')
					{
						_test = 'exists';
						_type = '';
						_arg = this.cmbField.getValue();
						_arg1 = '';
						_arg2 = '';
						
						if(this.cmbOperator.getValue() == 'notexists')
							_not = true;
						else
							_not = false;
					}
					else
					{
						_test = 'header';
						_arg = '';
						_arg1 = this.cmbField.getValue();
						_arg2 = this.txtCondition.getValue();

						if(this.cmbOperator.getValue() == 'notcontains' || this.cmbOperator.getValue() == 'notis')
							_not = true;
						else
							_not = false;

						if(this.cmbOperator.getValue() == 'contains' ||this.cmbOperator.getValue() == 'notcontains')
							_type = 'contains';
						else if(this.cmbOperator.getValue() == 'is' ||this.cmbOperator.getValue() == 'notis')
							_type = 'is';
					}
				}
				
				var record;
				var values ={
						test: _test,
						not:  _not,
						type: _type,
						arg:	_arg,
						arg1: _arg1,
						arg2: _arg2
					};

				if(this.index==-1){
					record = new rule_add(values)
					this.grid.store.insert( this.grid.store.getCount(), record);
				}
				else
				{
					record = this.grid.store.getAt(this.index);
					Ext.apply(record.data,values);
					record.commit();
				}
				
				this.resetForm();
			}
		},
		scope : this
	})

	this.btnClearRule = new Ext.Button({
		text: t("Clear", "sieve"),
		handler : function() {			
			this.resetForm();
		},
		scope : this
	})

	config.bodyStyle='padding:5px';
	config.border=false;
	config.autoHeight=true;
	config.region='center';
	config.url=GO.settings.modules.sieve.url+'fileIO.php';
	config.baseParams={
		task : 'addRule',
		account_id : 0,
		script_name : '',
		rule_name : '',
		script_index : 0
	};

	config.items=[
		{
			xtype:'compositefield',
			items:[
				this.cmbField,
				this.txtCustom,
				this.cmbUnderOver,
				this.cmbOperator, 
				this.txtCondition,
				this.rgSize,
				this.btnAddRule,
				this.btnClearRule
			],
			hideLabel:true
		}];
	GO.sieve.ConditionPanel.superclass.constructor.call(this, config);
}

Ext.extend(GO.sieve.ConditionPanel, Ext.FormPanel,{
	index : -1,
	grid : false,

	setFormValues : function (record,index){
		this.index=typeof(index)=='undefined' ? -1 : index;
		this.form.setValues(record.data);

		if(record.data.test == 'size')
		{
			this.cmbField.setValue(record.data.test);
			this.cmbUnderOver.setValue(record.data.type);

			var last = record.data.arg.substr(record.data.arg.length-1,1);
			var first = record.data.arg.substr(0,record.data.arg.length-1);

			if(last != 'K' && last != 'M' && last != 'G')
			{
				first = first+last;
				last = 'B';
			}

			this.txtCondition.setValue(first);
			this.rgSize.setValue(last);
		}
		if(record.data.arg1 != 'Subject' && record.data.arg1 != 'From' && record.data.arg1 != 'To' && record.data.arg1 != 'size' && record.data.test != 'size' && record.data.arg1 != 'X-Spam-Flag')
		{			
			this.cmbField.setValue('custom');
			this.txtCustom.setValue(record.data.arg1);
		}

		if(record.data.type == 'contains' && record.data.not == true)
			this.cmbOperator.setValue('notcontains');
		else if(record.data.type == 'is' && record.data.not == true)
			this.cmbOperator.setValue('notis');
		else if(record.data.test == 'exists' && record.data.not == true)
		{
			this.cmbOperator.setValue('notexists');
			
			if(record.data.arg != '' && (record.data.arg == 'notis' || record.data.arg == 'is' || record.data.arg == 'notcontains' || record.data.arg == 'contains' || record.data.arg == 'exists' || record.data.arg == 'notexists'))
				this.cmbField.setValue(record.data.arg);
			else
			{
				this.cmbField.setValue('custom');
				if(record.data.arg == 'notis' || record.data.arg == 'is' || record.data.arg == 'notcontains' || record.data.arg == 'contains' || record.data.arg == 'exists' || record.data.arg == 'notexists')
					this.txtCustom.setValue(record.data.arg1);
				else
					this.txtCustom.setValue(record.data.arg);
			}
		}
		else if(record.data.test == 'exists' && record.data.not == false)
		{
			this.cmbOperator.setValue('exists');

			if(record.data.arg != '' && (record.data.arg == 'notis' || record.data.arg == 'is' || record.data.arg == 'notcontains' || record.data.arg == 'contains' || record.data.arg == 'exists' || record.data.arg == 'notexists'))
				this.cmbField.setValue(record.data.arg);
			else
			{
				this.cmbField.setValue('custom');
				if(record.data.arg == 'notis' || record.data.arg == 'is' || record.data.arg == 'notcontains' || record.data.arg == 'contains' || record.data.arg == 'exists' || record.data.arg == 'notexists')
					this.txtCustom.setValue(record.data.arg1);
				else
					this.txtCustom.setValue(record.data.arg);
			}
		}

		this.setVisibleFields();
		this.btnAddRule.setText(t("Edit"));
		this.btnClearRule.setText(t("Cancel"));
	},
	toggleCondition : function(){
		var type = this.cmbOperator.getValue();

		if(this.cmbField.getValue() != 'size' &&  (type=='exists' || type=='notexists'))
			this.txtCondition.hide();
		else
			this.txtCondition.show();
	},
	setVisibleFields : function(){

		this.toggleCondition();

		switch(this.cmbField.getValue())
		{
			case 'size':
				this.txtCustom.hide();
				this.cmbOperator.hide();
				this.cmbUnderOver.show();
				this.rgSize.show();				
				break;

			case 'From':
				this.txtCustom.hide();
				this.cmbOperator.show();
				this.cmbUnderOver.hide();
				this.rgSize.hide();
				break;

			case 'To':
				this.txtCustom.hide();
				this.cmbOperator.show();
				this.cmbUnderOver.hide();
				this.rgSize.hide();				
				break;
				
			case 'Subject':
				this.txtCustom.hide();
				this.cmbOperator.show();
				this.cmbUnderOver.hide();
				this.rgSize.hide();
				break;
				
			case 'X-Spam-Flag':
				this.txtCustom.hide();
				this.cmbOperator.hide();
				this.txtCondition.hide();
				this.cmbUnderOver.hide();
				this.rgSize.hide();
				break;
				
			case 'custom':
				this.txtCustom.show();
				this.cmbOperator.show();
				
				if(this.cmbOperator.getValue() == 'exists' || this.cmbOperator.getValue() == 'notexists')
					this.txtCondition.hide();
				else
					this.txtCondition.show();
				
				this.cmbUnderOver.hide();
				this.rgSize.hide();
				break;
		}
		this.doLayout();
	},
	onShow : function(){
		GO.sieve.ConditionPanel.superclass.onShow.call(this);
	},
	resetForm : function(){
		this.btnAddRule.setText(t("Add"));
		this.btnClearRule.setText(t("Clear", "sieve"));
		this.form.reset();
		this.setVisibleFields();
		this.index=-1;
	}
});
