import {
	browser,
	btn, collapsebtn,
	comp,
	Component, ComponentEventMap, DefaultEntity,
	EntityID,
	form, section,
	t,
	tbar, Toolbar, Window
} from "@intermesh/goui";
import {CommentList} from "./CommentList.js";
import {CommentEditor} from "./CommentEditor.js";
import {client, DetailPanel} from "@intermesh/groupoffice-core";
import {LabelDialog} from "./LabelDialog.js";
import {commentDS} from "./Index.js";

export class CommentsPanel extends Component {
	private readonly commentList!: CommentList;
	private readonly commentEditor!: CommentEditor;
	private readonly countBadge!: Component;

	private entityId: EntityID | undefined;

	private _title!:string
	private readonly titleCmp: Component;
	set title(title: string) {
		if(this.titleCmp) {
			this.titleCmp.html = title;
		}
		this._title = title;
	}

	get title() {
		return this._title;
	}

	set section(section: string|undefined) {
		this.commentList.store.setFilter("section", {section: section})
	}

	get section() {
		return this.commentList.store.getFilter("section")?.section
	}
	constructor(public entityName: string) {
		super();

		this.cls = "card";

		this.title = t("Comments");

		this.disabled = true;

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
					}, this.titleCmp = comp({
						tagName: "h3",
						text: this.title,
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

							return commentDS.create(
								Object.assign({
									entity: this.entityName,
									entityId: this.entityId!,
									labels: labelIds,
									text: form.value.text,
									attachments: form.value.attachments,
									section: this.section
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

	public async load(id: EntityID) {
		this.entityId = id;


		this.commentList.store.setFilter("entity", {
			entity:	this.entityName,
			entityId: id
		});

		await this.commentList.store.load();
		this.countBadge.text = this.commentList.store.count().toString();
		this.disabled = false;
	}
}