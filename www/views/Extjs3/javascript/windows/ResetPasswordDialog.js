/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ResetPasswordDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.LogoComponent = Ext.extend(Ext.BoxComponent, {
	onRender : function(ct, position){
		this.el = ct.createChild({
			tag: 'div',
			cls: "go-app-logo"
		});
	}
});

/**
 * @class GO.dialog.ResetPasswordDialog
 * @extends Ext.Window
 * The Group-Office login dialog window.
 * 
 * @cfg {Function} callback A function called when the login was successfull
 * @cfg {Object} scope The scope of the callback
 * 
 * @constructor
 * @param {Object} config The config object
 */
 
GO.dialog.ResetPasswordDialog = function(config){
	
	if(!config)
	{
		config={};
	}
	
	if(typeof(config.modal)=='undefined')
	{
		config.modal=true;
	}
	Ext.apply(this, config);

	this.formPanel = new Ext.FormPanel({		
		labelWidth: 120,
		defaultType: 'textfield',
		waitMsgTarget:true,        
		bodyStyle:'padding:5px 10px 5px 10px',
		items: [
			new GO.LogoComponent(),
			new GO.form.HtmlComponent({
				html: t("Please fill in the form below to reset your password.")+'<br/><br/>'
			}),
		{
			itemId: 'password',
			fieldLabel: t("Password"),
			name: 'password',
			inputType: 'password',
			allowBlank:false
			,anchor:'100%'
		},{
			fieldLabel: t("Confirm"),
			name: 'confirm',
			inputType: 'password',
			allowBlank:false,
			anchor:'100%'
		}]
	});
	
	GO.dialog.ResetPasswordDialog.superclass.constructor.call(this, {
		autoHeight:true,
		width:400,
		draggable:false,
		resizable: false,
		closeAction:'hide',
		title: t("Change password"),
		closable: false,
		items: [			
		this.formPanel
		],		
		buttons: [
		{
			text: t("Ok"),
			handler: this.changePass,
			scope:this
		}
		],
		keys: [{
			key: Ext.EventObject.ENTER,
			fn: this.changePass,
			scope:this
		}]
	});
};

Ext.extend(GO.dialog.ResetPasswordDialog, GO.Window, {
	focus: function(){
		var f= this.formPanel.form.findField('password');
		if(!f){
			f = this.formPanel.form.findField('confirm');
		}
		f.focus(true);
	},
	changePass : function(){							
		this.formPanel.form.submit({
			url:GO.url('auth/SetNewPassword'),
			params:{
				email: GO.email,
				usertoken: GO.usertoken
			},
			waitMsg:t("Loading..."),
			success:function(form, action){
				Ext.Msg.show({
					title:t("Password has been changed"),
					msg: t("Your password has been changed"),
					buttons: Ext.Msg.OK,
					fn: function() {
						go.reload();
					}
				});
			},
			failure: function(form, action) {
				
				if(action.result)
				{
					Ext.MessageBox.alert(t("Error"), action.result.feedback, function(){
						this.formPanel.form.findField('password').focus(true);
					},this);
				}
			},
			scope: this
		});
	}
});



