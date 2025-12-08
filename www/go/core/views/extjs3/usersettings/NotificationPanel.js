/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
go.usersettings.NoticiationPanel = Ext.extend(Ext.Panel, {

	initComponent() {

		this.notificationsFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			title: t('Notifications','users','core'),
			items:[{
				xtype:'xcheckbox',
				boxLabel: t("Mail reminders", "users", "core"),
				name: 'mail_reminders'
			}]
		});

		this.soundsFieldset = new Ext.form.FieldSet({
			labelWidth:dp(160),
			title: t('Sounds','users','core'),
			items:[{
				xtype:'xcheckbox',
				hideLabel: true,
				boxLabel: t("Mute all sounds", "users", "core"),
				name: 'mute_sound',
				listeners:{
					check: (cb, val) =>{
						if(val)	{
							this.cbMuteNewMailSound.disable();
							this.cbMuteReminderSound.disable();
						}	else {
							this.cbMuteNewMailSound.enable();
							this.cbMuteReminderSound.enable();
						}
					}
				}
			},this.cbMuteReminderSound = new Ext.ux.form.XCheckbox({
				xtype:'checkbox',
				boxLabel: t("Mute reminder sounds", "users", "core"),
				name: 'mute_reminder_sound'
			}),this.cbMuteNewMailSound = new Ext.ux.form.XCheckbox({
				xtype:'checkbox',
				boxLabel: t("Mute new mail sounds", "users", "core"),
				name: 'mute_new_mail_sound'
			})]
		});

		Ext.apply(this, {
			title: t('Notifications', 'users', 'core'),
			autoScroll: true,
			iconCls: 'ic-alarm',
			items: [
				this.notificationsFieldset,
				this.soundsFieldset
			]
		});

		this.supr().initComponent.call(this);
	}
});