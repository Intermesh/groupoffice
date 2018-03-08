/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author WilmarVB <wilmar@intermesh.nl>
 */

// THIS DIALOG IS NOT (YET) IN USE

GO.sieve.VacationDialog = Ext.extend(GO.dialog.TabbedFormDialog,{
	
	remoteModelIdName: 'account_id',
	submitAction : 'submitRules',
	loadAction : 'loadOutOfOffice',
	
	initComponent : function(){
		
		Ext.apply(this, {
			titleField: false,
			title:GO.sieve.lang.autoReplyMessage,
			formControllerUrl: 'sieve/sieve',
			autoScroll: true,
			border: false,
			width:640,
			height:380
			//fileUpload:true
		});
		
		GO.sieve.VacationDialog.superclass.initComponent.call(this);	
	},
	
	getSubmitParams : function() {
		return {
			'actions' : Ext.encode([{
				'type' : 'vacation',
				'days' : this.nDaysField.getValue(),
				'reason' : this.messageField.getValue(),
				'addresses' : this.emailAddressesField.getValue()
			}]),
			'criteria' : Ext.encode([{
				'test' : 'currentdate',
				'type' : 'value',
				'arg1' : 'ge',
				'arg2' : this.startDate.getValue()
			},{
				'test' : 'currentdate',
				'type' : 'value',
				'arg1' : 'le',
				'arg2' : this.endDate.getValue()
			}]),
			'script_name' : this.scriptName,
			'script_index' : this.scriptIndex
		};
	},
	
	beforeLoad : function(remoteModelId, config) {
		if (remoteModelId>0) {
			this.scriptName = config.script_name;
		} else {
			this._accountId = 0;
			Ext.MessageBox.alert(GO.lang.strError,'Attempt was made to open Out of office dialog, but the required account id is lacking or invalid. Please contact the administrator.');
		}
	},
	
	afterLoad : function(remoteModelId, config, action){
		var responseData = Ext.decode(action.response.responseText);
		if (GO.util.empty(responseData.data['script_index']))
			this.scriptIndex = -1;
		else {
			this.scriptIndex = responseData.data['script_index'];
//				this.startDate,
//				this.endDate,
			this.emailAddressesField.setValue(responseData.data['actions'][0]['addresses']);
			this.nDaysField.setValue(responseData.data['actions'][0]['days']);
			this.messageField.setValue(responseData.data['actions'][0]['reason']);
			this.disabledCheckbox.setValue(responseData.data.disabled);
			this.startDate.setValue();
			this.endDate.setValue();
			responseData.data['actions'][0]['type']
		}
	},
	
//	_submitForm : function() {
//		this.formPanel.form.submit({
//			url: 'sieve/sieve/submitOutOfOffice',
//			params: {
//				'accountId' : this._remoteModelId
//			},
//			waitMsg : GO.lang['waitMsgSave'],
//			success : function(form, action) {
//				this.hide();
//			},
//			failure : function(form, action) {
//				var error = '';
//				if (action.failureType == 'client') {
//					error = GO.lang.strErrorsInForm;
//				} else if (action.result) {
//					error = action.result.feedback;
//				} else {
//					error = GO.lang.strRequestError;
//				}
//
//				Ext.MessageBox.alert(GO.lang.strError, error);
//			},
//			scope : this
//		});
//	},
	
	buildForm : function() {
		this.joinField = new Ext.form.TextField({
			name: 'join',
			hidden: true,
			disabled: false,
			value: 'allof'
		});
		this.extraCheck = new Ext.form.TextField({
			name: 'fromVacationDialog',
			hidden: true,
			disabled: false,
			value: 'true'
		});
		this.nameField = new Ext.form.TextField({
			name: 'rule_name',
			hidden: true,
			disabled: false,
			value: 'Autoreply'
		});
		this.disabledCheckbox = new Ext.form.Checkbox({
			name:'disabled',
			checked:false,
			fieldLabel:GO.sieve.lang.disablefilter
		});
		this.messageField = new Ext.form.TextArea({
			name: 'message',
			allowBlank:false,
			anchor:'100%',
			height:80,
			width: 300,
			fieldLabel:GO.sieve.lang.reason
		});
		this.nDaysField = new GO.form.NumberField({
			name: 'days',
			value: 3,
			allowBlank:false,
			width:70,
			decimals:0,
			fieldLabel:GO.sieve.lang.days
		});
		this.startDate = new Ext.form.DateField({
			name : 'start_date',
			width : 100,
			format : GO.settings['date_format'],
			fieldLabel: GO.sieve.lang.vacationStart,
			allowBlank : false
		});
		this.endDate = new Ext.form.DateField({
			name : 'end_date',
			width : 100,
			format : GO.settings['date_format'],
			fieldLabel: GO.sieve.lang.vacationEnd,
			allowBlank : false
		});
		this.emailAddressesField = new Ext.form.TextArea({
			name: 'email',
			allowBlank:true,
			width:300,
			fieldLabel:GO.sieve.lang.addressesLabelOptional
		});
		
		this.addPanel({
//			url: 'sieve/sieve/loadOutOfOffice',
			bodyStyle: 'padding: 5px;',
			border:false,
			layout: 'form',
			items: [
				this.joinField,
				this.extraCheck,
				this.nameField,
				this.disabledCheckbox,
				this.messageField,
				this.nDaysField,
				this.startDate,
				this.endDate,
				this.emailAddressesField
			]
		});
		
	}
	
});