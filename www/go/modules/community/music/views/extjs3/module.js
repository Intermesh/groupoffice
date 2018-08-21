go.Modules.register("community", "music", {
	mainPanel: "go.modules.community.music.MainPanel",
	
	//The title is shown in the menu and tab bar
	title: t("Music"),
	
	//All module entities must be defined here. Stores will be created for them.
	entities: ["Genre", "Album", "Artist"],
	
	//Put code to initialize the module here.
	initModule: function () {}
});
