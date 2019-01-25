go.modules.community.pages.SitePropertyDialog = Ext.extend(go.form.Dialog, {
    title: t('Properties'),
    entityStore: go.Stores.get("Site"),
    initFormItems: function () {
	var items = [{
		xtype: 'fieldset',
		items: [
		    {
			xtype: 'textfield',
			name: 'siteName',
			fieldLabel: t("Site Name"),
			anchor: '100%',
			allowBlank: false
		    },
//		    {
//			xtype: 'combo',
//			name: 'documentFormat',
//			fieldLabel: t("document format"),
//			anchor: '100%',
//			allowBlank: false,
//			triggerAction: 'all',
//			hiddenName: 'documentFormat',
//			emptyText: t("Please select..."),
//			editable: true,
//			selectOnFocus: true,
//			forceSelection: true,
//			store: [['html', 'html'], ['mark', 'markdown']],
//		    },
		]
	    }
	];
	return items;
    },
});

