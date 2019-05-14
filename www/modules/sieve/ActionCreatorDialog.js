/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @author WilmarVB <wilmar@intermesh.nl>
 */

GO.sieve.ActionRecord = Ext.data.Record.create([
	{
		name: 'id',
		type: 'integer'
	},
	{
		name: 'type',
		type: 'string'
	},
	{
		name: 'copy',
		type: 'string'
	},
	{
		name: 'target',
		type: 'string'
	},
	{
		name: 'days',
		type: 'string'
	},
	{
		name: 'addresses',
		type: 'string'
	},
	{
		name: 'reason',
		type: 'string'
	}]);

GO.sieve.ActionCreatorDialog = function(config){
	config = config || {};

	this._buildForm();

//	this.btnAddAction = new Ext.Button({
//		text: t("Add"),
//		handler : function() {
//			this._prepareValuesForStorage();
//			this.resetForm();
//		},
//		scope : this
//	})
//
//	this.btnClearAction = new Ext.Button({
//		text: t("Clear", "sieve"),
//		handler : function() {
//			this.resetForm();
//		},
//		scope : this
//	})

	config.title=t("Set action", "sieve");
	config.border=false;
	config.layout= 'fit';
	config.height = dp(600);
	config.width= dp(600);
	config.baseParams={
		task : 'addAction',
		account_id : 0,
		script_name : '',
		rule_name : '',
		script_index : 0
	};
	config.items=[this.formPanel];
	
	config.buttons = [{
		text : t("Ok"),
		handler : function() {
			if (this.formPanel.getForm().isValid()) {
				this.fireEvent('actionPrepared',this._prepareValuesForStorage());
				this.hide();
				this._resetForm();
			}
		},
		scope : this
	}, {
		text : t("Cancel"),
		handler : function() {
			this.hide();
			this._resetForm();
		},
		scope : this
	}];
	
	GO.sieve.ActionCreatorDialog.superclass.constructor.call(this, config);
	
	this.addEvents({'actionPrepared':true});
}

