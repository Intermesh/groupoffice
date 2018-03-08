/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ResetPasswordDialog.js 14816 2013-05-21 08:31:20Z mschering $
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
				html: GO.lang.changePasswordText+'<br/><br/>'
			}),
		{
			itemId: 'password',
			fieldLabel: GO.lang.strPassword,
			name: 'password',
			inputType: 'password',
			allowBlank:false
			,anchor:'100%'
		},{
			fieldLabel: GO.lang.strConfirm,
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
		title: GO.lang.changePassword,
		closable: false,
		items: [			
		this.formPanel
		],		
		buttons: [
		{
			text: GO.lang['cmdOk'],
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
			waitMsg:GO.lang.waitMsgLoad,
			success:function(form, action){
				Ext.Msg.show({
					title:GO.lang.changePasswordSuccessTitle,
					msg: GO.lang.changePasswordSuccess,
					buttons: Ext.Msg.OK,
					fn: function() {
						document.location = GO.url();
					}
				});
			},
			failure: function(form, action) {
				
				if(action.result)
				{
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback, function(){
						this.formPanel.form.findField('password').focus(true);
					},this);
				}
			},
			scope: this
		});
	}
});



