/*

GO.moduleManager.onModuleReady('calendar',function() {
	Ext.override(GO.email.AccountDialog, {
		render: GO.email.AccountDialog.prototype.render.createSequence(function () {
// debugger;
			this.selectAuthMethodCombo = new go.form.ComboBox({
				fieldLabel: t("Select Authentication Method"),
				hiddenName: 'authenticationMethod',
				anchor: '100%',
				emptyText: t("Please select..."),
				pageSize: 50,
				valueField: 'id',
				displayField: 'text',
				triggerAction: 'all',
				editable: false,
				selectOnFocus: true,
				forceSelection: true,
				store: new Ext.data.ArrayStore({
					fields: ['id', 'text'],
					data: [
						['credentials', t("User name and password")],
						['OAuth2', t("Google OAuth2")]
					]
				}),
				handler: function (a, b, c) {
					console.log(a);
					console.log(b);
					console.log(c);

				}
			});
			this.incomingTab.items.push(this.selectAuthMethodCombo);
			// GO.email.AccountDialog.prototype.superclass.render.call(this);

			// return true;
		})

	});
});

*/
