/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: EmailComposer.js 21813 2017-12-04 09:09:12Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.EmailComposer = function(config) {
	Ext.apply(config);

	this.cls='em-composer';
	
	var priorityGroup = Ext.id();
	
	var optionsMenuItems = [
	this.notifyCheck = new Ext.menu.CheckItem({
		text : GO.email.lang.notification,
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
	+ GO.email.lang.priority + '</div>', {
		text : GO.email.lang.high,
		checked : false,
		group : priorityGroup,
		checkHandler : function() {
			this.sendParams['priority'] = '1';
		},
		scope : this
	}, this.normalPriorityCheck = new Ext.menu.CheckItem({
		text : GO.email.lang.normal,
		checked : true,
		group : priorityGroup,
		checkHandler : function() {
			this.sendParams['priority'] = '3';
		},
		scope : this
	}), {
		text : GO.email.lang.low,
		checked : false,
		group : priorityGroup,
		checkHandler : function() {
			this.sendParams['priority'] = '5';
		},
		scope : this
	},'-',this.htmlCheck = new Ext.menu.CheckItem({
		text:GO.email.lang.htmlMarkup,
		disabled:GO.util.isIpad(),
		checked:GO.email.useHtmlMarkup && !GO.util.isIpad(),
		listeners : {
			checkchange: function(check, checked) {
								 	
				if(!this.emailEditor.isDirty() || confirm(GO.email.lang.confirmLostChanges))
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
						
//	if(GO.gnupg)
//	{
//		optionsMenuItems.push('-');
//			
//		optionsMenuItems.push(this.encryptCheck = new Ext.menu.CheckItem({
//			text:GO.gnupg.lang.encryptMessage,
//			checked: false,
//			listeners : {
//				checkchange: function(check, checked) {
//					if(this.formPanel.baseParams.content_type=='html')
//					{
//						if(!confirm(GO.gnupg.lang.confirmChangeToText))
//						{
//							check.setChecked(!checked, true);
//							return false;
//						}else
//						{
//							this.emailEditor.setContentTypeHtml(false);
//							this.htmlCheck.setChecked(false, true);
//							this.showConfig.keepEditingMode=true;
//							this.show(this.showConfig);
//						}
//					}
//						
//					this.htmlCheck.setDisabled(checked);
//						
//					this.sendParams['encrypt'] = checked
//					? '1'
//					: '0';
//								
//					return true;
//				},
//				scope:this
//			}
//		}));
//	}

	this.optionsMenu = new Ext.menu.Menu({
		items : optionsMenuItems
	});

	this.showMenu = new Ext.menu.Menu({
				
		items : [this.formFieldCheck = new Ext.menu.CheckItem({
			text : GO.email.lang.sender,
			checked : true,
			checkHandler : this.onShowFieldCheck,
			scope : this
		}),
		this.ccFieldCheck = new Ext.menu.CheckItem({
			text : GO.email.lang.ccField,
			checked : GO.email.showCCfield,
			checkHandler : this.onShowFieldCheck,
			scope : this
		}),
		this.bccFieldCheck = new Ext.menu.CheckItem({
			text : GO.email.lang.bccField,
			checked : GO.email.showBCCfield,
			checkHandler : this.onShowFieldCheck,
			scope : this
		})
		]
	});




	var items = [
	this.fromCombo = new Ext.form.ComboBox({
		store : GO.email.aliasesStore,
		editable:false,
		fieldLabel : GO.email.lang.from,
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

	this.toCombo = new GO.form.ComboBoxMulti({
		sep : ',',
		fieldLabel : GO.email.lang.sendTo,
		name : 'to',
		anchor : '100%',
		height : 50,
		store : new GO.data.JsonStore({
			url : GO.url("search/email"),
			fields : ['full_email','info']
		}),
		valueField : 'full_email',
		displayField : 'info'
	}),

	this.ccCombo = new GO.form.ComboBoxMulti({
		sep : ',',
		fieldLabel : GO.email.lang.cc,
		name : 'cc',
		anchor : '100%',
		height : 50,
		store : new GO.data.JsonStore({
			url : GO.url("search/email"),
			fields : ['full_email','info']
		}),
		displayField : 'info',
		valueField : 'full_email',
		hideTrigger : true,
		minChars : 2,
		triggerAction : 'all',
		selectOnFocus : false

	}),

	this.bccCombo = new GO.form.ComboBoxMulti({
		sep : ',',
		fieldLabel : GO.email.lang.bcc,
		name : 'bcc',
		anchor : '100%',
		height : 50,
		store : new GO.data.JsonStore({
			url : GO.url("search/email"),
			fields : ['full_email','info']
		}),
		displayField : 'info',
		valueField : 'full_email',
		hideTrigger : true,
		minChars : 2,
		triggerAction : 'all',
		selectOnFocus : false

	})];
								
	var anchor = -113;
						
	if(GO.settings.modules.savemailas && GO.settings.modules.savemailas.read_permission)
	{		
		if (!this.selectLinkField) {
			this.selectLinkField = new GO.form.SelectLink({
				anchor : '100%'
			});
			anchor+=26;
			items.push(this.selectLinkField);
			
			this.selectLinkField.on('change',function(){
				this.replaceTemplateLinkTag();
			},this);	
		}
	}

	try {
		if(config && config.links)
		{
			if (!this.selectLinkField) {
				this.selectLinkField = new GO.form.SelectLink({
					anchor : '100%'
				});
				anchor+=26;
				items.push(this.selectLinkField);
				
				this.selectLinkField.on('change',function(){
					this.replaceTemplateLinkTag();
				},this);
			}
		}
	} catch(e) {}

	items.push(this.subjectField = new Ext.form.TextField({
		fieldLabel : GO.email.lang.subject,
		name : 'subject',
		anchor : '100%'
	}));
	this.emailEditor = new GO.base.email.EmailEditorPanel({
		maxAttachmentsSize:parseInt(GO.settings.config.max_attachment_size),
		region:'center',
		listeners:{
			submitshortcut:function(){
				this.sendMail(false, false);
			},
			scope:this
		}
	});
	
	this.formPanel = new Ext.form.FormPanel({
		border : false,		
		waitMsgTarget : true,
		cls : 'go-form-panel',		
		layout:"border",
		items : [{
			region:"north",
			layout:'form',
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
		text : GO.email.lang.send,
		iconCls : 'btn-send',
		tooltip:'CTRL+ENTER',
		handler : function() {
			this.sendMail();
		},
		scope : this
	}), this.saveButton = new Ext.Button({
		iconCls : 'btn-save',
		text : GO.lang.cmdSave,
		handler : function() {
			this.sendMail(true);
		},
		scope : this
	}), {
		text : GO.email.lang.extraOptions,
		iconCls : 'btn-settings',
		menu : this.optionsMenu
	}	, this.showMenuButton = new Ext.Button({
		text : GO.email.lang.show,
		iconCls : 'btn-show',
		menu : this.showMenu
	})];

	tbar.push(this.emailEditor.getAttachmentsButton());

	if (GO.addressbook) {
		
		this.btnAddressbook = new Ext.Button({
			text : GO.addressbook.lang.addressbook,
			iconCls : 'btn-addressbook',
			handler : function() {
				if (!this.addressbookDialog) {
					this.addressbookDialog = new GO.email.AddressbookDialog();
					this.addressbookDialog.on('addrecipients',
						function(fieldName, selections) {
							this.addRecipients(fieldName,selections);
						}, this);
				}

				this.addressbookDialog.show();
			},
			scope : this
		});
		
		tbar.push(this.btnAddressbook);
		
	}

	if(GO.addressbook){
		
		this.templatesStore = new GO.data.JsonStore({
			url : GO.url("addressbook/template/emailSelection"),
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
			iconCls:'ml-btn-mailings',
			text:GO.addressbook.lang.emailTemplate
		}));
		
		
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
									this.templatesStore.load();
									delete this.templatesStore.baseParams.default_template_id;
									delete this.templatesStore.baseParams.type;
									delete this.templatesStore.baseParams.account_id;
									var fromComboValue = this.fromCombo.getValue();
									this.fromCombo.store.load();
									this.fromCombo.setValue(fromComboValue);
								}else if(!this.emailEditor.isDirty() || confirm(GO.email.lang.confirmLostChanges))
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
									text: GO.addressbook.lang.setCurrentTemplateAsDefault,
									template_id: "default"
								}) ;
								this.add({
									text: GO.addressbook.lang.setCurrentTemplateAsDefaultEAccount,
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
							text : GO.addressbook.lang.setCurrentTemplateAsDefault,
							handler : function() {
								var template_id = "default";
								this.templatesStore.baseParams.default_template_id=this.lastLoadParams.template_id;
								this.templatesStore.baseParams.type = template_id;
								
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
							text : GO.addressbook.lang.setCurrentTemplateAsDefaultEAccount,
							handler : function() {
								
								var template_id = "default_for_account";
								this.templatesStore.baseParams.default_template_id=this.lastLoadParams.template_id;
								this.templatesStore.baseParams.type = template_id;
								
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
					if (GO.addressbook) {
						if (this.isVisible()) {
							if(!this.emailEditor.isDirty() || confirm(GO.email.lang.confirmLostChanges)) {
								this._changeTemplate(record.get('template_id'));
							}
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
//						}else if(!this.emailEditor.isDirty() || confirm(GO.email.lang.confirmLostChanges))
//						{							
//							
//						}else
//						{
//							return false;							
//						}
					
					this._changeTemplate(record.get('template_id'));			
					
					this.templateSelectionDialog.close();
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
		
	}

	var focusFn = function() {
		this.toCombo.focus();
	};

	GO.email.EmailComposer.superclass.constructor.call(this, {
		title : GO.email.lang.composeEmail,
		width : 750,
		height : 500,
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

	if (GO.addressbook) {
//		this.templatesStore.on('load',function(combo,records){
//			if (this.isVisible()) {
//				if(!this.emailEditor.isDirty() || confirm(GO.email.lang.confirmLostChanges))
//				{
//					v
//					this._changeTemplate(template_id);
//				}
//			}
//		}, this);
	}

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
		if (GO.addressbook) {
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
					if (!this.emailEditor.isDirty() || confirm(GO.email.lang['confirmLostChanges']))
						this._changeTemplate(newAccountRecord.get('template_id'));
			}
			this._setSignature(cb,newAccountRecord);
//				},
//				scope:this
//			});
		} else {
			this._setSignature(cb,newAccountRecord);
		}
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

	/*
	 *handles ctrl+enter from html editor
	 */
	fireSubmit : function(e) {
		if (e.ctrlKey && Ext.EventObject.ENTER == e.getKey()) {
			//e.stopEvent();
			e.preventDefault();
			this.sendMail(false, false);
			return false;
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

		this.showCC(GO.email.showCCfield===1);
		this.showBCC(GO.email.showBCCfield===1);			
		this.ccFieldCheck.setChecked(GO.email.showCCfield);
		this.bccFieldCheck.setChecked(GO.email.showBCCfield);

		if (this.defaultAcccountId) {
			this.fromCombo.setValue(this.defaultAcccountId);
		}
		this.notifyCheck.setChecked(false);
		this.normalPriorityCheck.setChecked(true);

		this.formPanel.form.reset();
		this.emailEditor.reset();
		
		this.fireEvent("reset", this);
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
		if(GO.addressbook){
			if(config.disableTemplates){
				this.templatesBtn.setDisabled(config.disableTemplates);
			} else {
				this.templatesBtn.setDisabled(false);
			}
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

		Ext.getBody().mask(GO.lang.waitMsgLoad);

		delete this.link_config;
		
		this.showConfig=config;
		
		if (!this.rendered) {
			
			var requests = {				
				aliases:{r:'email/alias/store','limit':0}
			};
			
			if(GO.addressbook){
				requests.templates={r:'addressbook/template/emailSelection'};
				if (!GO.util.empty(config.account_id))
					requests.templates['account_id'] = config.account_id;
			}
				
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
						Ext.Msg.alert(GO.email.lang.noAccountTitle,
							GO.email.lang.noAccount);
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

				if (GO.addressbook) {
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
				
				if (GO.addressbook) {
					// Enable the addressbook button when not creating newsletters
					this.btnAddressbook.setDisabled(false);
				}
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
					waitMsg : GO.lang.waitMsgLoad,
					failure:function(form, action)
					{
						Ext.getBody().unmask();
						GO.errorDialog.show(action.result.feedback)
					},
					success : function(form, action) {

						if(action.result.sendParams)
							Ext.apply(this.sendParams, action.result.sendParams);

						this.afterShowAndLoad(config);
						
						if(action.result.data.link_value){
							this.selectLinkField.setValue(action.result.data.link_value);
							this.selectLinkField.setRemoteText(action.result.data.link_text);
						}
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
						
						this.fireEvent('dialog_ready', this);
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
			if (config.link_config && this.selectLinkField) {
				this.link_config = config.link_config;
				if (config.link_config.modelNameAndId) {
					this.selectLinkField.setValue(config.link_config.modelNameAndId);
					this.selectLinkField.setRemoteText(config.link_config.text);
				}
			}
			
			
				
		}
	},
	
	
	_changeTemplate : function(template_id) {
		if (GO.addressbook && !GO.util.empty(this.lastLoadParams) && this.lastLoadParams.template_id>=0 && this.lastLoadParams.template_id!=template_id) {
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
					waitMsg : GO.lang.waitMsgLoad,
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
		
		this.fireEvent('afterShowAndLoad',this);
		
		if(this.selectLinkField){
			this.replaceTemplateLinkTag();
		}
		
		console.log(config);
		
		if (config['delegated_cc_enabled']) {
				
				GO.request({
					url: 'email/account/loadAddress',
					params: {
						id: config.account_id
					},
					success: function( options, response, result ) {
						console.log(result);
						var name = result.data['name'];
						var email = result.data['email'];
						this.ccCombo.setValue('"'+name+'" <'+email+'>');
					},
					scope: this
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
			|| confirm(GO.email.lang.confirmEmptySubject)) {
			

			// extra sync to make sure all is in there.
			//this.htmlEditor.syncValue();

			var waitMsg=null;
			if(!autoSave){
				waitMsg = draft ? GO.lang.waitMsgSave : GO.email.lang.sending;
			}
			
			//make sure autosave doesn't trigger at the same time we're sending it.
			if(!autoSave && !draft)
				this.stopAutoSave();
			
			var sendUrl = this.sendURL;
			if(this.sendParams.save_to_path)
				sendUrl = GO.url("email/message/saveToFile");
			else if(draft || autoSave)
				sendUrl = GO.url("email/message/save");

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
	
						if (GO.addressbook && action.result.unknown_recipients
							&& action.result.unknown_recipients.length) {
							if (!GO.email.unknownRecipientsDialog)
								GO.email.unknownRecipientsDialog = new GO.email.UnknownRecipientsDialog();
	
							GO.email.unknownRecipientsDialog.store.loadData({
								recipients : action.result.unknown_recipients
							});
	
							GO.email.unknownRecipientsDialog.show();
						}

						if (this.link_config && this.link_config.callback) {
							this.link_config.callback.call(this);
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
						var fb = action.result && action.result.feedback ? action.result.feedback : GO.lang.strRequestError;
						
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
			case this.formFieldCheck.id :
				this.fromCombo.getEl().up('.x-form-item').setDisplayed(checked);
				this.doLayout();
				break;

			case this.ccFieldCheck.id :
				this.showCC(checked);				
				break;

			case this.bccFieldCheck.id :
				this.showBCC(checked);
				break;
		}
	},
	
	replaceTemplateLinkTag: function() {

		var editorValue = this.emailEditor.getActiveEditor().getValue();
		var linkValue = '';

		if (!GO.util.empty(this.selectLinkField.getValue())) {
			var linkValue = this.selectLinkField.getRawValue();
			var nValue = this.selectLinkField.getValue();
			
			GO.request({
				url: 'core/createModelUrl',
				params: {
					modelTypeAndKey: nValue
				},
				success: function(response,options,result) {
					var newValue = editorValue.replace(/<span class="go-composer-link">(.*?)<\/span>/g, function(match, contents, offset, s) {
						// onclick="GO.linkHandlers[\''+nParts[0]+'\'].call(this, '+nParts[1]+');"
						return '<span class="go-composer-link"><a href="' + result.url + '">' + linkValue + '</a></span>';
					});

					this.emailEditor.getActiveEditor().setValue(newValue);
				},
				scope: this
			});
		}
	}
});

//GO.email.TemplatesList = function(config) {
//
//	Ext.apply(config);
//	var tpl = new Ext.XTemplate(
//		'<div id="template-0" class="go-item-wrap">'+GO.addressbook.lang.noTemplate+'</div>',
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
