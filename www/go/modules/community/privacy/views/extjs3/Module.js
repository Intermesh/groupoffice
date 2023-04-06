go.Modules.register("community", "privacy", {
	title: t("Privacy"),
	entities: [],
	/**
	 * These panels will show in the System settings
	 */
	systemSettingsPanels: [
		"go.modules.community.privacy.SystemSettingsPanel",
	],

	initModule: function () {}

});

go.modules.community.privacy.emptyTrashHandler = function(btn, e) {
	const module = go.Modules.get("community", "privacy"), settings = module.settings,
		trashABId = settings.trashAddressBook;
	Ext.MessageBox.confirm(t("Confirm"),
		t("Are you sure that you want to empty the trash address book?"),
		(b) => {
			if (b !== "yes") {
				return false;
			}
			go.Db.store("Contact").query({
					limit: 0,
					filter: {
						addressBookId: [trashABId]
					}
				},
				(result) => {
					if (!go.util.empty(result.ids)) {
						go.Db.store("Contact").set({
							destroy: result.ids
						}).then(() => {
							Ext.MessageBox.alert(t("Success"), t("The trash address book has been successfully emptied"));
						});
					}
				}, this);
		}
	);
	return false;
}

