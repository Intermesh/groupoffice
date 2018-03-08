go.login.ForgotPanel = Ext.extend(go.login.BaseLoginPanel, {
	
	initComponent: function() {
	
		this.emailField = new Ext.form.TextField({
			fieldLabel: t("Email"),
			name: 'email',			
			vtype: 'email',
			allowBlank:false,
			anchor:'100%'
		});
				
		Ext.apply(this,{
			items: [
				{html:'<h5>'+t('Request a recovery email')+'</h5><br>'},
				{html:t("Enter your email address")},
				this.emailField
			],
			listeners:{
				afterLayout: function(){
					this.emailField.focus(true);
				},
				scope:this
			}
		});
	
		go.login.ForgotPanel.superclass.initComponent.call(this);
	},
	
	submit: function(form) {
		
		form.getEl().mask();
		
		go.AuthenticationManager.forgotPassword(this.emailField.getValue(),function(authMan, success) {
			if(!success) {
				GO.errorDialog.show(t("A server error occurred."));
			} else {
				go.notifier.msg({
					iconCls: 'ic-email',
					title: t("Recovery e-mail sent"), 
					description: t('An e-mail with instructions has been sent to your e-mail address.')
				});
			}
			form.next(1); // userpanel
		},this);
	}
});