import {
	avatar,
	browser,
	btn,
	checkbox,
	checkboxselectcolumn,
	column,
	comp,
	Component,
	DataSourceStore,
	form,
	Form,
	mstbar,
	Notifier,
	paginator,
	router,
	searchbtn,
	splitter,
	t,
	Table,
	tbar
} from "@intermesh/goui";
import {authManager, client, FilterCondition, img, JmapDataSource, jmapds, User} from "@intermesh/groupoffice-core";


export class MainPanel extends Component {


	private user?: User;
	// private west: Component;
	private center: Component;
	// private east: Component;

	constructor() {
		super("section");

		this.id = "support";
		this.cls = "vbox fit";
		this.on("render", async () => {
			try {
				this.user = await authManager.requireLogin();
			} catch (e) {
				console.warn(e);
				Notifier.error(t("Login is required on this page"));
			}

		});
		// this.west = this.createWest();

		this.items.add(
				comp({
						flex: 1, cls: "hbox mobile-cards"
					},
					// this.west,

					// splitter({
					// 	stateId: "support-splitter-west",
					// 	resizeComponentPredicate: this.west
					// }),

					this.center = comp({
							cls: 'active vbox',
							itemId: 'table-container',
							// style: {
							// 	minWidth: "365px", //for the resizer's boundaries
							// 	maxWidth: "850px"
							// }
						},

						tbar({},
							// btn({
							// 	cls: "for-small-device",
							// 	title: t("Menu"),
							// 	icon: "menu",
							// 	handler: (button, ev) => {
							// 		this.activatePanel(this.west);
							// 	}
							// }),

							'->',

							searchbtn({
								listeners: {
									input: (sender, text) => {

										// (this.taskTable.store.queryParams.filter as FilterCondition).text = text;
										// this.taskTable.store.load();

									}
								}
							}),

							// mstbar({table: this.taskTable}),

							btn({
								itemId: "add",
								icon: "add",
								text: t("New request"),
								cls: "filled primary",
								handler: async () => {

								}
							})
						),

						comp({
								flex: 1,
								stateId: "support",
								cls: "scroll border-top main"
							},
						),


						// paginator({
						// 	store: this.taskTable.store
						// })
					),


					// splitter({
					// 	stateId: "support-splitter",
					// 	resizeComponentPredicate: "table-container"
					// }),
					//
					// this.east = comp({
					// 		itemId: 'scroll-component',
					// 		tabIndex: -1,
					// 		flex: 1,
					// 		cls: 'scroll'
					// 	},
					// 	tbar({},
					// 		btn({
					// 			itemId: "back",
					// 			cls: "for-small-device",
					// 			icon: "chevron_left",
					// 			text: t("Back"),
					// 			handler: () => {
					// 				router.goto("supportclient");
					// 			}
					// 		})
					// 	),
					// )
				)
			);
	}

	// private activatePanel(active:Component) {
	// 	this.center.el.classList.remove("active");
	// 	this.east.el.classList.remove("active");
	// 	this.west.el.classList.remove("active");
	//
	// 	active.el.classList.add("active");
	// }


	// private createWest() {
	//
	//
	// 	return comp({
	// 			cls: "vbox scroll",
	// 			width: 300
	// 		},
	// 		tbar({
	//
	// 			},
	// 			comp({
	// 				tagName: "h3",
	// 				text: t("Help"),
	// 				flex: 1
	// 			}),
	// 			'->',
	// 			btn({
	// 				cls: "for-small-device",
	// 				title: t("Close"),
	// 				icon: "close",
	// 				handler: (button, ev) => {
	// 					this.activatePanel(this.center);
	// 				}
	// 			})
	// 		),
	// 		tbar({},
	// 			checkbox({
	// 				type: "switch",
	// 				label: t("Show completed"),
	// 				listeners: {
	//
	// 				}
	// 			})
	// 		),
	//
	// 		comp({tagName: "hr"}),
	// 	);


}
