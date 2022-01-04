/* global go */

go.cron.NewCronDialog = Ext.extend(go.form.Dialog, {
	title: t('Scheduled tasks'),
	entityStore: "CronJobSchedule",
	// width: dp(1000),
	// height: dp(800),
	resizable: true,
	maximizable: true,
	collapsible: true,
	modal: false,

	initFormItems: function () {
		return [{
			xtype: "fieldset",
			items: [{
				xtype: 'textfield',
				fieldLabel: t('Description'),
				name: 'description'
			}, {
				xtype: 'textfield',
				fieldLabel: t('Expression'),
				name: 'expression'
			}, {
				xtype: 'checkbox',
				fieldLabel: t('Enabled'),
				name: 'enabled'
			}]
		}];
	}
});


