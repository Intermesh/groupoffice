import {btn, comp, Component, menu, root, Menu} from "@intermesh/goui";
import {authManager, modules, client} from "@intermesh/groupoffice-core";

export class Main extends Component {
	private menu: Menu;
	private container: Component;
	constructor() {
		super();
		this.menu = menu();
		this.container = comp();
		this.items.add(this.menu, this.container);

		modules.getMainPanels().forEach(async (m) => {

			if(m.module == "notes") {

				const cmp = await m.callback();
				this.container.items.replace(cmp);

				// this.menu.items.add(
				// 	btn({
				// 		text: m.title,
				// 		handler: async () => {
				// 			const cmp = await m.callback();
				// 			this.container.items.replace(cmp);
				// 		}
				// 	})
				// );
			}
		});
	}

}



client.uri = "/api/"


await modules.loadModule("community", "notes");

authManager.requireLogin().then(async () => {

	// root.items.add(new Main());
	root.cls = "fit";

	modules.getMainPanels().forEach(async (m) => {

		if (m.module == "notes") {

			const cmp = await m.callback();
			root.items.add(cmp);
		}
	});

});