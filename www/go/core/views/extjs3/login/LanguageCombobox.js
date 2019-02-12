go.login.LanguageCombobox = Ext.extend(Ext.form.ComboBox,{

	initComponent: function() {
		
		Ext.apply(this,{
			fieldLabel: t('Language'),
			name: 'language_text',
			store:  new Ext.data.SimpleStore({
				fields: ['id', 'language'],
				data : GO.Languages
			}),
			hiddenName: 'login_language',
			displayField:'language',
			iconClsField:'id',
			valueField: 'id',
			mode:'local',
			triggerAction:'all',
			forceSelection: false,
			editable: false,
			value: GO.lang.iso,
			maxHeight:999999,
			width:dp(192)
 		});

//		this.tpl = '<tpl for="."><div class="x-combo-list-item">' +
//		'<table><tbody><tr><td>' +
//			'<div class="flag flag-{'+this.valueField+'}"></div>' +
//		'</td><td>'+
//			'{' + this.displayField + '}'+ 
//		'</td></tr></tbody></table>' +
//		'</div></tpl>';
		go.login.LanguageCombobox.superclass.initComponent.call(this);
	}
});
