Ext.onReady(function () {
	GO.mainLayout.on("render", function () {
		if (!go.User.isAdmin) {
			return;
		}
		Ext.Msg.confirm(t("Add demo data?", "demodata"), t("Welcome to Group-Office! We can add some demo users and demonstration data to Group-Office. All users will have the password 'demo'. Do you want to add this?", "demodata"), function (btn) {
			if (btn == 'yes') {
				document.location = GO.url('demodata/demodata/create');
			} else {

				var demodataMod = go.Db.store("Module").findBy(function (mod) { console.log(mod); return mod.name == 'demodata'; });
				GO.request({
					url: 'modules/module/delete',
					params: {
						id: demodataMod.id
					}
				});
			}
		});
	});
});