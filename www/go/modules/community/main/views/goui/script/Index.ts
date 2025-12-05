import {btn, comp, Component, menu, root, Menu, CardMenu, cardmenu, cards, router} from "@intermesh/goui";
import {authManager, modules, client, MainPanel} from "@intermesh/groupoffice-core";

export class Main extends Component {
	private menu: CardMenu;
	private container: Component;
	constructor() {
		super();

		this.cls = "fit vbox";
		this.menu = cardmenu({
			cls: "main-menu"
		});
		this.container = cards({
			flex: 1
		});

		this.items.add(this.menu, this.container);

		// Get all registered panels
		modules.getMainPanels().forEach(async (m) => {

			const itemId =  m.package + "/" +m.module;

			// Add route to the panel
			router.add(new RegExp(`^${m.package}/${m.module}$`), () => {
				return this.openPanel(m)
			});

			// Add button to the route
			this.menu.items.add(
					btn({
						itemId,
						text: m.title,
						handler: () => {
							router.goto(itemId);
						}
					})
				);
			// }
		});
	}

	private async openPanel(m:MainPanel) {
		const itemId =  m.package + "/" +m.module;

		let cmp = this.container.findChild(itemId);
		if(!cmp) {
			cmp = await m.callback();
			cmp.itemId = itemId;
			this.container.items.add(cmp);
		}
		cmp.show();
	}
}

// Todo, make this configurable or auto load?
client.uri = "/api/";

// Authenticate
authManager.requireLogin().then(async () => {

	// Load modules
	await modules.loadAll();

	//todo this was already fired before loading the modules. Change init() functions or load before firing?
	client.fireAuth();

	// Add the Main component holding all the module panels
	root.items.add(new Main());

	// fire off the router
	void router.start();
});