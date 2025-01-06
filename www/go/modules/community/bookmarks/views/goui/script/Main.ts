import {btn, combobox, comp, Component, Filter, searchbtn, t, tbar} from "@intermesh/goui";
import {BookmarksGridView} from "./BookmarksGridView.js";
import {jmapds} from "@intermesh/groupoffice-core";
import {BookmarksDialog} from "./BookmarksDialog.js";
import {ManageCategoriesWindow} from "./ManageCategoriesWindow.js";
import {BookmarksColumnView} from "./BookmarksColumnView.js";

export class Main extends Component {
	private isGridView: boolean = true;

	constructor() {
		super();

		const gridView = new BookmarksGridView();
		void gridView.store.load();

		const columnView = new BookmarksColumnView();
		void columnView.store.load();
		columnView.hidden = true;

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
					this.isGridView = !this.isGridView;

					gridView.hidden = !this.isGridView;
					columnView.hidden = this.isGridView;
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
						gridView.store.setFilter("categoryId", {categoryId: newValue});
						columnView.store.setFilter("categoryId", {categoryId: newValue});

						void gridView.store.load();
						void columnView.store.load();
					},
					change: (field, newValue, oldValue) => {
						if (newValue === "") {
							gridView.store.clearFilter("categoryId");
							columnView.store.clearFilter("categoryId");

							void gridView.store.load();
							void columnView.store.load();
						}
					}
				},
				clearable: true
			}),
			'->',
			searchbtn({
				listeners: {
					input: (sender, text) => {
						(gridView.store.queryParams.filter as Filter).text = text;
						void gridView.store.load();

						(columnView.store.queryParams.filter as Filter).text = text;
						void columnView.store.load();
					}
				}
			})
		);

		this.items.add(toolbar);
		this.items.add(gridView);
		this.items.add(columnView);
	}
}