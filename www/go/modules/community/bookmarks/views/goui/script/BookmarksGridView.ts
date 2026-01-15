import {comp, Component, DataSourceStore} from "@intermesh/goui";
import {img, client} from "@intermesh/groupoffice-core";
import {BookmarkContextMenu} from "./BookmarkContextMenu.js";

export class BookmarksGridView extends Component {
	public store: DataSourceStore;

	constructor(store: DataSourceStore) {
		super();

		this.cls = "scroll pad bookmark-grid-view";

		this.store = store;

		this.store.on("load", ({target, records}) => {
			this.items.clear();

			const container = comp({cls: "flow"});

			let lastCategoryId = 0;

			records.forEach((bookmark) => {
				if (bookmark.category.id != lastCategoryId) {
					container.items.add(
						comp({tagName: "h3", text: bookmark.category.name})
					)

					lastCategoryId = bookmark.category.id;
				}

				const bookmarkComp = comp({
						cls: "bookmark",
						listeners: {
							beforerender: ({target}) => {
								target.el.addEventListener("click", ev => {
									ev.preventDefault();

									if (bookmark.openExtern) {
										window.open(bookmark.content);
									} else {
										window.open(bookmark.content, "_self");
									}

								})
							},
							render: ({target}) => {
								target.el.addEventListener("contextmenu", ev => {
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
		});
	}
}