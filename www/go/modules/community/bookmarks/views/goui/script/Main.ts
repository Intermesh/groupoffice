import {btn, combobox, Component, searchbtn, t, tbar} from "@intermesh/goui";
import {BookmarksGrid} from "./BookmarksGrid.js";
import {jmapds} from "@intermesh/groupoffice-core";
import {BookmarksDialog} from "./BookmarksDialog.js";
import {ManageCategoriesWindow} from "./ManageCategoriesWindow.js";

export class Main extends Component {
	constructor() {
		super();


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
			combobox({
				dataSource: jmapds("BookmarksCategory"),
				label: t("Category"),
				name: "category",
				listeners: {
					change: () => {

					}
				}
			}),
			'->',
			searchbtn({
				listeners: {
					input: (sender, text) => {

					}
				}
			})
		);
		this.items.add(toolbar);


		const grid = new BookmarksGrid();
		this.items.add(grid)

		void grid.store.load();
	}
}