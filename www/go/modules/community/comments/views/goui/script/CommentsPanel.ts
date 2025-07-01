import {
	browser,
	btn, collapsebtn,
	comp,
	Component, DefaultEntity,
	EntityID,
	form,
	t,
	tbar, Toolbar, Window
} from "@intermesh/goui";
import {CommentList} from "./CommentList.js";
import {CommentEditor} from "./CommentEditor.js";
import {client, DetailPanel, jmapds} from "@intermesh/groupoffice-core";
import {LabelDialog} from "./LabelDialog.js";

export class CommentsPanel extends Component {
	private commentList!: CommentList;
	private commentEditor!: CommentEditor;
	private countBadge!: Component;

	private entityId: EntityID | undefined;

	constructor(public entityName: string) {
		super();

		this.cls = "card";

		this.commentList = new CommentList();
		this.commentEditor = new CommentEditor();

		this.countBadge = comp({
			minHeight: 25,
			minWidth: 25,
			hidden: true,
			text: "",
			cls: "count-badge"
		});

		this.items.add(comp({
				cls: "vbox"
			},
			tbar({},
				comp({
						cls: "hbox"
					}, comp({
						tagName: "h3",
						text: t("Comments"),
						flex: 1
					}),
					this.countBadge,
				),
				"->",
				btn({
					hidden: !client.user.isAdmin,
					icon: "settings",
					handler: () => {
						if (client.user.isAdmin) {
							const lblDialog = new LabelDialog();

							lblDialog.store.load();

							lblDialog.show();
						}
					}
				}),
				collapsebtn({
					stateId: "comments-collapser",
					collapseEl: b => b.parent!.nextSibling()!
				}),
			),
			comp({},
				comp({}, this.commentList),
				form({
					flex: 1,
					handler: (form) => {
						if (form.value.text.length > 0) {
							const labelIds = form.value.labels.map((l: { id: number; }) => l.id);

							jmapds("Comment").create(
								Object.assign({
									entity: this.entityName,
									entityId: this.entityId!,
									labels: labelIds,
									text: form.value.text,
									attachments: form.value.attachments
								})
							).then((r) => {
								form.reset();
							}).catch((err) => {
								void Window.error(err);
							})
						}
					}
				},
					this.commentEditor
				),
				tbar({},
					btn({
						icon: "upload",
						text: t("Attach file"),
						handler: async () => {
							const files = await browser.pickLocalFiles(true);
							this.mask();
							const blobs = await client.uploadMultiple((files));
							this.unmask();

							const attachments = this.commentEditor.attachments.value;
							blobs.forEach((blob: any) => {
								attachments.push({
									name: blob.name,
									blobId: blob.id
								})
							})
							this.commentEditor.attachments.value = attachments;
						}
					}),
					"->",
					btn({
						icon: "arrow_circle_up",
						text: t("Scroll to top"),
						handler: () => {
							this.commentList.scroller.el.scrollTop = 0;
						}
					})
				)
			)
		));
	}

	public static addToDetail(detailPanel:DetailPanel) {
		const comments = new CommentsPanel(detailPanel.entityName);
		detailPanel.on("load", ( {entity}) => {
			comments.load(entity.id);
		})
		detailPanel.scroller.items.add(comments);
	}

	public onLoad(entity:DefaultEntity) {
		this.load(entity.id);
	}

	public load(id: EntityID): void {
		this.entityId = id;

		this.commentList.store.queryParams.filter = {
			entityId: id
		}

		this.commentList.store.load().then(() => {
			this.countBadge.text = this.commentList.store.count().toString();
		});
		this.commentEditor.store.load();
	}
}