Ext.extend(GO.sieve.ActionCreatorDialog, GO.Window,{

	_recordId : -1,

	show : function(record) {
		this._recordId=-1;
		this._resetForm();
		if (typeof(record)=='object') {
			this._recordId = record.get('id');

			if (this._recordId==-1)
				this.txtEmailAddressOptional.setValue(record.get('addresses'));
			
			this.cmbAction.setValue(record.get('type'));
			this._transForm(record.get('type'));
			switch (record.get('type')) {
				case 'fileinto':
				case 'fileinto_copy':
					this.cmbFolder.setValue(record.get('target'));
					break;
				case 'redirect':
				case 'redirect_copy':
					this.txtEmailAddress.setValue(record.get('target'));
					break;
				case 'reject':
					this.txtMessage.setValue(record.get('value'));
					this.txtMessage.setValue(record.get('target'));
					break;
				case 'vacation':
					this.txtEmailAddressOptional.setValue(record.get('addresses'));
					this.txtDays.setValue(record.get('days'));
					this.txtMessage.setValue(record.get('reason'));
					break;
				case 'addflag':
					if(record.get('target') == '\\Seen'){
						this.cmbAction.setValue('set_read');
					}
					break;
				default:
					break;
			}
		}
		GO.sieve.ActionCreatorDialog.superclass.show.call(this);
	},

	_prepareValuesForStorage : function() {
		// Build up the data before adding the data to the grid.
		var _type = '';
		var _target = '';
		var _days = '';
		var _addresses = '';
		var _reason = '';
		var _text = '';

		switch(this.cmbAction.getValue())
		{
			case 'set_read':
				_type		= 'addflag';
				_target = '\\Seen';
				_text		= t("Mark message as read", "sieve");
				break;
			case 'fileinto':
				_type		= 'fileinto';
				_target = this.cmbFolder.getValue();
				_text		= t("Move email to the folder", "sieve")+': '+_target;
				
				break;
			case 'fileinto_copy':
				_type		= 'fileinto_copy';
				_target = this.cmbFolder.getValue();
				_text		= t("Copy email to the folder", "sieve")+': '+_target;
				break;
			case 'redirect':
				_type		= 'redirect';
				_target = this.txtEmailAddress.getValue();
				_text		= t("Redirect to", "sieve")+': '+_target;
				break;
			case 'redirect_copy':
				_type		= 'redirect_copy';
				_target = this.txtEmailAddress.getValue();
				_text		= t("Send a copy to", "sieve")+': '+_target;
				break;
			case 'reject':
				_type		= 'reject';
				_target = this.txtMessage.getValue();
				_text		= t("Reject with message", "sieve")+': "'+_target+'"';
				break;
			case 'vacation':
				_type = 'vacation';
				_target = '';
				_days = this.txtDays.getValue();
				_addresses = this.txtEmailAddressOptional.getValue();
				_reason = this.txtMessage.getValue();
				if (!GO.util.empty(_addresses))
					var addressesText = t("Autoreply is active for", "sieve")+': '+_addresses+'. ';
				else
					var addressesText = '';
				_text = t("Reply every", "sieve")+' '+_days+' '+t("day(s)", "sieve")+'. '+addressesText+t("Message:", "sieve")+' "'+_reason+'"';
				break;
			case 'discard':
				_type		= 'discard';
				_target = '';
				_text		= t("Discard", "sieve");
				break;
			case 'stop':
				_type		= 'stop';
				_target = '';
				_text		= t("Stop", "sieve");
				break;
			default:
				break;
		}

		return {
			id : this._recordId,
			type:_type,
			target:_target,
			days: _days,
			reason: _reason,
			addresses: _addresses,
			text: _text
		};
	},

	_resetForm : function(){
		this.formPanel.getForm().reset();
		this._toggleComponentUse(this.cmbAction,true);
		this._toggleComponentUse(this.cmbFolder,false);
		this._toggleComponentUse(this.txtEmailAddressOptional,false);
		this._toggleComponentUse(this.txtEmailAddress,false);
		this._toggleComponentUse(this.txtMessage,false);
		this._toggleComponentUse(this.txtDays,false);
		this.doLayout();
		
		if(!GO.email.subscribedFoldersStore.loaded)
			GO.email.subscribedFoldersStore.load();
	},

	_transForm : function(type) {
		switch (type) {
			case 'fileinto':
			case 'fileinto_copy':
				this._toggleComponentUse(this.cmbFolder,true);
				this._toggleComponentUse(this.txtEmailAddressOptional,false);
				this._toggleComponentUse(this.txtEmailAddress,false);
				this._toggleComponentUse(this.txtDays,false);
				this._toggleComponentUse(this.txtMessage,false);
				break;
			case 'redirect':
			case 'redirect_copy':
				this._toggleComponentUse(this.cmbFolder,false);
				this._toggleComponentUse(this.txtEmailAddressOptional,false);
				this._toggleComponentUse(this.txtEmailAddress,true);
				this._toggleComponentUse(this.txtDays,false);
				this._toggleComponentUse(this.txtMessage,false);
				break;
			case 'reject':
				this._toggleComponentUse(this.cmbFolder,false);
				this._toggleComponentUse(this.txtEmailAddressOptional,false);
				this._toggleComponentUse(this.txtEmailAddress,false);
				this._toggleComponentUse(this.txtDays,false);
				this._toggleComponentUse(this.txtMessage,true);
				break;
			case 'vacation':
				this._toggleComponentUse(this.cmbFolder,false);
				this._toggleComponentUse(this.txtEmailAddressOptional,true);
				this._toggleComponentUse(this.txtEmailAddress,false);
				this._toggleComponentUse(this.txtDays,true);
				this._toggleComponentUse(this.txtMessage,true);
				break;
			case 'set_read':
			case 'discard':
			case 'stop':
				this._toggleComponentUse(this.cmbFolder,false);
				this._toggleComponentUse(this.txtEmailAddressOptional,false);
				this._toggleComponentUse(this.txtEmailAddress,false);
				this._toggleComponentUse(this.txtDays,false);
				this._toggleComponentUse(this.txtMessage,false);
				break;
		}
	},

	_buildForm : function() {
		this.cmbAction = new GO.form.ComboBox({
			hiddenName: 'type',
			fieldLabel:t("Action", "sieve"),
			valueField:'value',
			displayField:'field',
			store: GO.sieve.cmbActionStore,
			mode:'local',
			triggerAction:'all',
			editable:false,
			selectOnFocus:true,
			forceSelection:true,
			allowBlank:false,
			width:300,
			emptyText:t("Action", "sieve")
		});

		this.cmbFolder = new GO.form.ComboBox({
			hiddenName:'target',
			fieldLabel:t("To folder", "sieve"),
			valueField:'name',
			value: 'INBOX',
			displayField:'name',
			store: GO.email.subscribedFoldersStore,
			mode:'local',
			triggerAction:'all',
			editable:false,
			selectOnFocus:true,
			forceSelection:true,
			allowBlank:false,
			width:300,
			hidden: true,
			disabled: true
		});

		this.txtEmailAddressOptional = new Ext.form.TextArea({
			name: 'email',
			allowBlank:true,
			anchor: '100%',
			fieldLabel:t("Activate also for these aliases (separated by comma)", "sieve"),
			hidden: true,
			disabled: true
		});

		this.txtEmailAddress = new Ext.form.TextField({
			name: 'email',
			allowBlank:false,
			vtype:'emailAddress',
			anchor: '100%',
			fieldLabel:t("E-mail"),
			hidden: true,
			disabled: true
		});

		this.txtMessage = new Ext.form.TextArea({
			name: 'message',
			allowBlank:false,
//			hideLabel:true,
			anchor:'100%',
			height:200,
			fieldLabel:t("Message", "sieve"),
//			listeners:{
//				scope:this,
//				focus: function(){
//					this.setHeight(100);
//				}
//			},
			disabled: true,
			hidden: true
		});

		this.txtDays = new GO.form.NumberField({
			name: 'days',
			hidden:true,
			allowBlank:false,
			width:70,
			decimals:0,
			fieldLabel:t("Reply every x days", "sieve"),
			disabled: true,
			minValue:1,
			value:7
		});
			
		this.formPanel = new Ext.form.FormPanel({
			layout: 'form',
			border:false,
			items: [ {
				xtype: "fieldset",				
				items: [
					this.cmbAction,
					this.cmbFolder,
					this.txtDays,
					this.txtMessage,
					this.txtEmailAddressOptional,
					this.txtEmailAddress
				]
			}]
		});
		
		this.cmbAction.on('select',function(combo,record,index){
			this.cmbFolder.reset();
			this.txtEmailAddress.reset();
			this.txtDays.reset();
			this.txtMessage.reset();
			this._transForm(record.data.value);
		},this);
	},
	
	_toggleComponentUse : function (component,use) {
		if (use==true) {
			component.show();
			component.setDisabled(false);
			if (component == this.txtDays)
				this.txtDays.setValue(7);
		} else {
			component.hide();
			component.setDisabled(true);
		}
	}
});
