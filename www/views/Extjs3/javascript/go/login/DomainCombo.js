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
					data: GO.authenticationDomains.map(function (i) {
						return [i, i];
					}),
					idIndex: 0
				});
				
				go.login.DomainCombo.superclass.initComponent.call(this);				
			},
			
			setDomains : function(domains) {
				GO.authenticationDomains = domains;
				
				this.store.loadData(GO.authenticationDomains.map(function (i) {
					return [i, i];
				}));
			}
		
});
