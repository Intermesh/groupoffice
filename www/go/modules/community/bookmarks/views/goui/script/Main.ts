import {btn, combobox, comp, Component, Filter, searchbtn, t, tbar} from "@intermesh/goui";
import {BookmarksGrid} from "./BookmarksGrid.js";
import {jmapds} from "@intermesh/groupoffice-core";
import {BookmarksDialog} from "./BookmarksDialog.js";
import {ManageCategoriesWindow} from "./ManageCategoriesWindow.js";

export class Main extends Component {
	constructor() {
		super();

		const grid = new BookmarksGrid();
		void grid.store.load();

		const toolbar = tbar({
				cls: "border-bottom"
			},
			btn({
				icon: "add",
				text: t("Add"),
				handler: () => {
					const dlg = new BookmarksDialog();

					dlg.show();
				}
			}),
			btn({
				icon: "view_module",
				text: t("Toggle view"),
				handler: () => {

				}
			}),
			btn({
				icon: "settings",
				text: t("Administrate categories"),
				handler: () => {
					const manageCategoriesGrid = new ManageCategoriesWindow();

					manageCategoriesGrid.show();
				}
			}),
			comp({tagName: "h5", text: t("Category")}),
			combobox({
				dataSource: jmapds("BookmarksCategory"),
				name: "category",
				placeholder: t("Show all"),
				listeners: {
					setvalue: (field, newValue, oldValue) => {
						grid.store.setFilter("categoryId", {categoryId: newValue});

						void grid.store.load();
					},
					change: (field, newValue, oldValue) => {
						if (newValue === "") {
							grid.store.clearFilter("categoryId");

							void grid.store.load();
						}
					}
				},
				clearable: true
			}),
			'->',
			searchbtn({
				listeners: {
					input: (sender, text) => {
						(grid.store.queryParams.filter as Filter).text = text;
						void grid.store.load();
					}
				}
			})
		);

		this.items.add(toolbar);
		this.items.add(grid)

	}
}