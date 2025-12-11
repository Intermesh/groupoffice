import {root} from "@intermesh/goui";
import {authManager, client, main,  modules, router} from "@intermesh/groupoffice-core";


router.newMainLayout = true;

// Todo, make this configurable or auto load?
client.uri = "/api/";

// Authenticate
authManager.requireLogin().then(async () => {

	// Load modules
	await modules.loadAll();

	//todo this was already fired before loading the modules. Change init() functions or load before firing?
	client.fireAuth();

	// Loads all panels
	main.load();

	// Add the Main component holding all the module panels
	root.items.add(main);

	// fire off the router
	void router.start();
});