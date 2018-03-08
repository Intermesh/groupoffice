/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: AccountDialog.js 21672 2017-11-13 14:05:35Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.AccountDialog = function(config) {
	Ext.apply(this, config);

//	var sslCb;

	var advancedItems = [ new Ext.form.TextField({
		fieldLabel : GO.email.lang.port,
		name : 'port',
		value : '143',
		allowBlank : false
	}), new Ext.form.TextField({
		fieldLabel : GO.email.lang.rootMailbox,
		name : 'mbroot'
	}) ];

	if (GO.sieve) {
		advancedItems.push(
			new Ext.form.NumberField({
				fieldLabel : GO.sieve.lang.sievePort,
				name : 'sieve_port',
				decimals : 0,
				allowBlank : false,
				value:GO.sieve.sievePort
			})
		);
		advancedItems.push(
			new Ext.ux.form.XCheckbox({
				boxLabel: GO.sieve.lang.useTLS,
				checked:GO.sieve.sieveTls,
				name: 'sieve_usetls',
//				allowBlank: true,
				hideLabel:true
			})
		);
	}

	if(GO.addressbook){
				
		this.templatesCombo = new GO.form.ComboBox({
			fieldLabel : GO.email.lang['defaultEmailTemplate'],
			hiddenName : 'default_account_template_id',
			width: 300,
			store : new GO.data.JsonStore({
				url : GO.url("addressbook/template/accountTemplatesStore"),
				baseParams : {
					'type':"0"
				},
				root : 'results',
				totalProperty : 'total',
				id : 'id',
				fields : ['id', 'name', 'group', 'text','template_id','checked'],
				remoteSort : true
			}),
			value : '',
			valueField : 'id',
			displayField : 'name',
			typeAhead : true,
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true
		});
		
//		this.templatesBtn = new Ext.Button({
//
//			iconCls:'ml-btn-mailings',
//			text:GO.addressbook.lang.emailTemplate,
//			menu:this.templatesMenu = new GO.menu.JsonMenu({
//				store:this.templatesStore,
//				listeners:{
//					scope:this,
//					itemclick : function(item, e ) {
//						if(item.template_id=='default'){
//							this.templatesStore.baseParams.default_template_id=this.lastLoadParams.template_id;
//							this.templatesStore.load();
//							delete this.templatesStore.baseParams.default_template_id;
//						}else if(!this.emailEditor.isDirty() || confirm(GO.email.lang.confirmLostChanges))
//						{							
//							this.lastLoadParams.template_id=item.template_id;
//							this.lastLoadParams.keepHeaders=1;
//							this.loadForm(this.lastLoadUrl, this.lastLoadParams);							
//						}else
//						{
//							return false;							
//						}
//					}
//				}
//			})
//		});
		
	}

	this.imapAllowSelfSignedCheck = new Ext.ux.form.XCheckbox({
		boxLabel: GO.email.lang.allowSelfSignedText,
		name: 'imap_allow_self_signed',
		hideLabel:false,
		fieldLabel:''
	});
		

	var incomingTab = {
		title : GO.email.lang.incomingMail,
		layout : 'form',
		defaults : {
			anchor : '100%'
		},
		defaultType : 'textfield',
		autoHeight : true,
		cls : 'go-form-panel',
		waitMsgTarget : true,
		labelWidth : 120,
		items : [/*typeField = new Ext.form.ComboBox({
			fieldLabel : GO.email.lang.type,
			hiddenName : 'type',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [['imap', 'IMAP'],
				['pop3', 'POP-3']]

			}),
			value : 'imap',
			valueField : 'value',
			displayField : 'text',
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true,
			listeners : {
				change : function() {
					this.refreshNeeded = true;
				},
				scope : this
			}
		}),*/ new Ext.form.TextField({
			fieldLabel : 'IMAP '+GO.email.lang.host,
			name : 'host',
			allowBlank : false,
			listeners : {
				change : function() {
					this.refreshNeeded = true;
				},
				scope : this
			}
		}), new Ext.form.TextField({
			fieldLabel : GO.lang.strUsername,
			name : 'username',
			allowBlank : false,
			listeners : {
				change : function() {
					this.refreshNeeded = true;
				},
				scope : this
			}
		}),
		new Ext.ux.form.XCheckbox({
			boxLabel: GO.email.lang.storePassword,
			checked: true,
			name: 'store_password',
//			allowBlank: true,
			hideLabel:true,
			hidden: true //this function only works with imap auth at the moment.
		}),
		new Ext.form.TextField({
			fieldLabel : GO.lang.strPassword,
			name : 'password',
			inputType : 'password',
//			allowBlank : false,
			listeners : {
				change : function() {
					this.refreshNeeded = true;
				},
				scope : this
			}
		}),
		this.ImapEncryptionField = new Ext.form.ComboBox({
			fieldLabel : GO.email.lang.encryption,
			hiddenName : 'imap_encryption',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [
				['', GO.email.lang.noEncryption],
				['tls', 'TLS'], ['ssl', 'SSL']]
			}),
			value : '',
			valueField : 'value',
			displayField : 'text',
			typeAhead : true,
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true
		}),
		this.imapAllowSelfSignedCheck,
//		sslCb = new Ext.ux.form.XCheckbox({
//			fieldLabel : GO.email.lang.ssl,
//			name : 'use_ssl',
//			checked : false
//		}), 
		{
			xtype : 'fieldset',
			title : GO.email.lang.advanced,
			collapsible : true,
			forceLayout:true,
			collapsed : true,
			autoHeight : true,
			autoWidth : true,
			// defaults: {anchor: '100%'},
			defaultType : 'textfield',
			labelWidth : 75,
			labelAlign : 'left',

			items : advancedItems
		}]
	};

	// end incomming tab

	var properties_items = [
	this.selectUser = new GO.form.SelectUser({
		fieldLabel : GO.lang.strUser,
		disabled : !GO.settings.has_admin_permission,
		anchor : '100%'
	}),
	{
		fieldLabel : GO.lang.strName,
		name : 'name',
		allowBlank : false,
		anchor : '100%'
	}, {
		fieldLabel : GO.lang.strEmail,
		name : 'email',
		vtype: 'emailAddress',
		allowBlank : false,
		disabled:!GO.settings.modules.email.write_permission && GO.email.disableAliases,
		listeners : {
			change : function() {
				this.refreshNeeded = true;
			},
			scope : this
		},
		anchor : '100%'
	}, {
		xtype : 'textarea',
		name : 'signature',
		fieldLabel : GO.email.lang.signature,
		height : 100,
		anchor : '100%'
	}
	];

	this.aliasesButton = new Ext.Button({
		text : GO.email.lang.manageAliases,
		handler : function() {
			if (!this.aliasesDialog) {
				this.aliasesDialog = new GO.email.AliasesDialog();
			}
			this.aliasesDialog.show(this.account_id);
		},
		scope : this
	})

	this.doNotMarkAsReadCbx = new Ext.ux.form.XCheckbox({
		boxLabel: GO.email.lang.doNotMarkAsRead,
		fieldLabel: '',
		checked:false,
		name: 'do_not_mark_as_read',
//		allowBlank: true
	});
	
	this.fullReplyHeadersCbx = new Ext.ux.form.XCheckbox({
		boxLabel: GO.email.lang.fullReplyHeaders,
		fieldLabel: '',
		checked:false,
		name: 'full_reply_headers',
//		allowBlank: true
	});
	
	this.placeSignatureBelowReplyCbx = new Ext.ux.form.XCheckbox({
		boxLabel: GO.email.lang.placeSignatureBelowReply,
		fieldLabel: '',
		checked:false,
		name: 'signature_below_reply',
//		allowBlank: true
	});
	
	properties_items.push(this.placeSignatureBelowReplyCbx);
	properties_items.push(this.doNotMarkAsReadCbx);
	properties_items.push(this.fullReplyHeadersCbx);

	if (GO.addressbook)
		properties_items.push(this.templatesCombo);

	if(GO.settings.modules.email.write_permission || !GO.email.disableAliases)
		properties_items.push(this.aliasesButton);
	
	var propertiesTab = {
		title : GO.lang.strProperties,
		layout : 'form',
		anchor : '100% 100%',
		defaultType : 'textfield',
		autoHeight : true,
		cls : 'go-form-panel',
		labelWidth : 100,
		items :properties_items
	};

	this.smtpAllowSelfSignedCheck = new Ext.ux.form.XCheckbox({
		boxLabel: GO.email.lang.allowSelfSignedText,
		name: 'smtp_allow_self_signed',
		hideLabel:false,
		fieldLabel:''
	});

	var outgoingTab = {
		title : GO.email.lang.outgoingMail,
		layout : 'form',
		defaults : {
			anchor : '100%'
		},
		defaultType : 'textfield',
		autoHeight : true,
		cls : 'go-form-panel',
		labelWidth : 120,
		items : [new Ext.form.TextField({
			fieldLabel : GO.email.lang.host,
			name : 'smtp_host',
			allowBlank : false,
			value : GO.email.defaultSmtpHost
		}), this.encryptionField = new Ext.form.ComboBox({
			fieldLabel : GO.email.lang.encryption,
			hiddenName : 'smtp_encryption',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [
				['', GO.email.lang.noEncryption],
				['tls', 'TLS'], ['ssl', 'SSL']]
			}),
			value : '',
			valueField : 'value',
			displayField : 'text',
			typeAhead : true,
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true
		}),
		this.smtpAllowSelfSignedCheck,
		new Ext.form.TextField({
			fieldLabel : GO.email.lang.port,
			name : 'smtp_port',
			value : '25',
			allowBlank : false
		}),
		{
			xtype:'xcheckbox',
			checked: false,
			name: 'smtp_auth',
			hideLabel:true,
			boxLabel:GO.email.lang.useAuth,
			listeners:{
				check:function(cb, checked){
					this.smtpUsername.setDisabled(!checked);
					this.smtpPassword.setDisabled(!checked);
				},
				scope:this
			}
		},this.smtpUsername= new Ext.form.TextField({
			fieldLabel : GO.lang.strUsername,
			name : 'smtp_username',
			disabled:true
		}),
		this.smtpPasswordStore = new Ext.ux.form.XCheckbox({
			checked: true,
			name: 'store_smtp_password',
//			allowBlank: true,
			hideLabel:true,
			hidden: true
		}),
		this.smtpPassword = new Ext.form.TextField({
			fieldLabel : GO.lang.strPassword,
			name : 'smtp_password',
			inputType : 'password',
			disabled:true
		})]
	};

	GO.email.subscribedFoldersStore = new GO.data.JsonStore({
		url : GO.url("email/folder/store"),
		baseParams : {
			task : 'subscribed_folders',
			account_id : 0
		},
		fields : ['name']
	});

	this.foldersTab = new Ext.Panel({
		listeners:{
			show:function(){
				if(!GO.email.subscribedFoldersStore.loaded)
					GO.email.subscribedFoldersStore.load();
			}
		},
		title : GO.email.lang.folders,
		autoHeight : true,
		layout : 'form',
		cls : 'go-form-panel',
		defaults : {
			anchor : '100%'
		},
		defaultType : 'textfield',
		labelWidth : 150,
		tbar : [{
			iconCls : 'btn-add',
			text : GO.email.lang.manageFolders,
			cls : 'x-btn-text-icon',
			scope : this,
			handler : function() {

				if (!this.foldersDialog) {
					this.foldersDialog = new GO.email.FoldersDialog();
				}
				this.foldersDialog.show(this.account_id);

			}
		}],

		items : [new GO.form.ComboBoxReset({
			fieldLabel : GO.email.lang.sendItemsFolder,
			hiddenName : 'sent',
			store : GO.email.subscribedFoldersStore,
			valueField : 'name',
			displayField : 'name',
			value:'Sent',
			typeAhead : true,
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true,
			emptyText : GO.lang.disabled
		}), new GO.form.ComboBoxReset({
			fieldLabel : GO.email.lang.trashFolder,
			hiddenName : 'trash',
			value:'Trash',
			store : GO.email.subscribedFoldersStore,
			valueField : 'name',
			displayField : 'name',
			typeAhead : true,
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true,
			emptyText : GO.lang.disabled
		}), new GO.form.ComboBoxReset({
			fieldLabel : GO.email.lang.draftsFolder,
			hiddenName : 'drafts',
			value:'Drafts',
			store : GO.email.subscribedFoldersStore,
			valueField : 'name',
			displayField : 'name',
			typeAhead : true,
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true,
			emptyText : GO.lang.disabled
		}), new Ext.ux.form.XCheckbox({
			boxLabel : GO.email.lang.ignoreSentFolder,
			name : 'ignore_sent_folder',
			checked : false,
			hideLabel : true
		})]
	});
	
	var levelLabels={};
	levelLabels[GO.permissionLevels.create]=GO.email.lang.useAccount;
	levelLabels[GO.email.permissionLevels.delegated]=GO.email.lang['permissionDelegated'];

	this.permissionsTab = new GO.grid.PermissionsPanel({levels:[
			GO.permissionLevels.read,
			GO.email.permissionLevels.delegated,
			GO.permissionLevels.create,
			GO.permissionLevels.manage
	  ],
	  levelLabels:levelLabels
	});

	//this.permissionsTab.disabled = false;
	
	
	this.filterGrid = new GO.email.FilterGrid();

	var items = [propertiesTab,

	this.foldersTab, this.permissionsTab,this.filterGrid];

	this.labelsTab = new GO.email.LabelsGrid();

	items.push(this.labelsTab);

	if (GO.settings.modules.email.write_permission) {
		items.splice(1, 0, incomingTab, outgoingTab);
	}

	this.propertiesPanel = new Ext.form.FormPanel({
		url : GO.url("email/account/submit"),
		// labelWidth: 75, // label settings here cascade unless
		// overridden,
		baseParams:{
			ajax:true
		},
		defaults:{forceLayout:true},
		defaultType : 'textfield',
		waitMsgTarget : true,
		labelWidth : 120,
		border : false,
		items : [this.tabPanel = new Ext.TabPanel({
			hideLabel : true,
			deferredRender : false,
			activeTab : 0,
			border : false,
			anchor : '100% 100%',
			items : items,
			enableTabScroll:true
		})]

	});

	/*typeField.on('select', function(combo, record, index) {

		var value = index == 1 ? '110' : '143';

		this.propertiesPanel.form.findField('port').setValue(value);
	}, this);*/

	this.encryptionField.on('select', function(combo, record, index) {
		var value = record.data.value == 'ssl' ? '465' : '587';
		
		this.propertiesPanel.form.findField('smtp_port')
		.setValue(value);
	}, this);
	
	this.ImapEncryptionField.on('select', function(combo, record, index) {
		var value = record.data.value == 'ssl' ? '993':'143';
		this.propertiesPanel.form.findField('port')
		.setValue(value);
	}, this);
	
	

