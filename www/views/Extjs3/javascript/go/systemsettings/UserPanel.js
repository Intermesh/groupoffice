go.systemsettings.UserPanel = Ext.extend(Ext.Panel, {

	initComponent: function () {

		Ext.apply(this,{
			title:t('User'),
			autoScroll:true,
			iconCls: 'ic-person',
			layout:'column',
			items: [
				{
					columnWidth: .5,//left
					items:[this.allowSettings()]
				},{
					columnWidth: .5,//right
					items: [this.passwordSettings()]
				}
			]
		});
		
		go.systemsettings.UserPanel.superclass.initComponent.call(this);
	},
	
	allowSettings : function() {
		return new FieldSet({
			title:t('Allow'),
			items: [
				{
					xtype: 'xcheckbox',
					name : 'allow_themes',
					boxLabel: t('Enable theme switching by users')
				},{
					xtype: 'numberfield',
					name: 'limit_usersearch',
					fieldLabel: t('If set, user queries will only return this maximum number of users. Useful in large environments where you don\'t want users to scroll through all.')
				},{
					xtype: 'textfield',
					name: 'register_user_groups',
					fieldLabel: t('If set, new users will be added to the given groups automatically on creation. This needs to be a comma separated string of group names.')
				},{
					xtype: 'combobox',
					name: 'register_visible_user_groups',
					fieldLabel: 'This is a comma separated list of group names where new users are automatically visible to.'
				}
			]
		});
	},
	
	passwordSettings : function() {
		return new FieldSet({
			title:t('Password'),
			items: [
				{
					xtype: 'xcheckbox',
					name : 'allow_password_change',
					boxLabel: t('Enable password changing by users')
				},{
					xtype: 'xchecgbox',
					name : 'password_validate',
					boxLabel: t('Enable password validation (options below)')
				},{
					xtype: 'xcheckbox',
					name: 'password_require_uc',
					boxLabel: t('Require an uppercase characters')
				},{
					xtype:'xcheckbox',
					name: 'password_require_lc',
					boxLabel: t('Require a lowercase characters')
				},{
					xtype: 'xcheckbox',
					name: 'password_require_num',
					boxLabel: t('Require a number')
				},{
					xtype: 'xcheckbox',
					name: 'password_require_sc',
					boxLabel: t('Require a special char')
				},{
					xtype: 'numberfield',
					name: 'password_require_uniq',
					value: 3,
					fieldLabel: t('Required number of unique characters')
				},{
					xtype: 'numberfield',
					name: 'password_min_length',
					value: 6,
					fieldLabel: t('Minimum required password length')
				},{
					xtype: 'xcheckbox',
					name: 'disable_security_token_check',
					boxLabel: t('Disable security check for cross domain forgeries.')
				},{
					xtype: 'xcheckbox',
					name: 'force_ssl',
					boxLabel: t('Force an HTTPS connection in the main /index.php')
				},{
					xtype: 'numberfield',
					name: 'session_inactivity_timeout',
					fieldLabel: t('Automatically log a user out after n seconds of inactivity.')
				},{
					xtype: 'numberfield',
					name: 'force_password_change',
					value: 0,
					fieldLabel: t('The amount of days before a password change is forced to the user, When set to 0 this function is disabled')
				}
			]
		});
	}

});

GO.mainLayout.onReady(function(){
	go.systemSettingsDialog.addPanel('system-general', go.systemsettings.GeneralPanel);
});

