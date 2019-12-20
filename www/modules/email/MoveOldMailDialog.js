GO.email.MoveOldMailDialog = function(config){
	
	if(!config)
	{
		config={};
	}

	this.buildForm();

	config.layout='fit';
	config.title=t("Move old mails", "email");
	//	config.stateId='email-message-dialog';
	config.maximizable=true;
	config.modal=false;
	config.width=500;
	config.height=200;
	config.resizable=true;
	config.minizable=true;
	config.closeAction='hide';	
	config.items=this.formPanel;
	config.buttons=[{
		text: t("Ok"),
		handler: function()
		{
			this.submitForm();
		},
		scope:this
	},{
		text: t("Close"),
		handler: function()
		{
			this.hide();
		},
		scope:this
	}];
	
	GO.email.MoveOldMailDialog.superclass.constructor.call(this, config);

}

Ext.extend(GO.email.MoveOldMailDialog, go.Window,{

	onShow : function() {
		GO.email.MoveOldMailDialog.superclass.onShow.call(this);
		if (typeof(this.node)=='object') {
			this.folderNameField.setValue(this.node.attributes.mailbox);
		}
		this.untilDate.setValue(this.getDefaultDate());
	},

	buildForm : function() {
		this.formPanel = new Ext.form.FormPanel({
			timeout:120000,
			url : GO.url("email/message/MoveOld"),
			waitMsgTarget : true,
			border : false,
			cls : 'go-form-panel',
			items : [this.folderNameField = new GO.form.PlainField({
				anchor : '100%',
				allowBlank:false,
				fieldLabel : t("Folder", "email")
			}),{
				xtype : 'plainfield',
				anchor : '100%',
				allowBlank:false,
				hideLabel : true,
				value : t("Select a date. If you click OK after that, all the emails in the selected folder before that date will be moved to the selected target folder.", "email")
			},this.selectMailbox = new GO.form.ComboBoxReset({
				fieldLabel : t("Move to", "email"),
				hiddenName : 'target_mailbox',
				store : new GO.data.JsonStore({
					url : GO.url("email/folder/store"),
					baseParams : {
						task : 'subscribed_folders',
						account_id : 0
					},
					fields : ['name']
				}),
				valueField : 'name',
				displayField : 'name',
				value:'Trash',
				typeAhead : true,
				mode : 'local',
				triggerAction : 'all',
				editable : false,
				selectOnFocus : true,
				forceSelection : true
			})
		, this.untilDate = new Ext.form.DateField({
				name : 'until_date',
				width : 100,
				format : GO.settings['date_format'],
				allowBlank : false,
				fieldLabel : t("All emails before", "email")
			})
			]
		});
		
		this.selectMailbox.store.on('load',function(store,records,options){
			this.selectMailbox.setValue(this.selectMailbox.store.reader.jsonData['trash']);
		},this);
		
	},

	setNode : function(node) {
		this.node = node;
		this.account_id = node.attributes.account_id;
		this.selectMailbox.store.baseParams.account_id=this.account_id;
		this.selectMailbox.store.load();
	},

	getDefaultDate : function() {
		var date = new Date();
		date.setFullYear(date.getFullYear()-2);
		return date;
	},

	getNode : function() {
		if (typeof(this.node)=='undefined')
			return {};
		else
			return this.node;
	},

	submitForm : function(hide) {
		Ext.Msg.show({
			title: t("MoveOldMails", "email"),
			icon: Ext.MessageBox.WARNING,
			msg: t("Are you sure you want to move all the emails older then {date} from \"{source}\" to \"{target}\"?", "email").replace("{date}", this.untilDate.value).replace("{source}", this.folderNameField.getValue()).replace("{target}", this.selectMailbox.getValue()),
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn=='yes') {
					this.formPanel.form.submit({
						url : GO.url("email/message/MoveOld"),
						params : {
							'account_id' : this.account_id,
							'mailbox' : this.node.attributes.mailbox
						},
						waitMsg : t("Saving..."),
						success : function(form, action) {

							GO.email.messagesGrid.store.load({
								callback:function(){
									Ext.MessageBox.alert(t("Success"), t("The number of moved messages is", "email")+": "+action.result.total);
									this.hide();
								},
								scope:this
							});

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
						},
						scope : this

					});

				}
			},
			scope : this
		});
	}
});
