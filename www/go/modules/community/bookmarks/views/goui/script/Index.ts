import {client, JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {router, t, translate} from "@intermesh/goui";
import {Main} from "./Main.js";

modules.register({
	package: "community",
	name: "bookmarks",
	async init() {
		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:bookmarks"]) {
				// User has no access to this module
				return;
			}

			translate.load(GO.lang.community.bookmarks, "community", "bookmarks");

			router.add(/^bookmarks\/(\d+)$/, (bookmarkId) => {
				modules.openMainPanel("bookmarks")
			});

			modules.addMainPanel("community", "bookmarks", "bookmarks", t("Bookmarks"), () => {
				return new Main();
			});
		})
	}
});

export const bookmarkDS = new JmapDataSource("Bookmark");
export const bookmarksCategoryDS = new JmapDataSource("BookmarksCategory");
