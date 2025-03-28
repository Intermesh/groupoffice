import {
	btn,
	checkbox, checkboxcolumn, column,
	Component,
	datasourcestore,
	DataSourceStore, h3, hr,
	menu,
	searchbtn,
	t,
	table,
	tbar
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class TasklistGrid extends Component {
	public store: DataSourceStore;

	constructor() {
		super();

		this.store = datasourcestore({
			dataSource: jmapds("TaskList"),
			filters: {
				role: {
					role: "list"
				},
				subscribed: {
					isSubscribed: true
				}
			},
			sort: [{property: "name", isAscending: true}]
		});

		this.items.add(
			tbar({},
				checkbox({
					listeners: {
						change: (field, newValue, oldValue) => {

						}
					}
				}),
				h3({
					text: t("Lists")
				}),
				"->",
				searchbtn({
					listeners: {
						input: (sender, text) => {

						}
					}
				}),
				btn({
					icon: "more_vert",
					menu: menu({},
						btn({
							icon: "add",
							text: t("Create task list..."),
							handler: () => {

							}
						}),
						btn({
							icon: "bookmark_added",
							text: t("Subscribe to task list..."),
							handler: () => {

							}
						})
					)
				})
			),
			table({
				cls: "no-row-lines",
				headers: false,
				store: this.store,
				fitParent: true,
				columns: [
					checkboxcolumn({
						id: "filter",
						listeners: {
							change: (col, field, value, record, storeIndex) => {

							}
						}
					}),
					column({
						id: "name"
					}),
					column({
						id: "btn",
						sticky: true,
						width: 32,
						renderer: (columnValue, record, td, table, storeIndex, column) => {
							return btn({
								icon: "more_vert",
								menu: menu({},
									btn({
										icon: "edit",
										text: t("Edit..."),
										handler: () => {

										}
									}),
									btn({
										icon: "delete",
										text: t("Delete..."),
										handler: () => {

										}
									}),
									hr({}),
									btn({
										icon: "remove_circle",
										text: t("Unsubscribe"),
										handler: () => {

										}
									})
								)
							})
						}
					})
				]
			})
		)
	}
}
