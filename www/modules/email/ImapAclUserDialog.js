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
					fieldLabel:t("Username"),
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
				fieldLabel:t("Permissions"),				
				anchor:'100%',
				columns:1,
				items:[{
						boxLabel:t("Read", "email"),
						name:'read',
						checked:true
				},{
						boxLabel:t("Write", "email"),
						name:'write'
				},{
						boxLabel:t("Delete", "email"),
						name:'delete'
				},{
						boxLabel:t("Create mailbox", "email"),
						name:'createmailbox'
				},{
						boxLabel:t("Delete mailbox", "email"),
						name:'deletemailbox'
				},{
						boxLabel:t("Administrate", "email"),
						name:'admin'
				}
				]
			}]
			
		});


		Ext.apply(this, {
			width:400,
			autoHeight:true,
			title:t("Share", "email"),
			items:[this.formPanel],
			buttons:[{
				text : t("Ok"),
				handler : function() {
					this.submitForm();
				},
				scope : this
			},{
				text : t("Close"),
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
			waitMsg : t("Saving..."),
			success : function(form, action) {

				this.fireEvent('save', this);
				this.hide();
			},
			failure : function(form, action) {
				if (action.failureType == 'client') {
					Ext.MessageBox.alert(t("Error"),
						t("You have errors in your form. The invalid fields are marked."));
				} else {
					Ext.MessageBox.alert(t("Error"),
						action.result.feedback);
				}
			},
			scope : this
		});
	}

});
