import {btn, CardMenu, cardmenu, cards, comp, Component, root, router, searchbtn, tbar, Toolbar} from "@intermesh/goui";
import {authManager, client, entities, ExtJSWrapper, MainPanel, modules} from "@intermesh/groupoffice-core";




export class Main extends Component {
	private readonly menu;
	private readonly container;
	constructor() {
		super();

		this.cls = "fit vbox main-container";

		this.container = cards({
			flex: 1
		});

		this.menu = cardmenu({
			flex: 1,
			cls: "main-menu",
			overflowMenu: true,
			cardContainer: this.container
		});


	}

	/**
	 * Load all module panels and sets up routes
	 */
	public load() {
		this.items.add(
			comp({
				cls: "header hbox"
			},
				this.menu,
				tbar({},
					btn({
						icon: "notifications"
					}),
					searchbtn({
						icon: "search"
					}),
					btn({
						icon: "settings"
					})
				)
			),
			this.container
		);

		// Get all registered panels
		modules.getMainPanels().forEach(async (m) => {

			// Add route to the panel
			router.add(new RegExp(`^${RegExp.escape(m.id)}$`), () => {
				return this.openPanel(m.id)
			});

			// Add button to the route
			this.menu.items.add(
				btn({
					itemId: m.id,
					text: m.title,
					handler: () => {
						router.goto(m.id);
					}
				})
			);
			// }
		});

		this.addLegacyDefaultRoutes();
	}

	/**
	 * Support default routes to legacy detail panels in extjs3
	 * @private
	 */
	private addLegacyDefaultRoutes() {
		router.add(/([a-zA-Z0-9]*)\/([0-9]*)/, async (entity, id) => {

			const entityObj = entities.get(entity);
			if(!entityObj) {
				console.log("Entity ("+entity+") not found in default entity route")
				return false;
			}

			const detailViewName = entity.charAt(0).toLowerCase() + entity.slice(1) + "Detail";

			const mainPanelCmp = await this.openPanel(entityObj.package + "/" + entityObj.module) as ExtJSWrapper;

			if(!mainPanelCmp) {
				console.error("mainpanel not found!");
				return;
			}

			if (mainPanelCmp.extJSComp.route) {
				mainPanelCmp.extJSComp.route(id, entityObj);
			} else if(mainPanelCmp.extJSComp[detailViewName]) {
				mainPanelCmp.show();
				mainPanelCmp.extJSComp[detailViewName].load(id);
				mainPanelCmp.extJSComp[detailViewName].show();
			} else {
				console.log("Default entity route failed because " + detailViewName + " or 'route' function not found in mainpanel of " + entityObj.module + ":", mainPanelCmp);
				console.log(arguments);
			}
		});
	}

	public async openPanel(panelId:string) {
		const m = modules.getPanelById(panelId);
		if(!m) {
			throw "notfound";
		}

		let cmp = this.container.findChild(panelId);
		if(!cmp) {
			cmp = await m.callback();
			cmp.itemId = panelId;
			this.container.items.add(cmp);
		}


		//extjs3 panels have this func
		//@ts-ignore
		if(cmp.routeDefault) {
			//@ts-ignore
			cmp.routeDefault();
		}
		cmp.show();

		return cmp;
	}
}

// Todo, make this configurable or auto load?
client.uri = "/api/";

export const main = new Main();
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