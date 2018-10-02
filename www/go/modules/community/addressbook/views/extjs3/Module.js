go.Modules.register("community", "addressbook", {
	mainPanel: "go.modules.community.addressbook.MainPanel",
	title: t("Addressbook"),
	entities: ["Contact", "AddressBook", "AddressBookGroup"],
	systemSettingsPanels: ["go.modules.community.addressbook.SystemSettingsPanel"],
	initModule: function () {}
});

//go.Stores.get("User");