GO.email.ImapAclUserDialog = Ext.extend(GO.Window, {

	initComponent : function(){

		this.formPanel = new Ext.form.FormPanel({
			autoHeight:true,
			waitMsgTarget : true,
			url : GO.url("email/folder/setAcl"),
			border : false,
			baseParams : {
				account_id : 0,
				mailbox:''
			},
			cls : 'go-form-panel',
			items : [new GO.form.ComboBox({
					xtype:'textfield',
					fieldLabel:GO.lang.strUsername,
					name:'identifier',
					anchor:'100%',
					displayField: 'username',		
					valueField: 'username',
					triggerAction: 'all',
					selectOnFocus:true,					
					pageSize: parseInt(GO.settings['max_rows_list']),
					store: new GO.data.JsonStore({
						url: GO.url("email/account/usernames"),
						id: 'username',
						fields:['username','email'],
						remoteSort: true
					})
			}),{
				xtype:'checkboxgroup',
				fieldLabel:GO.lang.strPermissions,				
				anchor:'100%',
				columns:1,
				items:[{
						boxLabel:GO.email.lang.readPerm,
						name:'read',
						checked:true
				},{
						boxLabel:GO.email.lang.writePerm,
						name:'write'
				},{
						boxLabel:GO.email.lang.deletePerm,
						name:'delete'
				},{
						boxLabel:GO.email.lang.createMailboxPerm,
						name:'createmailbox'
				},{
						boxLabel:GO.email.lang.deleteMailboxPerm,
						name:'deletemailbox'
				},{
						boxLabel:GO.email.lang.adminPerm,
						name:'admin'
				}
				]
			}]
			
		});


		Ext.apply(this, {
			width:400,
			autoHeight:true,
			title:GO.email.lang.shareFolder,
			items:[this.formPanel],
			buttons:[{
				text : GO.lang['cmdOk'],
				handler : function() {
					this.submitForm();
				},
				scope : this
			},{
				text : GO.lang['cmdClose'],
				handler : function() {
					this.hide();
				},
				scope : this
			}]
		});
		GO.email.ImapAclDialog.superclass.initComponent.call(this);
		this.addEvents({
			save:true
		});
	},

	focus : function(){
		var f = this.formPanel.form.findField('identifier');
		f.focus();
	},
	
	setData : function(mailbox, account_id, record){
		this.formPanel.baseParams.mailbox=mailbox;
		this.formPanel.baseParams.account_id=account_id;

		var f = this.formPanel.form.findField('identifier');
		
		if(record){
			this.formPanel.form.setValues(record.json);			
			f.setDisabled(true);
			this.formPanel.baseParams.identifier=f.getValue();
		}else{
			this.formPanel.form.reset();
			delete this.formPanel.baseParams.identifier;
			f.setDisabled(false);
			f.focus();
		}
	},


	submitForm : function(hide) {
		this.formPanel.form.submit({
			url : GO.url("email/folder/setAcl"),
			waitMsg : GO.lang['waitMsgSave'],
			success : function(form, action) {

				this.fireEvent('save', this);
				this.hide();
			},
			failure : function(form, action) {
				if (action.failureType == 'client') {
					Ext.MessageBox.alert(GO.lang['strError'],
						GO.lang['strErrorsInForm']);
				} else {
					Ext.MessageBox.alert(GO.lang['strError'],
						action.result.feedback);
				}
			},
			scope : this
		});
	}

});