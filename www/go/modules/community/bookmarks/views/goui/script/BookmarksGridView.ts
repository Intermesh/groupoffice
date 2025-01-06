import {comp, Component, datasourcestore, DataSourceStore} from "@intermesh/goui";
import {jmapds, img, client} from "@intermesh/groupoffice-core";
import {BookmarkContextMenu} from "./BookmarkContextMenu.js";

export class BookmarksGridView extends Component {
	public store: DataSourceStore;

	constructor() {
		super();

		this.cls = "scroll pad bookmark-grid-view";


		this.store = datasourcestore({
			dataSource: jmapds("Bookmark"),
			sort: [{property: "categoryId", isAscending: true}],
			queryParams: {
				limit: 0,
				filter: {
					permissionLevel: 5
				}
			},
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
								cls: "bookmark",
								listeners: {
									beforerender: (cmp) => {
										cmp.el.addEventListener("click", ev => {
											ev.preventDefault();

											if (bookmark.openExtern) {
												window.open(bookmark.content);
											} else {
												window.open(bookmark.content, "_self");
											}

										})
									},
									render: (cmp) => {
										cmp.el.addEventListener("contextmenu", ev => {
											ev.preventDefault();

											const contextMenu = new BookmarkContextMenu(client.user, bookmark);

											contextMenu.showAt(ev);
										})
									}
								}
							},
							comp({
									cls: "hbox"
								},
								img({
									cls: "bookmark-logo",
									blobId: bookmark.logo
								}),
								comp({
										cls: "vbox",
										style: {
											width: "85%",
											paddingLeft: "1.5rem"
										}
									},
									comp({
										cls: "bookmark-name",
										tagName: "h4",
										text: bookmark.name
									}),
									comp({
										cls: "bookmark-desc",
										text: bookmark.description
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
				},
				creator: {
					path: "createdBy",
					dataSource: jmapds("Principal")
				}
			}
		})
	}
}