import {
	arrayfield,
	ArrayField,
	btn,
	Button, comp,
	Component,
	ContainerField,
	containerfield,
	datasourcestore,
	DataSourceStore, DisplayField,
	displayfield, EntityID,
	hr,
	HtmlField,
	htmlfield,
	menu,
	Notifier,
	root,
	t
} from "@intermesh/goui";
import {client, HtmlFieldMentionPlugin, Image, principalDS} from "@intermesh/groupoffice-core";
import {commentLabelDS} from "./Index.js";

export class CommentEditor extends Component {
	public readonly labels;
	public readonly attachments;
	public readonly store;

	public readonly addBtn;
	public readonly editor;


	constructor(submitButton = true) {
		super();

		this.title = t("Comment");

		this.cls = "vbox";

		this.on("render",  () => {
			void this.store.load();
		})

		this.items.add(
			this.editor = htmlfield({
				flex: 1,
				name: "text",
				required: true,
				listeners: {
					beforerender: ev => {
						new HtmlFieldMentionPlugin(ev.target, async (text) => {
							const r = await principalDS.query({
								filter: {
									entity: "User",
									text: text
								},
								limit: 10
							});
							const get = await principalDS.get(r.ids);
							return get.list.map(p => {
								return {value: p.description!, display: p.description + " (" + p.name + ")"}
							});
						}, 6);

						ev.target.getToolbar().items.insert(6, hr());
					},

					setvalue: ({target}) => {
						void Image.replaceImages(target.el);
					},

					attach: async ({file}) => {
						this.mask();
						const blob = await client.upload(file);
						this.unmask();

						const attachments = this.attachments.value;
						attachments.push({
							name: blob.name,
							blobId: blob.id
						})
						this.attachments.value = attachments;
					},

					insertimage: ({file, img}) => {
						root.mask();

						client.upload(file).then(r => {
							if (img) {
								img.dataset.blobId = r.id;
								img.removeAttribute("id");
							}
							Notifier.success("Uploaded " + file.name + " successfully");
						}).catch((err) => {
							console.error(err);
							Notifier.error("Failed to upload " + file.name);
						}).finally(() => {
							root.unmask();
						});
					}
				}
			})
			,

			this.attachments = arrayfield({
				// style: {padding: "0 1.2rem"},
				itemContainerCls: "",
				name: "attachments",
				buildField: (v) => {
					return containerfield({
							cls: "hbox comment-editor-attachment"
						},
						displayfield({
							htmlEncode: false,
							flex: 1,
							value: `<i class="icon">description</i> ${v!.name.htmlEncode()}`,
						}),
						btn({
							icon: "delete",
							handler: (button) => {
								button.findAncestorByType(ContainerField)!.remove()
							}
						})
					)
				}
			}),

			this.labels = arrayfield<EntityID>({
				itemContainerCls: "",
				name: "labels",
				buildField: (v) => {

					return	displayfield({
						tagName: "div",
						cls: "comment-editor-label",
						htmlEncode: false,
						flex: 1,
						renderer: (value, record) => {

							return commentLabelDS.single(value).then(lbl => {
								return comp({
									cls: "hbox fit",

								},
									comp({
										html: `<i class="icon" style="color: #${lbl.color}">label</i> ${lbl.name.htmlEncode()}`,
										flex: 1
									}),
									btn({
							icon: "delete",
										handler: (button) => {
											button.findAncestorByType(DisplayField)!.remove()
										}
									}))

								});
						}
					});
				}
			})
		);


		this.editor.getToolbar().items.insert(0,
			this.addBtn = btn({
				disabled: true,
				icon: "label",
				title: t("Add labels"),
				menu: menu({}),
				tabIndex: -1, // Skip toolbar in tabbing through forms
			}),
			hr()
			)


		this.editor.getToolbar().items.insert(0,
			this.addBtn = btn({
				disabled: true,
				icon: "label",
				title: t("Add labels"),
				menu: menu({}),
				tabIndex: -1, // Skip toolbar in tabbing through forms
			}),
			hr()
			)

		this.store = datasourcestore({
			dataSource: commentLabelDS,
			listeners: {
				load: ({records}) => {
					if (records.length > 0) {
						this.addBtn.disabled = false;

						let labelButtons: string | Component | Button[] = [];

						records.forEach((label) => {
							labelButtons.push(
								btn({
									icon: "label",
									style: {color: "#" + label.color},
									text: label.name,
									handler: () => {
										if (this.labels.value.some(l => l === label.id)) {
											return
										}

										const labels = this.labels.value;

										labels.push(label.id);

										this.labels.value = labels;
									}
								})
							)
						});

						this.addBtn.menu = menu({}, ...labelButtons);
					}
				}
			}
		})
	}
}
