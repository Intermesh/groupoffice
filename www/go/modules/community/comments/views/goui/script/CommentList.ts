import {
	a,
	avatar, browser, btn,
	comp,
	Component,
	datasourcestore,
	DataSourceStore,
	Format, menu, Notifier, router,
	t, win
} from "@intermesh/goui";
import {Image, client, img, jmapds} from "@intermesh/groupoffice-core";
import {CommentDialog} from "./CommentDialog.js";

export class CommentList extends Component {
	public store!: DataSourceStore
	public scroller!: Component

	constructor() {
		super()

		this.store = datasourcestore({
			dataSource: jmapds("Comment"),
			sort: [{property: "date", isAscending: true}],
			listeners: {
				load: ( {records}) => {
					this.items.clear();

					this.scroller = comp({
						flex: 1,
						cls: "scroll",
						style: {maxHeight: (document.body.offsetHeight * 0.7) + "px"}
					});

					const imgPromises: Promise<any>[] = [];

					let lastDate: string;

					records.forEach((comment) => {
						let currentDate = Format.date(comment.date);

						if (!lastDate || currentDate !== lastDate) {
							this.scroller.items.add(comp({
								tagName: "h5",
								style: {
									textAlign: "center",
									padding: "0.8rem 0"
								},
								text: currentDate
							}))

							lastDate = currentDate;
						}

						if(!comment.creator) {
							comment.creator = {
								name: t("Unknown user")
							};
						}

						const avatarCnt = comp({
								cls: "go-detail-view-avatar",
								itemId: "avatar-container",
								listeners: {
									render: ({target}) => {
										target.el.onclick = () => {
											go.modules.community.addressbook.lookUpUserContact(comment.creator.id)
										}
									}
								}
							},
							comment.creator.avatarId ?
								img({
									cls: "goui-avatar",
									style: {cursor: "pointer"},
									blobId: comment.creator.avatarId
								}) :
								avatar({
									style: {cursor: "pointer"},
									displayName: comment.creator.name
								})
						);

						let writtenByUser = (client.user.id == comment.creator.id);

						let commentTitle = t("{author} wrote at {date}")
							.replace("{author}", comment.creator.name)
							.replace("{date}", Format.dateTime(comment.createdAt));

						if (comment.createdAt != comment.modifiedAt) {
							commentTitle += "\n" + t("Edited by {author} at {date}")
								.replace("{author}", comment.modifier.name)
								.replace("{date}", Format.dateTime(comment.modifiedAt));
						}

						if (comment.createdAt != comment.date) {
							commentTitle += "\n" + t("The date was changed to {date}")
								.replace("{date}", Format.dateTime(comment.date));
						}

						const commentComp = comp({
							flex: 1,
							cls: "comment-comment",
							style: {
								backgroundColor: writtenByUser ? "var(--fg-main-tp)" : "var(--bg-mid)"
							},
							title: commentTitle,
							html: comment.text,
							listeners: {
								beforerender: ({target}) => {
									imgPromises.push(Image.replaceImages(target.el));

									// Enlarge images when clicked in window
									target.el.addEventListener("click", (e) => {
										if (e.target && (e.target as HTMLElement).tagName == "IMG") {
											const img = e.target as HTMLImageElement

											const imageWin = win({
													width: window.innerWidth - 50,
													height: window.innerHeight - 50,
													maximized: false,
													maximizable: true,
													modal: true,
													title: img.alt ?? "Image",
												},
												comp({
													flex: 1,
													itemId: "img-cont",

													style: {
														alignItems: "center",
														justifyContent: "center"
													},
													listeners: {
														beforerender: ({target}) => {
															const i = img.cloneNode() as HTMLImageElement;
															i.style.height = "100%";
															i.style.width = "100%";
															i.style.objectFit = "contain";
															target.el.appendChild(i);
														}
													}
												})
											);

											imageWin.show();

										}
									});
								},
								render: ({target}) => {
									target.el.addEventListener("contextmenu", ev => {
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
														const commentDlg = new CommentDialog();
														void commentDlg.load(comment.id);
														commentDlg.show();
													}
												}
											}),
											btn({
												icon: "delete",
												text: t("Delete"),
												disabled: client.user.isAdmin ? false : !writtenByUser,
												handler: () => {
													if (client.user.isAdmin || writtenByUser) {
														jmapds("Comment").confirmDestroy([comment.id]);
													}
												}
											})
										)

										contextMenu.showAt(ev);
									});
								}
							}
						});

						const labels = comp({
							cls: "hbox"
						});

						comment.labelEntities.forEach((l: any) => {
							labels.items.add(
								comp({
									tagName: "i",
									cls: "icon",
									style: {color: `#${l.color}`, margin: "0"},
									title: l.name,
									html: "label"
								})
							)
						});


						const attachments = comp({
							cls: "vbox"
						});

						comment.attachments.forEach((a: any) => {
							attachments.items.add(
								comp({
										cls: "hbox comment-attachment"
									},
									btn({
										flex: 1,
										icon: "description",
										text: a.name,
										handler: () => {
											window.open("api/download.php?blob=" + a.blobId + "&inline=1", "_blank");
										}
									}),
									btn({
										icon: "download",
										handler: () => {
											client.downloadBlobId(a.blobId, a.name).catch((error) => {
												Notifier.error(error)
											});
										}
									})
								)
							);
						});

						commentComp.items.add(labels);
						commentComp.items.add(attachments);

						const triangle = comp({
							title: commentTitle,
							cls: writtenByUser ? "comment-triangle comment-triangle-right" : "comment-triangle comment-triangle-left"
						})

						const avatarComp = comp({
								cls: "hbox",
								width: 70
							},
							writtenByUser ? triangle : avatarCnt,
							writtenByUser ? avatarCnt : triangle,
						);

						this.scroller.items.add(
							comp({
									cls: "hbox",
									style: {
										marginBottom: "0.8rem",
										padding: "0 0.8rem"
									}
								},
								comp({
									cls: ""
								}),
								writtenByUser ? commentComp : avatarComp,
								writtenByUser ? avatarComp : commentComp,
							)
						);
					});

					this.items.add(this.scroller);

					this.scroller.el.scrollTop = this.scroller.el.scrollHeight;
				}
			},
			queryParams: {
				limit: 0
			},
			relations: {
				creator: {
					path: "createdBy",
					dataSource: jmapds("Principal")
				},
				modifier: {
					path: "modifiedBy",
					dataSource: jmapds("Principal")
				},
				labelEntities: {
					path: "labels",
					dataSource: jmapds("CommentLabel")
				}
			}
		});
	}
}