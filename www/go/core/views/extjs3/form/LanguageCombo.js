go.form.LanguageCombo = Ext.extend(go.form.ComboBox, {
	initComponent: function() {
		Ext.apply(this,{
			fieldLabel: t('Language'),
			store:  new Ext.data.SimpleStore({
				fields: ['id', 'language'],
				data : GO.Languages
			}),
			hiddenName: 'language',
			displayField:'language',
			iconClsField:'id',
			valueField: 'id',
			mode:'local',
			triggerAction:'all',
			forceSelection: true,
			value: GO.lang.iso
		});
		this.supr().initComponent.call(this);
	}
});

Ext.reg("golanguagecombo", go.form.LanguageCombo);