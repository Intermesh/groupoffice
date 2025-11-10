import {modules, authManager, client} from "@intermesh/groupoffice-core";
import {btn, CardMenu, comp, Component, Menu, menu, root} from "@intermesh/goui";

client.uri = "../../../../api/";

class Main extends Component {
	private menu: Menu;
	private container: Component;
	constructor() {
		super();
		
		this.menu = menu();
		this.container = comp();

		this.items.add(this.menu, this.container);

		console.log(modules);

		modules.getMainPanels().forEach(m => {
			this.menu.items.add(
				btn({
					text: m.title,
					handler: async () => {
						const cmp = await m.callback()
						this.container.items.replace(cmp);
					}
				})
			)
		})
	}
}

authManager.requireLogin().then(async () => {

	root.items.add(new Main());



})