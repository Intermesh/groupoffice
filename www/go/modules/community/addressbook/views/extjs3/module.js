go.Modules.register("community", "addressbook", {
	mainPanel: "go.modules.community.addressbook.MainPanel",
	title: t("Addressbook"),
	entities: ["Contact", "AddressBook"],
	initModule: function () {}
});
