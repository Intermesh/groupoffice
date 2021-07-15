/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AccountSettingsPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
go.usersettings.AccountSettingsPanel = Ext.extend(Ext.Panel, {

	title:t('Account','users','core'),
	iconCls: 'ic-account-circle',
	autoScroll:true,
	id: 'pnl-account-settings',
	passwordProtected: true,
	layout: "form",
	defaults: {
		anchor: "100%"
	},

	initComponent: function () {
			
		this.userFieldset = new Ext.form.FieldSet({
			labelWidth:dp(152),
			title: t('User','users','core'),
			layout: 'hbox',
			items:[
				{
					width: 150,					
					items: [
						this.avatarComp = new go.form.ImageField({			
							name: 'avatarId'										
						})						
					]
				},{
					flex:1,
					layout: 'form',
					defaults: {
						anchor: "100%"
					},
					items: [this.usernameField = new Ext.form.TextField({
						xtype: 'textfield',
						name: 'username',
						fieldLabel: t("Username"),
						needPasswordForChange: true,
						allowBlank: false,
						autocomplete: "off",
						regex: /^[A-Za-z0-9_\-\.\@]*$/,
						regexText: t("You have invalid characters in the username") + " (a-z, 0-9, -, _, ., @)."
					}),
					this.displayNameField = new Ext.form.TextField({
						fieldLabel: t('Display name','users','core'),
						name: 'displayName',
						allowBlank:false
					}),
					this.emailField = new Ext.form.TextField({
						fieldLabel: t('E-mail','users','core'),
						name: 'email',
						vtype:'emailAddress',
						needPasswordForChange: true,
						allowBlank:false
					}),
					this.recoveryEmailField = new Ext.form.TextField({
						fieldLabel: t("Recovery e-mail",'users','core'),
						name: 'recoveryEmail',
						needPasswordForChange: true,
						vtype:'emailAddress',
						allowBlank:false,
						hint: t('The recovery e-mail is used to send a forgotten password request to.','users','core')+'<br>'+t('Please use an email address that you can access from outside Group-Office.','users','core')
					})
					]
				
			}]
		});

		this.quotaFieldset = new Ext.form.FieldSet({
			labelWidth:dp(152),		
			hidden: !go.User.isAdmin,
			title: t('Disk space'),
			items: [{
				xtype: 'compositefield',
				items: [{
						xtype: 'numberfield',
						name: 'disk_quota',
						fieldLabel: t('Disk quota'),
						decimals: 0,
						width: dp(300),
						hint: t("Setting '0' will disable uploads for this user. Leave this field empty to allow unlimited space.")
					},{
						xtype: 'displayfield',
						value: 'MB'
				}]
			},
			{
				xtype: 'displayfield',
				name: 'disk_usage',
				fieldLabel: t('Space used'),
				setValue: function(v) {
					if(this.rendered) {
						v = Math.round(v/1024/1024*100)/100+'MB';
					}
					Ext.form.DisplayField.prototype.setValue.call(this, v);					
				},				
				width: dp(300)
			}
		]});
	
		this.passwordFieldset = new Ext.form.FieldSet({
			labelWidth:dp(152),
			title: t('Password','users','core'),
			defaults: {
				width: dp(300)
			},
			items:[
				this.passwordField1 = new go.form.PasswordGeneratorField({
					minLength: go.Modules.get("core","core").settings.passwordMinLength,
					needPasswordForChange: true,
					listeners: {						
						generated : function(field, pass) {
							this.passwordField2.setValue(pass);
						},
						scope: this
					}

				}),
		
				this.passwordField2 = new Ext.form.TextField({
					inputType: 'password',					
					fieldLabel: t("Confirm password", "users"),
					submit: false,
					minLength: go.Modules.get("core","core").settings.passwordMinLength,
					autocomplete: 'new-password'					
				})
			]
		});
		
		
		if(go.User.isAdmin) {
			this.passwordFieldset.insert(0, {
				xtype:"checkbox",
				hideLabel: true,
				boxLabel: t("Login enabled"),
				name: "enabled"
			});
		}
	
		Ext.apply(this,{
			items: [
				this.userFieldset,
				this.quotaFieldset,
				this.passwordFieldset
			].concat(go.customfields.CustomFields.getFormFieldSets("User").filter(function(fs){return !fs.fieldSet.isTab;}))
		});
		
		go.usersettings.AccountSettingsPanel.superclass.initComponent.call(this);
	},
	
	onLoadComplete : function(data){
		// Bubble further to child items
		this.items.each(function(fieldset) {
			if(fieldset.onLoadComplete){
				fieldset.onLoadComplete(data);
			}
		},this);
		
		this.checkPasswordAvailable(data);
		
	},
	
	checkPasswordAvailable : function(data) {
		//disable password fieldset if there's no password authentication method. User logged in via imap or ldap autheticator for example.
		var visible = false;
		data.authenticationMethods.forEach(function(method) {
			if(method.id == "password") {
				visible = true;
			}
		});

		this.usernameField.setDisabled(!visible);
		this.passwordField1.setDisabled(!visible);
		this.passwordField2.setDisabled(!visible);
	},
	
	onValidate : function() {
		if(this.passwordField1.getValue() != this.passwordField2.getValue()) {
			this.passwordField1.markInvalid(t("The passwords didn't match"));
			return false;
		}
		
		return true;
	},
	
	onSubmitStart : function(values){
		
		
		
		// Bubble further to child items
		this.items.each(function(fieldset) {
			if(fieldset.onSubmitStart){
				fieldset.onSubmitStart(values);
			}
		},this);
	},
	
	onSubmitComplete : function(result){
		// Bubble further to child items
		this.items.each(function(fieldset) {
			if(fieldset.onSubmitComplete){
				fieldset.onSubmitComplete(result);
			}
		},this);
	}
});


