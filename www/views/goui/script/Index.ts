import {root, router} from "@intermesh/goui";
import {authManager, client, customFields, main, modules} from "@intermesh/groupoffice-core";

client.uri = BaseHref + "api/";


// Loads all module scripts before authentication
await modules.loadCapabilities();

// Authenticate
authManager.requireLogin().then(async () => {

	// Load custom fields and server modules
	await Promise.all([
		customFields.init(),
		modules.init()
	])

	// Loads all panels in the main view
	main.load();

	// Add the Main view component holding all the module panels
	root.items.add(main);

	// fire off the router
	void router.start();
});