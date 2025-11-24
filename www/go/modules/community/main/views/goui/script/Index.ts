import {btn, comp, Component, menu, root, Menu, CardMenu, cardmenu, cards} from "@intermesh/goui";
import {authManager, modules, client} from "@intermesh/groupoffice-core";

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

		modules.getMainPanels().forEach(async (m) => {

			const itemId =  m.package + "/" +m.module;

			this.menu.items.add(
					btn({
						itemId,
						text: m.title,
						handler: async () => {

							let cmp = this.container.findChild(itemId);
							if(!cmp) {
								cmp = await m.callback();
								cmp.itemId = itemId;
								this.container.items.add(cmp);
							}
							cmp.show();

						}
					})
				);
			// }
		});
	}

}



client.uri = "/api/"


await Promise.all(
	[
		modules.loadModule("community", "notes"),
		modules.loadModule("community", "calendar"),
		modules.loadLegacy()
	]
);

authManager.requireLogin().then(async () => {
	root.items.add(new Main());
});