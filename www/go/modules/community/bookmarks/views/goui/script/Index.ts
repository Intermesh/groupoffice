import {client, JmapDataSource, modules, router} from "@intermesh/groupoffice-core";
import {BaseEntity, t, translate} from "@intermesh/goui";
import {Main} from "./Main.js";
import {BookmarkIFrame} from "./BookmarkIFrame.js";

modules.register({
	package: "community",
	name: "bookmarks",
	async init() {
		client.on("authenticated", async ({session}) => {
			if (!session.capabilities["go:community:bookmarks"]) {
				return;
			}

			const ids = (await bookmarkDS.query({
				filter: {behaveAsModule: true}
			})).ids;

			if (ids) {
				const bookmarkModules = await bookmarkDS.get(ids);

				bookmarkModules.list.forEach((b) => {
					const name = b.name.replace(/\s/g, '-');

					modules.addMainPanel("community", "bookmarks", name, name, () => {
						return new BookmarkIFrame(b);
					});
				});
			}

			translate.load(GO.lang.community.bookmarks, "community", "bookmarks");

			router.add(/^bookmarks\/(\d+)$/, (bookmarkId) => {
				modules.openMainPanel("bookmarks")
			});

			modules.addMainPanel("community", "bookmarks", "bookmarks", t("Bookmarks"), () => {
				return new Main();
			});
		})
	},
	entities: ["Bookmark", "BookmarksCategory"]
});

export interface Bookmark extends BaseEntity {
	categoryId: string,
	createdBy: string,
	name: string,
	content: string,
	description: string
}

export interface BookmarksCategory extends BaseEntity {
	createdBy: string,
	name: string
}

export const bookmarkDS = new JmapDataSource<Bookmark>("Bookmark");
export const bookmarksCategoryDS = new JmapDataSource<BookmarksCategory>("BookmarksCategory");
