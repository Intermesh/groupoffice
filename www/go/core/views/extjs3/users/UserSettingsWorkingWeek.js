go.users.UserSettingsWorkingWeek = Ext.extend(Ext.Panel, {
	
	iconCls: 'ic-access-time',
	title: t("Working week"),

	initComponent: function() {

		this.items = [{
			layout: 'form',
			labelWidth: 100,
			items: [{
				xtype: 'fieldset',
				title: t("Working hours"),				
				defaults: {
					serverFormats: false,
					maxValue: 24,
					minValue: 0,
					decimals: 2
				},
				items: [
					this.wwMoField = new GO.form.NumberField({
						fieldLabel: t("full_days")[1], 
						name:'workingWeek.mo_work_hours'
					}),this.wwTuField = new GO.form.NumberField({
						fieldLabel: t("full_days")[2], 
						name:'workingWeek.tu_work_hours'
					}),this.wwWeField = new GO.form.NumberField({
						fieldLabel: t("full_days")[3], 
						name:'workingWeek.we_work_hours'
					}),this.wwThField = new GO.form.NumberField({
						fieldLabel: t("full_days")[4], 
						name:'workingWeek.th_work_hours'
					}),this.wwFrField = new GO.form.NumberField({
						fieldLabel: t("full_days")[5], 
						name:'workingWeek.fr_work_hours'
					}),this.wwSaField = new GO.form.NumberField({
						fieldLabel: t("full_days")[6], 
						name:'workingWeek.sa_work_hours'
					}),this.wwSuField = new GO.form.NumberField({
						fieldLabel: t("full_days")[0], 
						name:'workingWeek.su_work_hours'
					})
				]
			}]
		}];
		go.users.UserSettingsWorkingWeek.superclass.initComponent.call(this);
	}
});
