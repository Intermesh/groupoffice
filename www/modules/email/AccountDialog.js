/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: AccountDialog.js 22204 2018-01-22 14:49:13Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.AccountDialog = function(config) {
	Ext.apply(this, config);

	var advancedItems = [ new Ext.form.TextField({
		fieldLabel : t("Root mailbox", "email"),
		name : 'mbroot'
	}) ];

	if(go.Modules.isAvailable("legacy", "sieve")) {

		advancedItems.push(
			new Ext.form.NumberField({
				fieldLabel : t("Sieve filter port number", "sieve"),
				name : 'sieve_port',
				decimals : 0,
				allowBlank : false,
				value:GO.sieve.sievePort
			})
		);
		advancedItems.push(
			new Ext.ux.form.XCheckbox({
				boxLabel: t("Use secure connection for (Sieve) email filters", "sieve"),
				checked:GO.sieve.sieveTls,
				name: 'sieve_usetls',
				hideLabel:true
			})
		);
	}

				
		this.templatesCombo = new GO.form.ComboBox({
			fieldLabel : t("Default e-mail template", "email"),
			hiddenName : 'default_account_template_id',
			width: 300,
			store : new GO.data.JsonStore({
				url : GO.url("email/template/accountTemplatesStore"),
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
		
	

	this.imapAllowSelfSignedCheck = new Ext.ux.form.XCheckbox({
		boxLabel: t("Allow self signed certificate when using SSL or TLS", "email"),
		name: 'imap_allow_self_signed',
		hideLabel:false,
		fieldLabel:''
	});

	var incomingTab = {
		title : t("Incoming mail", "email"),
		layout : 'form',
		defaults : {
			anchor : '100%'
		},
		defaultType : 'textfield',
		autoHeight : true,
		cls : 'go-form-panel',
		waitMsgTarget : true,
		labelWidth : 120,
		items : [new Ext.form.TextField({
			fieldLabel : 'IMAP '+t("Host", "email"),
			name : 'host',
			allowBlank : false,
			listeners : {
				change : function() {
					this.refreshNeeded = true;
				},
				scope : this
			}
		}),
		new Ext.form.TextField({
			fieldLabel : t("Port", "email"),
			name : 'port',
			value : '143',
			allowBlank : false
		}), new Ext.form.TextField({
			fieldLabel : t("Username"),
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
			boxLabel: t("Permanently store password", "email"),
			checked: true,
			name: 'store_password',
			hideLabel:true,
			hidden: true //this function only works with imap auth at the moment.
		}),
		new Ext.form.TextField({
			fieldLabel : t("Password"),
			name : 'password',
			inputType : 'password',
			listeners : {
				change : function() {
					this.refreshNeeded = true;
				},
				scope : this
			}
		}),
		this.ImapEncryptionField = new Ext.form.ComboBox({
			fieldLabel : t("Encryption", "email"),
			hiddenName : 'imap_encryption',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [
				['', t("No encryption", "email")],
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
		this.imapAllowSelfSignedCheck]
	};

	// end incomming tab

	var properties_items = [
	this.selectUser = new GO.form.SelectUser({
		fieldLabel : t("User"),
		disabled : !GO.settings.has_admin_permission,
		anchor : '100%'
	}),
	{
		fieldLabel : t("Name"),
		name : 'name',
		allowBlank : false,
		anchor : '100%'
	}, {
		fieldLabel : t("E-mail"),
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
		fieldLabel : t("Signature", "email"),
		height : 100,
		anchor : '100%'
	}
	];

	properties_items.push(this.templatesCombo);

	this.smtpAllowSelfSignedCheck = new Ext.ux.form.XCheckbox({
		boxLabel: t("Allow self signed certificate when using SSL or TLS", "email"),
		name: 'smtp_allow_self_signed',
		hideLabel:false,
		fieldLabel:''
	});
	
	var outgoingTab = {
		title : t("Outgoing mail", "email"),
		layout : 'form',
		xtype:'fieldset',
		defaults : {
			anchor : '100%'
		},
		defaultType : 'textfield',
		autoHeight : true,
		cls : 'go-form-panel',
		labelWidth : 120,
		items : [new Ext.form.TextField({
			fieldLabel : t("Host", "email"),
			name : 'smtp_host',
			allowBlank : false,
			value : GO.email.defaultSmtpHost
		}),new Ext.form.TextField({
			fieldLabel : t("Port", "email"),
			name : 'smtp_port',
			value : '25',
			allowBlank : false
		}), this.encryptionField = new Ext.form.ComboBox({
			fieldLabel : t("Encryption", "email"),
			hiddenName : 'smtp_encryption',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [
				['', t("No encryption", "email")],
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
		this.imapCredentialsCbx = new Ext.ux.form.XCheckbox({
			hideLabel: true,
			boxLabel: t("Use IMAP credentials", "email","communtiy"),
			name: 'force_smtp_login',
			handler: function(cb, checked) {
				if(checked) {
					this.smtpUsername.hide();
					this.smtpPassword.hide();
					this.useSmtpAuthentication.hide();
				} else {
					this.smtpUsername.show();
					this.smtpPassword.show();
					this.useSmtpAuthentication.show();
				}

			},
			scope: this
		}),

		this.useSmtpAuthentication = new Ext.ux.form.XCheckbox({
			checked: false,
			name: 'smtp_auth',
			hideLabel:true,
			boxLabel:t("Use authentication", "email"),
			listeners:{
				check:function(cb, checked){
					this.smtpUsername.setDisabled(!checked);
					this.smtpPassword.setDisabled(!checked);
				},
				scope:this
			}
		}),this.smtpUsername= new Ext.form.TextField({
			fieldLabel : t("Username"),
			name : 'smtp_username',
			disabled:true
		}),
		this.smtpPasswordStore = new Ext.ux.form.XCheckbox({
			checked: true,
			name: 'store_smtp_password',
			hideLabel:true,
			hidden: true
		}),
		this.smtpPassword = new Ext.form.TextField({
			fieldLabel : t("Password"),
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

	this.foldersTab = new Ext.form.FieldSet({
		title : t("Folders", "email"),
		autoHeight : true,
		layout : 'form',
		cls : 'go-form-panel',
		defaults : {
			anchor : '100%'
		},
		defaultType : 'textfield',
		labelWidth : 150,

		items : [new GO.form.ComboBoxReset({
			fieldLabel : t("Sent items folder", "email"),
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
			emptyText : t("Disabled")
		}), new GO.form.ComboBoxReset({
			fieldLabel : t("Trash folder", "email"),
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
			emptyText : t("Disabled")
		}), new GO.form.ComboBoxReset({
			fieldLabel : t("Drafts folder", "email"),
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
			emptyText : t("Disabled")
		})]
	});
	
	var propertiesTab = {
		title : t("Properties"),
		autoScroll: true,
		layout:'table',
		layoutConfig: {columns: 2},
		items: [{
				title : t("Properties"),
				xtype:'fieldset',
				layout : 'form',
				anchor : '100% 100%',
				defaultType : 'textfield',
				autoHeight : true,
				cls : 'go-form-panel',
				labelWidth : 100,
				items :properties_items
			},{ 
				rowspan: 2,
				defaults: {xtype:'fieldset'},
				items: [
					this.foldersTab,
					{
						xtype : 'fieldset',
						title : t("Extra options", "email"),
						forceLayout:true,
						labelWidth : 75,
						labelAlign : 'left',
						defaults: {hideLabel : true, checked:false},
						items : [
							new Ext.ux.form.XCheckbox({
								boxLabel : t("Store replies in the same folder as the original message", "email"),
								name : 'ignore_sent_folder'
							}),
							this.doNotMarkAsReadCbx = new Ext.ux.form.XCheckbox({
								boxLabel: t("Do not automatically mark emails as read", "email"),
								name: 'do_not_mark_as_read',
							}),
							this.fullReplyHeadersCbx = new Ext.ux.form.XCheckbox({
								boxLabel: t("Show full reply headers", "email"),
								name: 'full_reply_headers',
							}),
							this.placeSignatureBelowReplyCbx = new Ext.ux.form.XCheckbox({
								boxLabel: t("On reply/forward: Place signature always at the end of the mail.", "email"),
								name: 'signature_below_reply',
							})
						]
					}
				]
			}
		]
	};
	
	var levelLabels={};
	levelLabels[GO.permissionLevels.create]=t("Use account", "email");
	levelLabels[GO.email.permissionLevels.delegated]=t("Read only and delegated", "email");

	this.permissionsTab = new GO.grid.PermissionsPanel({levels:[
			GO.permissionLevels.read,
			GO.email.permissionLevels.delegated,
			GO.permissionLevels.create,
			GO.permissionLevels.manage
	  ],
	  levelLabels:levelLabels
	});

	var serverTab = {
		title: t('Server', 'email'),
		autoScroll: true,
		disabled: (!GO.settings.modules.email.write_permission),
		layout:'table',
		layoutConfig: {columns: 2},
		items: [{ 
				rowspan: 2,
				defaults: {xtype:'fieldset'},
				items: [
					incomingTab,
					{
						xtype : 'fieldset',
						title : t("Advanced", "email"),
						collapsible : true,
						forceLayout:true,
						collapsed : true,
						autoHeight : true,
						autoWidth : true,
						defaultType : 'textfield',
						labelWidth : 75,
						labelAlign : 'left',

						items : advancedItems
					}
				]
		},
		outgoingTab
		]
	};

	this.filterGrid = new GO.email.FilterGrid();
	this.labelsTab = new GO.email.LabelsGrid();

	var items = [
		propertiesTab,
		serverTab,
		this.filterGrid,
		this.labelsTab,
		this.permissionsTab
	];
	
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
			layoutOnTabChange: true,
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
		height: dp(616),
		width : dp(1008),
		stateId: 'email-account-dialog',
		closeAction : 'hide',
		title : t("E-mail Account", "email"),

		items : this.propertiesPanel,
		buttonAlign: 'left',
		buttons : [this.aliasesButton = new Ext.Button({
			iconCls: 'account',
			text : t("Aliases", "email"),
			handler : function() {
				if (!this.aliasesDialog) {
					this.aliasesDialog = new GO.email.AliasesDialog();
				}
				this.aliasesDialog.show(this.account_id);
			},
			hidden: (!GO.settings.modules.email.write_permission && GO.email.disableAliases),
			scope : this
		}),{
			iconCls : 'btn-folder',
			text : t("Folders", "email"),
			cls : 'x-btn-text-icon',
			scope : this,
			handler : function() {

				if (!this.foldersDialog) {
					this.foldersDialog = new GO.email.FoldersDialog();
				}
				this.foldersDialog.show(this.account_id);

			}
		},'->',{
			text : t("Apply"),
			handler : function() {
				this.save(false);
			},
			scope : this
		},{

			text : t("Ok"),
			handler : function() {
				this.save(true);
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
			waitMsg : t("Saving..."),
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
					error = t("You have errors in your form. The invalid fields are marked.");
				} else if (action.result) {
					error = action.result.feedback;
				} else {
					error = t("Could not connect to the server. Please check your internet connection.");
				}

				Ext.MessageBox.alert(t("Error"), error);
				
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

		if(!this.templatesCombo.store.loaded) {
			this.templatesCombo.store.load();
		}

		this.tabPanel.setActiveTab(0);

		this.aliasesButton.setDisabled(true);
		if (account_id) {
			this.loadAccount(account_id);
			GO.email.subscribedFoldersStore.baseParams.account_id = account_id;
			GO.email.subscribedFoldersStore.load();
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
		this.account_id = account_id;
		this.propertiesPanel.form.load({
			url : GO.url("email/account/load"),
			params : {
				id : account_id
			},
			waitMsg : t("Loading..."),
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
				
//				if (this.templatesCombo) {
//					this.templatesCombo.store.load();
//					this.templatesCombo.setRemoteText(action.result.remoteComboTexts['default_template_id']);
//				}
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
