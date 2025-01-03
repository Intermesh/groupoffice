import {comp, Component, datasourcestore, DataSourceStore} from "@intermesh/goui";
import {jmapds, img} from "@intermesh/groupoffice-core";

export class BookmarksGrid extends Component {
	public store: DataSourceStore;

	constructor() {
		super();

		this.cls = "pad";

		this.store = datasourcestore({
			dataSource: jmapds("Bookmark"),
			sort: [{property: "categoryId", isAscending: true}],
			listeners: {
				load: (store, bookmarks) => {
					this.items.clear();

					const container = comp({cls: "flow"});

					let lastCategoryId = 0;

					bookmarks.forEach((bookmark) => {
						if (bookmark.category.id != lastCategoryId) {
							container.items.add(
								comp({tagName: "h3", text: bookmark.category.name})
							)

							lastCategoryId = bookmark.category.id;
						}

						const bookmarkComp = comp({
								cls: "bookmark flow",
								width: 300,
								height: 100,
								style: {
									overflow: "hidden",
									textOverflow: "ellipsis"
								}
							},
							comp({flex:1, cls: "hbox"},
								img({
									style: {
										width: "32px",
										height: "32px",
									},
									blobId: bookmark.logo
								}),
								comp({
										cls: "vbox"
									},
									comp({
										text: bookmark.name,
										style: {
											paddingLeft: "0.5rem"
										}
									}),
									comp({
										cls: "",
										text: bookmark.description,
										style: {
											padding: "0.5rem",
											fontSize: "11px"
										}
									})
								)
							)
						);

						container.items.add(bookmarkComp);
					});

					this.items.add(container);
				}
			},
			relations: {
				category: {
					path: "categoryId",
					dataSource: jmapds("BookmarksCategory")
				}
			}
		})
	}
}