//	sslCb.on('check', function(checkbox, checked) {
//		//if (typeField.getValue() == 'imap') {
//			var port = checked ? 993 : 143;
//			this.propertiesPanel.form.findField('port').setValue(port);
//		/*} else {
//			var port = checked ? 995 : 110;
//			this.propertiesPanel.form.findField('port').setValue(port);
//		}*/
//	}, this);

	this.selectUser.on('select', function(combo, record, index) {
		if(GO.util.empty(this.account_id)){
			this.propertiesPanel.form.findField('email')
			.setValue(record.data.email);
			this.propertiesPanel.form.findField('username')
			.setValue(record.data.username);
			this.propertiesPanel.form.findField('name')
			.setValue(record.data.name);
		}
	}, this);

	GO.email.AccountDialog.superclass.constructor.call(this, {
		layout : 'fit',
		modal : false,
		height: 600,
		width : 900,
		closeAction : 'hide',
		title : GO.email.lang.account,

		items : this.propertiesPanel,

		buttons : [{

			text : GO.lang.cmdOk,
			handler : function() {
				this.save(true);
			},
			scope : this
		}, {

			text : GO.lang.cmdApply,
			handler : function() {
				this.save(false);
			},
			scope : this
		}, {

			text : GO.lang.cmdClose,
			handler : function() {
				this.hide();
			},
			scope : this
		}]
	});

	this.addEvents({
		'save' : true
	});

}

