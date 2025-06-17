import {comp, Component, DataSourceStore} from "@intermesh/goui";
import {client, img} from "@intermesh/groupoffice-core";
import {BookmarkContextMenu} from "./BookmarkContextMenu.js";

export class BookmarksColumnView extends Component {
	public store: DataSourceStore;

	constructor(store: DataSourceStore) {
		super();

		this.cls = "scroll bookmark-column-view";

		this.store = store;

		this.store.on("load", (store, bookmarks) => {
			this.items.clear();

			const container = comp({cls: "flow"});

			let lastCategoryId = 0;
			let lastCategoryComp: Component;

			bookmarks.forEach((bookmark) => {
				if (bookmark.category.id != lastCategoryId || typeof (lastCategoryComp) == undefined) {
					lastCategoryComp = comp({
							cls: "vbox bookmark-column"
						},
						comp({tagName: "h3", text: bookmark.category.name})
					)

					lastCategoryId = bookmark.category.id;
					container.items.add(lastCategoryComp);
				}

				const bookmarkComp = comp({
						cls: "hbox bookmark-in-column",
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
					img({
						cls: "bookmark-logo",
						blobId: bookmark.logo
					}),
					comp({
						cls: "bookmark-name",
						tagName: "h4",
						text: bookmark.name
					})
				);

				lastCategoryComp.items.add(bookmarkComp);
			});

			this.items.add(container);
		});
	}
}