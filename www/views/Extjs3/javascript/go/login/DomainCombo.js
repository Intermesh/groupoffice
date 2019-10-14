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
			
			reloadDomains : function() {
				Ext.Ajax.request({
					method: "GET",
					jsonData: {},
					url: go.User.apiUrl,
					callback: function(options, success, response) {
						var result = Ext.decode(response.responseText);								

						GO.authenticationDomains = result.auth.domains;

						this.store.loadData(GO.authenticationDomains.map(function (i) {
							return [i, i];
						}));
						
						this.setVisible(GO.authenticationDomains.length > 0);
						
						//needed for trigger rendering issue
						this.onResize();
					},
					scope: this
				});
			}
		
});
