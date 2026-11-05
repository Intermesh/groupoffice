import {root, router} from "@intermesh/goui";
import {authManager, client, customFields, main, modules} from "@intermesh/groupoffice-core";

client.uri = BaseHref + "api/";

// Loads all panels in the main view
await main.boot();

// Add the Main view component holding all the module panels
root.items.add(main);

// fire off the router
void router.start();
