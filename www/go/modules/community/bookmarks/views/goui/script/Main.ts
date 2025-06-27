import {
	btn,
	combobox,
	comp,
	Component,
	datasourcestore,
	DataSourceStore,
	Filter,
	searchbtn,
	t,
	tbar
} from "@intermesh/goui";
import {BookmarksGridView} from "./BookmarksGridView.js";
import {jmapds} from "@intermesh/groupoffice-core";
import {BookmarksDialog} from "./BookmarksDialog.js";
import {ManageCategoriesWindow} from "./ManageCategoriesWindow.js";
import {BookmarksColumnView} from "./BookmarksColumnView.js";

export class Main extends Component {
	private isGridView: boolean = true;
	private readonly store: DataSourceStore;

	constructor() {
		super();

		this.store = datasourcestore({
			dataSource: jmapds("Bookmark"),
			sort: [{property: "category", isAscending: true}, {property: "name"}],
			queryParams: {
				limit: 0,
				filter: {
					permissionLevel: 5
				}
			},
			relations: {
				category: {
					path: "categoryId",
					dataSource: jmapds("BookmarksCategory")
				},
				creator: {
					path: "createdBy",
					dataSource: jmapds("Principal")
				}
			}
		});


		const gridView = new BookmarksGridView(this.store);

		const columnView = new BookmarksColumnView(this.store);
		columnView.hidden = true;

		void this.store.load();

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
					setvalue: ( {newValue}) => {
						this.store.setFilter("categoryId", {categoryId: newValue});

						void this.store.load();
					},
					change: ({newValue}) => {
						if (newValue === "") {
							this.store.clearFilter("categoryId");

							void this.store.load();
						}
					}
				},
				clearable: true
			}),
			'->',
			searchbtn({
				listeners: {
					input: ( {text}) => {
						this.store.setFilter("search", {text: text});
						void this.store.load();
					}
				}
			})
		);

		this.items.add(toolbar);
		this.items.add(gridView);
		this.items.add(columnView);
	}
}