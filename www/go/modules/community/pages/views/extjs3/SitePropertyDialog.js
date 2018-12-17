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
			fieldLabel: t("site name"),
			anchor: '100%',
			allowBlank: false
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
			store: [['html', 'html'], ['mark', 'markdown']],
		    },
		]
	    }
	];
	return items;
    },
    submit: function () {

	if (!this.isValid()) {
	    return;
	}

	this.actionStart();

	this.formPanel.submit(function (formPanel, success, serverId) {
	    this.actionComplete();
	    if (success) {
		this.close();
	    }
	}, this);
    }
});

