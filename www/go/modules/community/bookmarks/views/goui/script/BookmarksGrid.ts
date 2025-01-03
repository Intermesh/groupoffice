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

					const container = comp({cls: "hbox"})

					bookmarks.forEach((bookmark) => {
						const bookmarkComp = comp({
							cls: "bookmark",
							width: 300,
							height: 100
						});

						const logo = img({
							cls: "bookmark-logo",
							blobId: bookmark.logo,
							width: 30,
							height: 30
						});

						bookmarkComp.items.add(
							comp({cls: "hbox fit"},
								logo,
								comp({cls: "fit"},
									comp({tagName: "h4", text: bookmark.name}),
									comp({tagName: "p", text: bookmark.description})
								)
							)
						);

						container.items.add(bookmarkComp);
					});

					this.items.add(container);
				}
			}
		})
	}
}