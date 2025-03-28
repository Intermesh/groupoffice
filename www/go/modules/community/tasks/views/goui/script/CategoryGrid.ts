import {
	btn,
	checkbox, checkboxcolumn, column,
	Component,
	datasourcestore,
	DataSourceStore,
	h3,
	menu,
	t, table,
	tbar
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class CategoryGrid extends Component {
	public store: DataSourceStore;

	constructor() {
		super();

		this.store = datasourcestore({
			dataSource: jmapds("TaskCategory")
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
					text: t("Categories")
				}),
				"->",
				btn({
					icon: "add",
					handler: () => {

					}
				})
			),
			table({
				cls: "no-row-lines",
				headers: false,
				fitParent: true,
				store: this.store,
				columns: [
					checkboxcolumn({
						id: "check",
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
						renderer: (columnValue, record, td, table1, storeIndex) => {
							return btn({
								icon: "more_vert",
								menu: menu({},
									btn({
										icon: "edit",
										text: t("Edit"),
										handler: () => {

										}
									}),
									btn({
										icon: "delete",
										text: t("Delete"),
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