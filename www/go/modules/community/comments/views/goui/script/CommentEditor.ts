import {
	arrayfield,
	ArrayField,
	btn, Button,
	Component, ContainerField, containerfield, datasourcestore, DataSourceStore, displayfield,
	fieldset, Format,
	htmlfield, menu,
	Notifier,
	root,
	t
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";

export class CommentEditor extends Component {
	public labels: ArrayField;
	public attachments: ArrayField;
	public store: DataSourceStore;

	public addBtn!: Button;

	constructor() {
		super();

		this.title = t("Comment");

		this.items.add(
			fieldset({
					cls: "group"
				},
				this.addBtn = btn({
					disabled: true,
					icon: "add",
					title: t("Add labels"),
					menu: menu({})
				}),
				htmlfield({
					flex: 1,
					name: "text",
					required: true,
					listeners: {
						insertimage: (htmlfield, file, img) => {
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
				}),
				btn({
					icon: "send",
					type: "submit"
				})
			),

			this.attachments = arrayfield({
				style: {padding: "0 1.2rem"},
				cls: "vbox",
				name: "attachments",
				buildField: (v) => {
					return containerfield({
							cls: "hbox comment-editor-attachment"
						},
						displayfield({
							escapeValue: false,
							flex: 1,
							value: `<i class="icon">description</i> ${Format.escapeHTML(v!.name)}`,
						}),
						btn({
							icon: "delete",
							handler: (button, ev) => {
								button.findAncestorByType(ContainerField)!.remove()
							}
						})
					)
				}
			}),

			this.labels = arrayfield({
				style: {padding: "0 1.2rem"},
				cls: "hbox flow",
				name: "labels",
				buildField: (v) => {
					return containerfield({
							cls: "hbox comment-editor-label"
						},
						displayfield({
							escapeValue: false,
							flex: 1,
							value: `<i class="icon" style="color: #${v!.color}">label</i> ${v!.name}`,
							style: {color: `#${v!.color}`}
						}),
						btn({
							icon: "cancel",
							handler: (button, ev) => {
								button.findAncestorByType(ContainerField)!.remove()
							}
						})
					)
				}
			})
		);

		this.store = datasourcestore({
			dataSource: jmapds("CommentLabel"),
			listeners: {
				load: (store, labels) => {
					if (labels) {
						this.addBtn.disabled = false;

						let labelButtons: string | Component | Button[] = [];

						labels.forEach((label) => {
							labelButtons.push(
								btn({
									icon: "label",
									style: {color: "#" + label.color},
									text: label.name,
									handler: () => {
										if (this.labels.value.some(l => l.id === label.id)) {
											return
										}

										const labels = this.labels.value;

										labels.push(label);

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