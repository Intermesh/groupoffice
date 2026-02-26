import {
	arrayfield, browser, btn,
	ContainerField,
	containerfield,
	datetimefield,
	displayfield,
	fieldset,
	Format,
	htmlfield,
	t
} from "@intermesh/goui";
import {client, FormWindow} from "@intermesh/groupoffice-core";
import {CommentEditor} from "./CommentEditor.js";

export class CommentDialog extends FormWindow {
	private commentEditor: CommentEditor;
	constructor() {
		super("Comment");

		this.title = t("Comment");

		this.stateId = "comment-dialog";
		this.maximizable = true;
		this.resizable = true;

		this.width = 600;
		this.height = 400;

		this.generalTab.items.add(fieldset({cls: "vbox gap fit"},
			datetimefield({
				name: "date",
				label: t("Date"),
				withTime: true,
				required: true
			}),
			Object.assign(this.commentEditor = new CommentEditor(), {flex: 1})
		));

		this.bbar.items.insert(0,
			btn({
				icon: "attach_file",
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
			})
		)
	}
}