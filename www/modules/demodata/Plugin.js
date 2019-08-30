Ext.onReady(function () {
	GO.mainLayout.on("authenticated", function () {
		if (!go.User.isAdmin) {
			return;
		}
		Ext.Msg.confirm(t("Add demo data?", "demodata"), t("Welcome to Group-Office! We can add some demo users and demonstration data to Group-Office. All users will have the password 'demo'. Do you want to add this?", "demodata"), function (btn) {
			if (btn == 'yes') {
				document.location = GO.url('demodata/demodata/create');
			} else {
				GO.request({
					url: 'modules/module/delete',
					params: {
						id: go.Db.store("Module").findBy(function (mod) { console.log(mod); return mod.name == 'demodata'; }).id
					}
				});
			}
		});
	});
});