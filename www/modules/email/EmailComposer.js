/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: EmailComposer.js 22391 2018-02-19 13:20:18Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.EmailComposer = function(config) {
	Ext.apply(this, config);	

	this.cls='em-composer';
	
	var priorityGroup = Ext.id();
	
	var optionsMenuItems = [
	this.notifyCheck = new Ext.menu.CheckItem({
		text : t("Request read notification", "email"),
		checked : false,
		checkHandler : function(check, checked) {
			this.sendParams['notification'] = checked
			? 1
			: 0;
		},
		scope : this
	}),
	'-',
	'<div class="menu-title">'
	+ t("Priority", "email") + '</div>', {
		text : t("High", "email"),
		checked : false,
		group : priorityGroup,
		checkHandler : function() {
			this.sendParams['priority'] = '1';
		},
		scope : this
	}, this.normalPriorityCheck = new Ext.menu.CheckItem({
		text : t("Normal", "email"),
		checked : true,
		group : priorityGroup,
		checkHandler : function() {
			this.sendParams['priority'] = '3';
		},
		scope : this
	}), {
		text : t("Low", "email"),
		checked : false,
		group : priorityGroup,
		checkHandler : function() {
			this.sendParams['priority'] = '5';
		},
		scope : this
	},'-',this.htmlCheck = new Ext.menu.CheckItem({
		text:t("Use HTML markup", "email"),
		disabled:GO.util.isIpad(),
		checked:GO.email.useHtmlMarkup && !GO.util.isIpad(),
		listeners : {
			checkchange: function(check, checked) {
								 	
				if(!this.emailEditor.isDirty() || confirm(t("Changes will be lost. Are you sure?", "email")))
				{
					this.emailEditor.setContentTypeHtml(checked);
					this.lastLoadParams.keepHeaders=1;
					this.loadForm(this.lastLoadUrl, this.lastLoadParams);
				}else
				{
					check.setChecked(!checked, true);
				}
			},
			scope:this
		}
	})];


	this.optionsMenu = new Ext.menu.Menu({
		items : optionsMenuItems
	});

	this.showMenu = new Ext.menu.Menu({
				
		items : [this.fromFieldCheck = new Ext.menu.CheckItem({
			text : t("From field", "email"),
			checked : go.User.emailSettings.show_from,
			checkHandler : this.onShowFieldCheck,
			scope : this
		}),
		this.ccFieldCheck = new Ext.menu.CheckItem({
			text : t("CC field", "email"),
			checked : go.User.emailSettings.show_cc,
			checkHandler : this.onShowFieldCheck,
			scope : this
		}),
		this.bccFieldCheck = new Ext.menu.CheckItem({
			text : t("BCC field", "email"),
			checked : go.User.emailSettings.show_bcc,
			checkHandler : this.onShowFieldCheck,
			scope : this
		})
		]
	});


	var fillMultipleRecipients = function(combo, ids, entityName) {
		var me = this, v = combo.getValue();

		me.getEl().mask(t("Loading..."));

		switch(entityName) {
			case "Contact":
					go.Jmap.request({
						method: "Contact/get",
						params: {
							properties: ["name", "emailAddresses"],
							ids: ids
						}
					}).then(function(result) {										
			
						result.list.forEach(function(contact) {
							if(!contact.emailAddresses[0]) {
								return;
							}
							if(!go.util.empty(v)) {
								v += ", ";
							}							
							v += '"' + contact.name.replace(/"/g, '\\"') + '" <' + contact.emailAddresses[0].email + '>';							
							combo.setValue(v);
						});
					}).finally(function(){
						me.getEl().unmask();
					});		
			break;

			case "User":
					go.Jmap.request({
						method: "User/get",
						params: {
							properties: ["displayName", "email"],
							ids: ids
						}
					}).then(function(result) {										
			
						result.list.forEach(function(user) {
							if(!go.util.empty(v)) {
								v += ", ";
							}							
							v += '"' + user.displayName.replace(/"/g, '\\"') + '" <' + user.email + '>';							
							combo.setValue(v);
						});
					}).finally(function(){
						me.getEl().unmask();
					});		
			break;
		}
		

		// go.Db.store("Contact").get(ids).then(function(result) {										

		// 	result.entities.forEach(function(contact) {
		// 		if(!go.util.empty(v)) {
		// 			v += ", ";
		// 		}							
		// 		v += '"' + contact.name.replace(/"/g, '\\"') + '" <' + contact.emailAddresses[0].email + '>';							
		// 		combo.setValue(v);
		// 	});
		// }).finally(function(){
		// 	me.getEl().unmask();
		// });

	}, fillSingleRecipient = function(combo, name, email, id, entityName) {
		var v = combo.getValue();
		if(!go.util.empty(v)) {
			v += ", ";
		}	
		v += '"' + name.replace(/"/g, '\\"') + '" <' + email + '>';							
		combo.setValue(v);
	};


	var items = [
	this.fromCombo = new Ext.form.ComboBox({
		store : GO.email.aliasesStore,
		editable:false,
		fieldLabel : t("From", "email"),
		name : 'alias_name',
		anchor : '100%',
		displayField : 'from',
		valueField : 'id',
		hiddenName : 'alias_id',
		forceSelection : true,
		triggerAction : 'all',
		mode : 'local',
		tpl: '<tpl for="."><div class="x-combo-list-item">{from:htmlEncode}</div></tpl>',
		listeners:{
			beforeselect: function(cb, newAccountRecord){
				this._checkLoadTemplate(cb,newAccountRecord);
			},
			scope:this
		}
	}),
	{
		xtype:'compositefield',
		anchor : '100%',
		items: [

			this.toCombo = new GO.email.RecipientCombo(),
			new Ext.Button({				
				iconCls : 'ic-add',
				handler: function() {
					var select = new go.util.SelectDialog({
						entities: ["Contact", "User"],

						scope: this,
						
						selectSingleEmail: function(name, email, id, entityName) {
							fillSingleRecipient.call(this, this.toCombo, name, email, id, entityName);							
						},

						selectMultiple: function(ids, entityName) {
							fillMultipleRecipients.call(this, this.toCombo, ids, entityName);
						}
					});
					select.show();
				},
				scope: this
			})
		]
	},

	{
		xtype:'compositefield',
		anchor : '100%',
		items: [this.ccCombo = new GO.email.RecipientCombo({
			fieldLabel : t("CC", "email"),
			name : 'cc',
			anchor : '100%'
		}),
		new Ext.Button({				
				iconCls : 'ic-add',
				handler: function() {
					var select = new go.util.SelectDialog ({

						entities: ["Contact", "User"],
						
						scope: this,
						
						selectSingleEmail: function(name, email, id, entityName) {
							fillSingleRecipient.call(this, this.ccCombo, name, email, id, entityName);							
						},

						selectMultiple: function(ids, entityName) {
							fillMultipleRecipients.call(this, this.ccCombo, ids, entityName);
						}
					});
					select.show();
				},
				scope: this
			})
		]
	},


	{
		xtype:'compositefield',
		anchor : '100%',
		items: [this.bccCombo = new GO.email.RecipientCombo({
			fieldLabel : t("BCC", "email"),
			name : 'bcc',
			anchor : '100%'
		}),
			new Ext.Button({				
				iconCls : 'ic-add',
				handler: function() {
					var select = new go.util.SelectDialog ({

						entities: ["Contact", "User"],

						scope: this,
						
						selectSingleEmail: function(name, email, id, entityName) {
							fillSingleRecipient.call(this, this.bccCombo, name, email, id, entityName);							
						},

						selectMultiple: function(ids, entityName) {
							fillMultipleRecipients.call(this, this.bccCombo, ids, entityName);
						}
					});
					select.show();
				},
				scope: this
			})	
			
	]
	}
	];
								
	// var anchor = -113;
						
	
	
	items.push(this.subjectField = new Ext.form.TextField({
		fieldLabel : t("Subject", "email"),
		name : 'subject',
		anchor : '100%'
	}));
	this.emailEditor = new GO.base.email.EmailEditorPanel({
		maxAttachmentsSize:parseInt(GO.settings.config.max_attachment_size),
		region:'center'
	});

	this.emailEditor.getHtmlEditor().on('ctrlenter', function() {
		this.sendMail(false, false);
	}, this);
	
	this.formPanel = new Ext.form.FormPanel({
		border : false,		
		waitMsgTarget : true,
		cls : 'go-form-panel',		
		layout:"border",
		items : [{
			region:"north",
			layout:'form',
			xtype: "fieldset",
			style: 'padding-bottom:0',
			labelWidth : 100,
			defaultType : 'textfield',
			autoHeight:true,
			border:false,
			items: items
		},this.emailEditor],
		keys:[{
			key: Ext.EventObject.ENTER,
			ctrl:true,
			fn: function(key, e){
				e.preventDefault();
				this.sendMail(false,false);
			},
			scope:this
		}]
	});

	//Set a long timeout for large attachments
	this.formPanel.form.timeout=3000;
	
	var tbar = [this.sendButton = new Ext.Button({
		
		iconCls : 'ic-send',
		text: t("Send", "email"),
		cls: 'primary',
		tooltip: 'CTRL + Enter',
		handler : function() {
			this.sendMail();
		},
		scope : this
	}), this.saveButton = new Ext.Button({
		iconCls : 'ic-save',
		tooltip : t("Save"),
		handler : function() {
			this.sendMail(true);
		},
		scope : this
	}), 
	'->',
		this.createLinkButton = new go.links.CreateLinkButton({
			text: "",
			iconCls: "ic-link"
		})
	];

	tbar.push(this.emailEditor.getAttachmentsButton(), 
			this.showMenuButton = new Ext.Button({
				tooltip : t("Show", "email"),
				iconCls : 'ic-more',				
				menu : this.showMenu
			}));


		
//		this.btnAddressbook = new Ext.Button({
//			tooltip : t("Address book", "addressbook"),
//			iconCls : 'ic-import-contacts',
//			handler : function() {
//				if (!this.addressbookDialog) {
//					this.addressbookDialog = new GO.email.AddressbookDialog();
//					this.addressbookDialog.on('addrecipients',
//						function(fieldName, selections) {
//							this.addRecipients(fieldName,selections);
//						}, this);
//				}
//
//				this.addressbookDialog.show();
//			},
//			scope : this
//		});
//		
//		tbar.push(this.btnAddressbook);
		
	
	
		
		this.templatesStore = new GO.data.JsonStore({
			url : GO.url("email/template/emailSelection"),
			baseParams : {
				'type':"0"
			},
			root : 'results',
			totalProperty : 'total',
			id : 'id',
			fields : ['id', 'name', 'group', 'text','template_id','checked'],
			remoteSort : true
		});
		
		tbar.push(this.templatesBtn = new Ext.Button({
			iconCls:'ic-style',
			tooltip:t("E-mail template", "email")
		}),
		{
			tooltip : t("Extra options", "email"),
			iconCls : 'ic-more-vert',
			menu : this.optionsMenu
		});
		
		
		this.templatesStore.on("load", function( scope, records, options ) {
			
			this.templatesMenu = new GO.menu.JsonMenu({
						store: new Ext.data.Store(),
						listeners:{
							scope:this,
							itemclick : function(item, e ) {
								if(item.template_id=='default' || item.template_id=='default_for_account'){
									this.templatesStore.baseParams.default_template_id=this.lastLoadParams.template_id;
									this.templatesStore.baseParams.type = item.template_id;
									if (item.template_id=='default_for_account') {
										var fromAccountRecord = this.fromCombo.store.getById(this.fromCombo.getValue());
										this.templatesStore.baseParams.account_id = fromAccountRecord['data']['account_id'];
									}
									
									this.getEl().mask();
									this.templatesStore.load({
										callback: function() {
											delete this.templatesStore.baseParams.default_template_id;
											delete this.templatesStore.baseParams.type;
											delete this.templatesStore.baseParams.account_id;
											var fromComboValue = this.fromCombo.getValue();
											this.fromCombo.store.load({
												scope: this,
												callback: function() {
													this.fromCombo.setValue(fromComboValue);
													this.getEl().unmask();
												}
											});										
											
										},
										scope: this
									});
									
								}else if(!this.emailEditor.isDirty() || confirm(t("Changes will be lost. Are you sure?", "email")))
								{							
									this._changeTemplate(item.template_id);			
								}else
								{
									return false;							
								}
							}
						},
						setChecked: function(template_id) {
							this.store.each(function(record){
								if (record.data['template_id']==template_id) {
									this.store.getById(record.id).set('checked',true);
									this.store.getById(record.id).json.checked = true;
								} else if(record.data['template_id']>=0) {
									this.store.getById(record.id).set('checked',false);
									this.store.getById(record.id).json.checked = false;
								}
							});
							if (!this.rendered)
								this.render();
							this.updateMenuItems();
						},
						updateMenuItems: function() {
							if(this.rendered){
								this.removeAll();
								this.el.sync();

								var records = this.store.getRange();

								for(var i=0, len=records.length; i<len; i++){
									if (records[i].json.handler) {
										eval("records[i].json.handler = "+records[i].json.handler);
									}
									if (records[i].json.menu) {
										eval("records[i].json.menu = "+records[i].json.menu);
									}

									this.add(records[i].json);
								}
								
								this.add(
									'-'
								) ;
								this.add({
									text: t("Set current template as default for myself", "email"),
									template_id: "default"
								}) ;
								this.add({
									text: t("Set current template as default for this email account", "email"),
									template_id: "default_for_account"
								});

								
								
								this.fireEvent('load', this, records);
								this.loaded = true;
							}
						}
					});
			
			
			if(this.templatesStore.totalLength > 10 ) {
				 
			if(!this.templateSelectionDialog) {
				this.templateSelectionDialog = new GO.email.TemplateSelectionDialog({
					tbar: [
						new Ext.Button({
							text : t("Set current template as default for myself", "email"),
							handler : function() {

								var default_template_id = this.templateSelectionDialog.getSelected().data.template_id;

								this.templatesStore.baseParams.default_template_id=default_template_id;
								this.templatesStore.baseParams.type = "default";
								
								this.templatesStore.load();

								delete this.templatesStore.baseParams.default_template_id;
								delete this.templatesStore.baseParams.type;
								delete this.templatesStore.baseParams.account_id;
								var fromComboValue = this.fromCombo.getValue();
								this.fromCombo.store.load();
								this.fromCombo.setValue(fromComboValue);
								
								
								
								
								
							},
							scope : this
						}),
						new Ext.Button({
							text : t("Set current template as default for this email account", "email"),
							handler : function() {

								var default_template_id = this.templateSelectionDialog.getSelected().data.template_id;

								this.templatesStore.baseParams.default_template_id=default_template_id;
								this.templatesStore.baseParams.type = "default_for_account";

								
								var fromAccountRecord = this.fromCombo.store.getById(this.fromCombo.getValue());
								this.templatesStore.baseParams.account_id = fromAccountRecord['data']['account_id'];
								
								this.templatesStore.load();
								delete this.templatesStore.baseParams.default_template_id;
								delete this.templatesStore.baseParams.type;
								delete this.templatesStore.baseParams.account_id;
								var fromComboValue = this.fromCombo.getValue();
								this.fromCombo.store.load();
								this.fromCombo.setValue(fromComboValue);
								
							},
							scope : this
						})
					],
					grid: {
						store: this.templatesStore
					}
				});
				
				this.templateSelectionDialog.grid.on("rowdblclick", function(grid, rowIndex){
					var record = grid.getStore().getAt(rowIndex);
//					console.log(record.template_id)
//					this._changeTemplate(record.get('template_id'));
						if (this.isVisible()) {
							if(!this.emailEditor.isDirty() || confirm(t("Changes will be lost. Are you sure?", "email"))) {
								this._changeTemplate(record.get('template_id'));
							}
						}
					
					
					
//					if(record.get('template_id')=='default' || record.get('template_id')=='default_for_account'){
//							this.templatesStore.baseParams.default_template_id=this.lastLoadParams.template_id;
//							this.templatesStore.baseParams.type = record.get('template_id');
//							if (record.get('template_id')=='default_for_account') {
//								var fromAccountRecord = this.fromCombo.store.getById(this.fromCombo.getValue());
//								this.templatesStore.baseParams.account_id = fromAccountRecord['data']['account_id'];
//							}
//							this.templatesStore.load();
//							delete this.templatesStore.baseParams.default_template_id;
//							delete this.templatesStore.baseParams.type;
//							delete this.templatesStore.baseParams.account_id;
//							var fromComboValue = this.fromCombo.getValue();
//							this.fromCombo.store.load();
//							this.fromCombo.setValue(fromComboValue);
//						}else if(!this.emailEditor.isDirty() || confirm(t("Changes will be lost. Are you sure?", "email")))
//						{							
//							
//						}else
//						{
//							return false;							
//						}
					
					this._changeTemplate(record.get('template_id'));			
					
					this.templateSelectionDialog.hide();
				}, this);
				
				
				this.templatesBtn.on('click', function() {
					this.templateSelectionDialog.show();
				}, this)
			}
			} else {
				
					this.templatesBtn.menu = this.templatesMenu;
					
					this.templatesMenu.store = this.templatesStore;
					this.templatesMenu.updateMenuItems();
			
			
			}
			
			
			
			
		}, this);
		
	

	var focusFn = function() {
		this.toCombo.focus();
	};

	GO.email.EmailComposer.superclass.constructor.call(this, {
		title : t("Compose an e-mail message", "email"),
		width : 750,
		height : 600,
		minWidth : 300,
		minHeight : 200,
		layout : 'fit',
		maximizable : true,
		collapsible : true,
		animCollapse : false,
		//plain : true,
		closeAction : 'hide',
		buttonAlign : 'center',
		focus : focusFn.createDelegate(this),
		tbar : tbar,
		items : this.formPanel
	});
	
	this.on('hide', function() {
		this.link = false;
	}, this);

	this.addEvents({
		'dialog_ready' :true,
		//		attachmentDblClicked : true,
		//zipOfAttachmentsDblClicked : true,
		'send' : true,
		'reset' : true,
		afterShowAndLoad:true,
		beforesendmail:true

	});
};

Ext.extend(GO.email.EmailComposer, GO.Window, {

	stateId : 'email-composer',
	
	showConfig : {},

	autoSaveTask : {},
	
	lastAutoSave : false,
	
	defaultSendParams : {
		priority : 3,
		notification : 0,
		draft_uid : 0,
		reply_uid : 0,
		reply_mailbox : "",
		in_reply_to : "",
		forward_uid : 0,
		forward_mailbox : ""
	},
	
	sendParams : {},
	
	_checkLoadTemplate : function(cb,newAccountRecord) {
//			GO.request({
//				url: 'addressbook/template/defaultTemplateId',
//				params:{
//					account_id: newAccountRecord.data['account_id']
//				},
//				success: function(options, response, result)
//				{

			var previousAccountRecord = cb.store.getById(cb.getValue());
			if (this.templatesBtn.disabled == true) {
				//console.log('disable template changing');
				// do not switch template when switching From addres
			} else if (newAccountRecord.get('template_id')!=previousAccountRecord.get('template_id')){
					this.templatesMenu.setChecked(newAccountRecord.get('template_id'));
					if (!this.emailEditor.isDirty() || confirm(t("Changes will be lost. Are you sure?", "email")))
						this._changeTemplate(newAccountRecord.get('template_id'));
			}
			this._setSignature(cb,newAccountRecord);
//				},
//				scope:this
//			});
	},
	
	_setSignature : function(cb,newAccountRecord) {
		var oldAccountRecord = cb.store.getById(cb.getValue());

		var oldSig = oldAccountRecord.get(this.emailEditor.getContentType()+"_signature");
		var newSig = newAccountRecord.get(this.emailEditor.getContentType()+"_signature");

		var editorValue = this.emailEditor.getActiveEditor().getValue();

		/*
		 *GO returns <br /> but the browse turns this into <br> so replace those
		 */
		if(this.emailEditor.getContentType()=='html'){
			editorValue = editorValue.replace(/<br>/g, '<br />');
			oldSig=oldSig.replace(/<br>/g, '<br />')
			newSig=newSig.replace(/<br>/g, '<br />')
		}
		if(GO.util.empty(oldSig))
		{
			this.addSignature(newAccountRecord);
		}else
		{
			this.emailEditor.getActiveEditor().setValue(editorValue.replace(oldSig,newSig));
		}
	},
	
	addSignature : function(accountRecord){
		accountRecord = accountRecord || this.fromCombo.store.getById(this.fromCombo.getValue());
			
		if(!accountRecord) {
			return false;
		}
		
		var signature_below_reply = accountRecord.get("signature_below_reply");
	
		var sig = accountRecord.get(this.emailEditor.getContentType()+"_signature");
		
		if(!GO.util.empty(sig))
		{
			if(this.emailEditor.getContentType()=='plain')
			{
				sig = "\n"+sig+"\n";
			}else
			{
				sig = '<br /><div id="EmailSignature">'+sig+'</div><br />';
			}
		}
		
		if(signature_below_reply){
			this.emailEditor.getActiveEditor().setValue(this.emailEditor.getActiveEditor().getValue()+sig);
		} else {
			this.emailEditor.getActiveEditor().setValue(sig+this.emailEditor.getActiveEditor().getValue());
		}
	},
	
	autoSave : function(){
		if(GO.util.empty(this.sendParams.addresslist_id) && this.lastAutoSave && this.lastAutoSave!=this.emailEditor.getActiveEditor().getValue())
		{
			this.sendMail(true,true);
		}
		this.lastAutoSave=this.emailEditor.getActiveEditor().getValue();
	},
	
	startAutoSave : function(){
		this.lastAutoSave=false;
		Ext.TaskMgr.start(this.autoSaveTask);
	},
	
	stopAutoSave : function(){
		Ext.TaskMgr.stop(this.autoSaveTask);
	},
	
	afterRender : function() {
		GO.email.EmailComposer.superclass.afterRender.call(this);

		this.autoSaveTask={
			run: this.autoSave,
			scope:this,
			interval:120000
		//interval:5000
		};
		
		this.on('hide', this.stopAutoSave, this);
	},

	toComboVisible : true,

	reset : function() {

		this.sendParams = {};
		Ext.apply(this.sendParams, this.defaultSendParams);

//		GO.email.showCCfield = true;
//		GO.email.showBCCfield = false;

		this.showFrom(go.User.emailSettings.show_from);
		this.showCC(go.User.emailSettings.show_cc);
		this.showBCC(go.User.emailSettings.show_bcc);
		this.fromFieldCheck.setChecked(go.User.emailSettings.show_from);
		this.ccFieldCheck.setChecked(go.User.emailSettings.show_cc);
		this.bccFieldCheck.setChecked(go.User.emailSettings.show_bcc);

		if (this.defaultAcccountId) {
			this.fromCombo.setValue(this.defaultAcccountId);
		}
		this.notifyCheck.setChecked(false);
		this.normalPriorityCheck.setChecked(true);

		this.formPanel.form.reset();
		this.emailEditor.reset();
		
		this.fireEvent("reset", this);
	},

	showFrom : function(show){
		this.fromCombo.getEl().up('.x-form-item').setDisplayed(show);
		if(show)
		{
			this.fromCombo.onResize();
		}
		this.doLayout();
	},

	showCC : function(show){
		this.ccCombo.getEl().up('.x-form-item').setDisplayed(show);
		if(show)
		{
			this.ccCombo.onResize();
		}		
		this.doLayout();
	},
	
	showBCC : function(show){
		this.bccCombo.getEl().up('.x-form-item').setDisplayed(show);		
		if(show)
		{
			this.bccCombo.onResize();
		}
		this.doLayout();
	},

	addRecipients : function(fieldName,selections) {
		var field = this.formPanel.form.findField(fieldName);

		var currentVal = field.getValue();
		if (currentVal != '' && currentVal.substring(currentVal.length-1,currentVal.length) != ',' && currentVal.substring(currentVal.length-2,currentVal.length-1)!=',')
			currentVal += ', ';

		currentVal += selections;

		field.setValue(currentVal);
		setTimeout(function() { field.syncHeight(); });
		
		if (fieldName == 'cc') {
			this.ccFieldCheck.setChecked(true);
		} else if (fieldName == 'bcc') {
			this.bccFieldCheck.setChecked(true);
		}
	},

	initTemplateMenu :  function(config){
		config = config||{};
		
//		if (typeof(config.template_id) == 'undefined' && this.templatesStore){
//			var templateRecordIndex = this.templatesStore.findBy(function(record,id){
//				return record.get('checked');
//			});
//
//			if(templateRecordIndex>-1)
//				config.template_id=this.templatesStore.getAt(templateRecordIndex).get('template_id');
//		}

		//check the right template menu item.
		if(this.templatesStore && this.templatesMenu && this.templatesMenu.items){
			var templateId = config.template_id || this.getDefaultTemplateId();
			var item = this.templatesMenu.items.find(function(item){
				return item.template_id==templateId;
			});
			if(item){
				item.setChecked(true);
			}
		}
			if(config.disableTemplates){
				this.templatesBtn.setDisabled(config.disableTemplates);
			} else {
				this.templatesBtn.setDisabled(false);
			}
		
		
	},
					
					
	getDefaultTemplateId : function(){
		var fromRecord = this.fromCombo.store.getById(this.fromCombo.getValue());
		if (fromRecord)
			return fromRecord.data['template_id'];
		else
			return null;
	},
	
	initFrom : function(config){
		var index=-1;
		if (config.account_id) {
			index = this.fromCombo.store.findBy(function(record, id){
				return record.get('account_id')==config.account_id;
			});
		}

		//find by e-mail
		if(config.from){
			index = this.fromCombo.store.findBy(function(record, id){
				return record.get('email')==config.from;
			});
		}
		if(index==-1)
		{
			index=0;
		}
		this.fromCombo.setValue(this.fromCombo.store.data.items[index].id);
//		this._checkLoadTemplate(this.fromCombo,this.fromCombo.store.getAt(0));
	},

	show : function(config) {
		
		config = config || {};

		Ext.getBody().mask(t("Loading..."));

		if(!config.keepLinks) {
			this.createLinkButton.reset();
		}

		this.showConfig=config;
		
		if (!this.rendered) {
			
			var requests = {				
				aliases:{r:'email/alias/store','limit':0}
			};
			
				requests.templates={r:'email/template/emailSelection'};
				if (!GO.util.empty(config.account_id))
					requests.templates['account_id'] = config.account_id;
			
				
			GO.request({
				url: 'core/multiRequest',
				params:{
					requests:Ext.encode(requests)
				},
				success: function(options, response, result)
				{
					this.fromCombo.store.loadData(result.aliases);

					if(this.templatesStore)
						this.templatesStore.loadData(result.templates);              
					
					Ext.getBody().unmask();

					var records = this.fromCombo.store.getRange();
					if (records.length) {
						if (!config.account_id) {
							this.showConfig.account_id = records[0].data.account_id;
						}

						this.render(Ext.getBody());
						this.show(this.showConfig);

						return;

					} else {
						Ext.getBody().unmask();
						Ext.Msg.alert(t("No account", "email"),
							t("You didn't configure an e-mail account yet. Go to Start menu -> E-mail -> Administration -> Accounts to setup your first e-mail account", "email"));
					}
					
				},
				scope:this
			});
			
			//this.htmlEditor.SpellCheck = false;
		} else {

			this.initTemplateMenu(config);
			
			//keep attachments when switchting from text <> html
			this.reset();
			
			//save the mail to a file location
			if(config.saveToPath){
				this.sendParams.save_to_path=config.saveToPath;
				this.sendButton.hide();
			}else
			{
				this.sendButton.show();
			}

			this.initFrom(config);

			if (config.values) {
				this.formPanel.form.setValues(config.values);
			}

			//this will be true when swithing from html to text or vice versa
			if(!config.keepEditingMode)
			{
				//remove attachments if not switching edit mode
				this.emailEditor.setAttachments();				
				this.emailEditor.setContentTypeHtml(GO.email.useHtmlMarkup && !GO.util.isIpad());
				
				this.htmlCheck.setChecked(GO.email.useHtmlMarkup && !GO.util.isIpad(), true);
				if(this.encryptCheck)
					this.encryptCheck.setChecked(false, true);
			}			

			this.toComboVisible = true;
			this.showMenuButton.setDisabled(false);
			this.toCombo.getEl().up('.x-form-item').setDisplayed(true);
			this.sendURL = GO.url('email/message/send');
			this.saveButton.setDisabled(false);
		
			this.notifyCheck.setChecked(GO.email.alwaysRequestNotification);
			
			if(config.move)
			{
				var pos = this.getPosition();
				this.setPagePosition(pos[0]+config.move, pos[1]+config.move);
			}			
			
			// for mailings plugin
			if (config.addresslist_id > 0) {
				this.sendURL = GO.url("addressbook/sentMailing/send");

				if(go.Modules.isAvailable("legacy", "addressbook")) {
					// Disable the addressbook button when creating newsletters
					this.btnAddressbook.setDisabled(true);
				}

				this.toComboVisible = false;
				this.showMenuButton.setDisabled(true);
				this.toCombo.getEl().up('.x-form-item').setDisplayed(false);
				this.showCC(false);
				this.showBCC(false);

				this.sendParams.addresslist_id = config.addresslist_id;
				this.sendParams.campaign_id = config.campaign_id;

				this.saveButton.setDisabled(true);
			}else
			{
				
//				if(go.Modules.isAvailable("legacy", "addressbook")) {
//					// Enable the addressbook button when not creating newsletters
//					this.btnAddressbook.setDisabled(false);
//				}
//				this.ccFieldCheck.setChecked(GO.email.showCCfield == '1');
//				this.bccFieldCheck.setChecked(GO.email.showBCCfield == '1');
			}
			
			var params = config.loadParams ? config.loadParams : {
				uid : config.uid,					
				task : config.task,
				mailbox : config.mailbox
			};
			
			//for directly loading a contact in a template
			if(config.contact_id)
				params.contact_id=config.contact_id;

			if(config.entity && config.entity == "Contact") {
				params.contact_id = config.entityId;
			}
			
			//for directly loading a company in a template
			if(config.company_id)
				params.company_id=config.company_id;

			params.to = this.toCombo.getValue();		
			params.cc = this.ccCombo.getValue();		
			params.bcc = this.bccCombo.getValue();		
			params.subject = this.subjectField.getValue();	
			
			if (config.addresslist_id > 0) {
				// so that template loading won't replace fields
				params.addresslist_id = config.addresslist_id;
			}
			
			
			if(typeof(config.template_id)=='undefined'){
				config.template_id=this.getDefaultTemplateId();
			}

			if (config.uid || config.template_id!='undefined' || config.loadUrl || config.loadParams) {
		
//				if(config.task=='opendraft')
//					this.sendParams.draft_uid = config.uid;
//				
				var fromRecord = this.fromCombo.store.getById(this.fromCombo.getValue());

				
				if (!GO.util.empty(config.account_id))
					params.account_id = config.account_id;
				else
					params.account_id =fromRecord.get('account_id');
				
				params.alias_id=fromRecord.get('id');					
				
				params.template_id=config.template_id;
				
				if(config.addEmailAsAttachmentList) {
					params.addEmailAsAttachmentList = Ext.encode(config.addEmailAsAttachmentList);
				}
				
				if(config.includeAttachments){
					params.includeAttachments = config.includeAttachments;
				}
				
				var url;
				
				if(!config.task)
					config.task='template';
				
				if(config.loadUrl)
				{
					url = config.loadUrl;
				}else if(config.task=='reply_all'){
					url = GO.url("email/message/reply");				
					params.replyAll=true;
				}else
				{
					url = GO.url("email/message/"+config.task);				
				}

				//sometimes this is somehow copied from the baseparams
				params.content_type = this.emailEditor.getContentType();

				if (typeof(config.values)!='undefined' && typeof(config.values.body)!='undefined')
					params.body = config.values.body;
				
				this.lastLoadUrl = url;
				this.lastLoadParams = params;

				this.formPanel.form.load({
					url : url,
					params : params,
					waitMsg : t("Loading..."),
					failure:function(form, action)
					{
						Ext.getBody().unmask();
						GO.errorDialog.show(action.result.feedback)
					},
					success : function(form, action) {

						if(action.result.sendParams)
							Ext.apply(this.sendParams, action.result.sendParams);

						this.afterShowAndLoad(config);
						
						if(action.result.data.account_id) {
							this.lastLoadParams.account_id = action.result.data.account_id
//							this.fromCombo.setValue(this.lastLoadParams.account_id);
						}
						if(action.result.data.alias_id)
							this.lastLoadParams.alias_id = action.result.data.alias_id
						if(action.result.data.template_id) {
							this.lastLoadParams.template_id = action.result.data.template_id
							this.initTemplateMenu(); // set template menu 
//							this.initTemplateMenu({template_id: this.lastLoadParams.template_id}); // set template menu 
						}
							
//						action.result.data.account_id

						Ext.defer(function() {
							// show() of EmailEditorPanel is deferred 100ms by Ext and the ready event comes to early
							this.fireEvent('dialog_ready', this);
						}, 100,this);

					},
					scope : this
				});

			}else
			{
				//in case users selects new default template.
				this.lastLoadUrl = GO.url("email/message/template");
				this.lastLoadParams = params;
				this.afterShowAndLoad(config);
				
			}
			
			
				
		}
	},
	
	
	_changeTemplate : function(template_id) {
		if (!GO.util.empty(this.lastLoadParams) && this.lastLoadParams.template_id>=0 && this.lastLoadParams.template_id!=template_id) {
			this.lastLoadParams.template_id=template_id;
			this.lastLoadParams.keepHeaders=1;
			this.loadForm(this.lastLoadUrl, this.lastLoadParams);
		}
	},
	
	loadForm : function(url, params){
		
		params.content_type = this.emailEditor.getContentType();
		
//		var ctFieldVal = this.emailEditor.hiddenCtField.getValue();
//		var inlineImgVal = this.emailEditor.hiddenInlineImagesField.getValue();
//		var attachVal = this.emailEditor.hiddenAttachmentsField.getValue(); // remember attachment
		var attachmentmentsData=[];
		var attachments = this.emailEditor.attachmentsView.store.getRange(); 
		for(var i=0;i<attachments.length;i++)
			attachmentmentsData.push(attachments[i].data);
		
		this.formPanel.form.load({
					url : url,
					params : params,
					waitMsg : t("Loading..."),
					failure:function(form, action)
					{
						Ext.getBody().unmask();
						GO.errorDialog.show(action.result.feedback)
					},
					success : function(form, action) {
						
						this.addSignature();

						if(action.result.sendParams)
							Ext.apply(this.sendParams, action.result.sendParams);
						
						//add existing attachments to result so emailEditor will set this after form load.
						if(action.result.data.attachments)
							attachmentmentsData=attachmentmentsData.concat(action.result.data.attachments);						
						
						action.result.data.attachments=attachmentmentsData;
					},
					scope : this
				});
	},

	
	afterShowAndLoad : function(config){
		
		if(config.task!='opendraft')
			this.addSignature();

		this.startAutoSave();

		this.ccFieldCheck.setChecked(GO.email.showCCfield || this.ccCombo.getValue()!=='');
		this.bccFieldCheck.setChecked(GO.email.showBCCfield || this.bccCombo.getValue()!=='');
	
		if(config.afterLoad)
		{
			if(!config.scope)
				config.scope=this;
			config.afterLoad.call(config.scope);
		}

		Ext.getBody().unmask();
		GO.email.EmailComposer.superclass.show.call(this);


		if (this.toCombo.getValue() == '') {
			this.toCombo.focus();
		} else {
			this.emailEditor.focus();
		}

		if(this.showConfig.entity && this.showConfig.entityId) {
			this.setLinkEntity(config);
		}


		
		this.fireEvent('afterShowAndLoad',this);


		if(config.blobs) {
			let me = this;
			setTimeout(function(){
			config.blobs.forEach(function(b) {
				me.emailEditor.attachmentsView.addBlob(b);
			}, me);
			});
		}
	},
	

	HandleResult : function (btn){
		if (btn == 'yes'){
			//this.htmlEditor.SpellCheck = true;
			this.sendMail();
		}else{
			//this.editor.plugins[1].spellcheck();
		}
	},

	submitForm : function(hide){
		this.sendMail(false, false);
	},
	
	setLinkEntity : function(link) {
	//	this.createLinkButton.addLink(link.entity, link.entityId);
	},

	sendMail : function(draft, autoSave) {
		//prevent double send with ctrl+enter
		if(this.sendButton.disabled){
			return false;
		}		
		
		if(!draft && !autoSave && !this.fireEvent('beforesendmail', this))
			return false;
		
		if(this.emailEditor.attachmentsView.maxSizeExceeded()){
			GO.errorDialog.show(this.emailEditor.attachmentsView.getMaxSizeExceededErrorMsg());
			return false;
		}
		
		this.saveButton.setDisabled(true);
		this.sendButton.setDisabled(true);

		if (autoSave || this.subjectField.getValue() != ''
			|| confirm(t("You didn't fill in a subject. Are you sure you want to send this message without a subject?", "email"))) {
			

			// extra sync to make sure all is in there.
			//this.htmlEditor.syncValue();

			var waitMsg=null;
			if(!autoSave){
				waitMsg = draft ? t("Saving...") : t("Sending...", "email");
			}
			
			//make sure autosave doesn't trigger at the same time we're sending it.
			if(!autoSave && !draft)
				this.stopAutoSave();
			
			var sendUrl = this.sendURL;
			if(this.sendParams.save_to_path)
				sendUrl = GO.url("email/message/saveToFile");
			else if(draft || autoSave)
				sendUrl = GO.url("email/message/save");
			
//			if(this.link) {
//				this.sendParams.link = this.link.entity + ":" + this.link.entityId;
//			}
			
			this.sendParams.links = Ext.encode(this.createLinkButton.getNewLinks());

			this.formPanel.form.submit({
				url : sendUrl,
				params : this.sendParams,
				waitMsg : waitMsg,
				waitMsgTarget : autoSave ? null : this.formPanel.body,
				success : function(form, action) {
					
					this.saveButton.setDisabled(false);
					this.sendButton.setDisabled(false);
					
					if (action.result.account_id) {
						this.account_id = action.result.account_id;
					}
					
					if(action.result.sendParams)
						Ext.apply(this.sendParams, action.result.sendParams);

					if(!draft && !autoSave)
					{
						if (this.callback) {
							if (!this.scope) {
								this.scope = this;
							}
	
							var callback = this.callback.createDelegate(this.scope);
							callback.call();
						}
						
						if (go.Modules.isAvailable("community", "addressbook") && action.result.unknown_recipients
							&& action.result.unknown_recipients.length) {
							if (!GO.email.unknownRecipientsDialog)
								GO.email.unknownRecipientsDialog = new GO.email.UnknownRecipientsDialog();
	
							GO.email.unknownRecipientsDialog.store.loadData({
								recipients : action.result.unknown_recipients
							});
	
							GO.email.unknownRecipientsDialog.show();
						}

	
						this.fireEvent('send', this);
					
						this.hide();
					}else
					{	
						this.fireEvent('save', this);
					}
				},

				failure : function(form, action) {
					if(!autoSave)
					{
						var fb = action.result && action.result.feedback ? action.result.feedback : t("Could not connect to the server. Please check your internet connection.");
						
						GO.errorDialog.show(fb);						
					}
					this.saveButton.setDisabled(false);
					this.sendButton.setDisabled(false);
				},
				scope : this

			});
		} else {
			this.subjectField.focus();
			this.saveButton.setDisabled(false);
			this.sendButton.setDisabled(false);
		}
	},

	onShowFieldCheck : function(check, checked) {
		
		switch (check.id) {
			case this.fromFieldCheck.id :
				this.showFrom(checked);
				break;

			case this.ccFieldCheck.id :
				this.showCC(checked);				
				break;

			case this.bccFieldCheck.id :
				this.showBCC(checked);
				break;
		}
	}
	
});

//GO.email.TemplatesList = function(config) {
//
//	Ext.apply(config);
//	var tpl = new Ext.XTemplate(
//		'<div id="template-0" class="go-item-wrap">'+t("No template", "addressbook")+'</div>',
//		'<tpl for=".">',
//		'<div id="template-{id}" class="go-item-wrap"">{name}</div>',
//		'<tpl if="!GO.util.empty(default_template)"><div class="ml-template-default-spacer"></div></tpl>',
//		'</tpl>');
//
//	GO.email.TemplatesList.superclass.constructor.call(this, {
//		store : config.store,
//		tpl : tpl,
//		singleSelect : true,
//		autoHeight : true,
//		overClass : 'go-view-over',
//		itemSelector : 'div.go-item-wrap',
//		selectedClass : 'go-view-selected'
//	});
//}

//Ext.extend(GO.email.TemplatesList, Ext.DataView, {
//	onRender : function(ct, position) {
//		this.el = ct.createChild({
//			tag : 'div',
//			cls : 'go-select-list'
//		});
//
//		GO.email.TemplatesList.superclass.onRender.apply(this,
//			arguments);
//	}
//
//});
