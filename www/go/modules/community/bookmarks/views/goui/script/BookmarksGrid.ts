import {btn, comp, Component, datasourcestore, DataSourceStore, menu, t} from "@intermesh/goui";
import {jmapds, img, client} from "@intermesh/groupoffice-core";
import {BookmarksDialog} from "./BookmarksDialog.js";

export class BookmarksGrid extends Component {
	public store: DataSourceStore;

	constructor() {
		super();

		this.cls = "pad";

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

						let writtenByUser = (client.user.id == bookmark.creator.id);

						const bookmarkComp = comp({
								cls: "bookmark flow",
								listeners: {
									beforerender: (cmp) => {
										cmp.el.addEventListener("click", ev => {
											ev.preventDefault();

											if(bookmark.openExtern){
												window.open(bookmark.content);
											} else {
												window.open(bookmark.content, "_self");
											}

										})
									},
									render: (cmp) => {
										cmp.el.addEventListener("contextmenu", ev => {
											ev.preventDefault();

											const contextMenu = menu({
													isDropdown: true
												},
												btn({
													icon: "edit",
													text: t("Edit"),
													disabled: client.user.isAdmin ? false : !writtenByUser,
													handler: () => {
														if (client.user.isAdmin || writtenByUser) {
															const dlg = new BookmarksDialog();
															void dlg.load(bookmark.id);
															dlg.show();
														}
													}
												}),
												btn({
													icon: "delete",
													text: t("Delete"),
													disabled: client.user.isAdmin ? false : !writtenByUser,
													handler: () => {
														if (client.user.isAdmin || writtenByUser) {
															jmapds("Bookmark").confirmDestroy([bookmark.id]);
														}
													}
												})
											)

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