Ext.extend(GO.email.AccountDialog, GO.Window, {

	save : function(hide) {
		this.propertiesPanel.form.submit({

			url : GO.url("email/account/submit"),
			params : {
				'id' : this.account_id
			},
			waitMsg : GO.lang['waitMsgSave'],
			success : function(form, action) {

				action.result.refreshNeeded = this.refreshNeeded
				|| this.account_id == 0;
				if (action.result.id) {
					//this.account_id = action.result.account_id;
					// this.foldersTab.setDisabled(false);
					this.loadAccount(action.result.id);
				}
				
				//This will reload the signature when it is changed
				if(GO.email.composers && GO.email.composers[0]) {
					GO.email.composers[0].fromCombo.store.reload();
				}
				this.refreshNeeded = false;
				this.fireEvent('save', this, action.result);

				if (hide) {
					this.hide();
				}

			},

			failure : function(form, action) {
				var error = '';
				if (action.failureType == 'client') {
					error = GO.lang.strErrorsInForm;
				} else if (action.result) {
					error = action.result.feedback;
				} else {
					error = GO.lang.strRequestError;
				}

				Ext.MessageBox.alert(GO.lang.strError, error);
				
				if(action.result.validationErrors){
					for(var field in action.result.validationErrors){
						form.findField(field).markInvalid(action.result.validationErrors[field]);
					}
				}
			},
			scope : this

		});

	},
	show : function(account_id) {
		GO.email.AccountDialog.superclass.show.call(this);

		this.tabPanel.setActiveTab(0);

		this.aliasesButton.setDisabled(true);
		if (account_id) {
			this.loadAccount(account_id);
			GO.email.subscribedFoldersStore.baseParams.account_id = account_id;
//			GO.email.subscribedFoldersStore.load();

			GO.email.subscribedFoldersStore.loaded=false;
		} else {

			this.propertiesPanel.form.reset();
			this.setAccountId(0);
			this.foldersTab.setDisabled(true);
			this.permissionsTab.setAcl(0);

			// default values

			// this.selectUser.setValue(GO.settings['user_id']);
			// this.selectUser.setRawValue(GO.settings['name']);
			// this.selectUser.lastSelectionText=GO.settings['name'];

			this.propertiesPanel.form.findField('name')
			.setValue(GO.settings['name']);
			this.propertiesPanel.form.findField('email')
			.setValue(GO.settings['email']);
			this.propertiesPanel.form.findField('username')
			.setValue(GO.settings['username']);

		}
	},

	loadAccount : function(account_id) {
		this.propertiesPanel.form.load({
			url : GO.url("email/account/load"),
			params : {
				id : account_id
			},
			waitMsg : GO.lang.waitMsgLoad,
			success : function(form, action) {
				this.refreshNeeded = false;

				this.setAccountId(account_id);

				this.selectUser.setRemoteText(action.result.remoteComboTexts.user_id);

				this.aliasesButton.setDisabled(false);

				this.foldersTab.setDisabled(false);

				if(!action.result.data.email_enable_labels) {
					this.tabPanel.hideTabStripItem(this.labelsTab);
				} else {
					this.tabPanel.unhideTabStripItem(this.labelsTab);
				}

				this.permissionsTab.setAcl(action.result.data.acl_id);
				
				if (this.templatesCombo) {
					this.templatesCombo.store.load();
					this.templatesCombo.setRemoteText(action.result.remoteComboTexts['default_template_id']);
				}
			},
			scope : this
		});
	},

	setAccountId : function(account_id){
		this.account_id = account_id;
		this.filterGrid.setAccountId(account_id);
		this.labelsTab.setAccountId(account_id);
	}
});
