/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PasswordPanel.js 21127 2017-04-24 14:30:47Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.users.PasswordPanel = function(config)
{
	if(!config)
	{
		config={};
	}

	var prefix = (config.ldap_password) ? 'ldap_' : '';

	config.autoScroll=true;
	config.border=false;
	config.hideLabel=true;
	config.title = GO.users.lang.changePassword;
	config.layout='form';
	config.defaults={anchor:'100%'};
	config.defaultType = 'textfield';
	config.cls='go-form-panel';
	config.labelWidth=140;

	this.currentPasswordField = new Ext.form.TextField({
		inputType: 'password',
		fieldLabel: GO.users.lang.currentPassword,
		name: 'current_'+prefix+'password'
		});

	this.passwordField1 = new Ext.form.TextField({
		inputType: 'password',
		fieldLabel: GO.users.lang.newPassword,
		name: prefix+'password'
		});
	this.passwordField2 = new Ext.form.TextField({
		inputType: 'password',
		fieldLabel: GO.users.lang.confirmPassword,
		name: prefix+'passwordConfirm'
		});
		
	this.recoveryEmailField = new Ext.form.TextField({
		fieldLabel: GO.lang.strRecoveryEmail,
		name: 'recovery_email',
		vtype:'emailAddress'
	});

	config.items=[
		new Ext.form.FieldSet({
			title: GO.users.lang.currentPassword,
			items:[
				new Ext.Container({
					html:GO.lang.currentPasswordText,
					style: {
            marginBottom: '10px'
					}
				}),
				this.currentPasswordField
			]
		}),
		new Ext.form.FieldSet({
			title: GO.users.lang.changePassword,
			items:[
				this.passwordField1,
				this.passwordField2
			]
		}),
		new Ext.form.FieldSet({
			title: GO.lang.strRecoveryEmail,
			items:[
				new Ext.Container({
					html:GO.lang.recoveryEmailText,
					style: {
            marginBottom: '10px'
					}
				}),
				this.recoveryEmailField
			]
		})
	];

	GO.users.PasswordPanel.superclass.constructor.call(this, config);
};

Ext.extend(GO.users.PasswordPanel, Ext.Panel,{
	onSaveSettings : function(){
		this.currentPasswordField.reset();
		this.passwordField1.reset();
		this.passwordField2.reset();
	}
});