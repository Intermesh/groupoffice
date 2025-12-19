import {root} from "@intermesh/goui";
import {authManager, client, customFields, main, modules, router} from "@intermesh/groupoffice-core";

// Todo, make this configurable or auto load?
client.uri = "/api/";


// Loads all module scripts before authentication
await modules.loadUI();

// Authenticate
authManager.requireLogin().then(async () => {

	// Load custom fields and server modules
	await Promise.all([
		customFields.init(),
		modules.init()
	])

	// loads legacy module panels. We need to be authenticated for that.
	modules.loadLegacyUI();

	// Loads all panels
	main.load();

	// Add the Main component holding all the module panels
	root.items.add(main);

	// fire off the router
	void router.start();
});