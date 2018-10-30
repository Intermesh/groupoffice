go.modules.community.pages.SitePropertiesForm = Ext.extend(Ext.form.FormPanel, {
	
	isValid: function () {

		return  this.getForm().isValid();
	},
	
	initComponent: function () {
	    
		this.items = [{
				xtype: 'fieldset',
				title: t('Site properties'),
				items: [
					{
						xtype: 'textfield',
						name: 'siteName',
						fieldLabel: t("site name"),
						anchor: '100%',
						allowBlank: false,
					}, {
					    xtype: 'combo',
						name: 'documentFormat',
						fieldLabel: t("document format"),
						anchor: '100%',
						allowBlank: false,
						triggerAction: 'all',
						hiddenName: 'documentFormat',
						emptyText: t("Please select..."),
						editable: true,
						selectOnFocus: true,
						forceSelection: true,
						store: [['html', 'html'],['mark','markdown']],
						value: "html"
					},
				]
			}
		];
		 go.modules.community.pages.SitePropertiesForm.superclass.initComponent.call(this);
	},
	
});


