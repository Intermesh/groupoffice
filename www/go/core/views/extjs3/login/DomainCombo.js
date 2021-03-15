go.login.DomainCombo = Ext.extend(GO.form.ComboBoxReset, {
	anchor: "100%",
	displayField: "label",
	valueField: "value",
	emptyText: t("None"),
	mode: "local",
	editable: false,
	fieldLabel: t("Domain"),
	hiddenName: "domain",
	triggerAction: "all",
	initComponent : function(){
		this.store = new Ext.data.ArrayStore({
			fields: ['label', 'value'],
			data: go.User.session.auth.domains.map(function (i) {
				return [i, i];
			}),
			idIndex: 0
		});

		go.login.DomainCombo.superclass.initComponent.call(this);
	},

	reloadDomains : function() {
		go.User.load().then((u) => {
			this.store.loadData(u.session.auth.domains.map(function (i) {
				return [i, i];
			}));

			this.setVisible(u.session.auth.domains.length > 0);

			//needed for trigger rendering issue
			this.onResize();
		});
	}
		
});
