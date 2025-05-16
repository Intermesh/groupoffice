import {
	arrayfield, btn,
	ContainerField,
	containerfield,
	datefield,
	displayfield,
	fieldset,
	Format,
	htmlfield,
	t
} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class CommentDialog extends FormWindow {
	constructor() {
		super("Comment");

		this.title = t("Comment");

		this.stateId = "comment-dialog";
		this.maximizable = true;
		this.resizable = true;

		this.width = 600;
		this.height = 400;

		this.form.on("beforesetvalue", (f, v) => {
			console.log(v);
		})

		this.generalTab.items.add(fieldset({},
			datefield({
				name: "date",
				label: t("Date"),
				withTime: true,
				required: true
			}),
			htmlfield({
				name: "text",
				label: t("Text"),
				required: true,
			}),
			arrayfield({
				name: "attachments",
				buildField: (v) => {
					return containerfield({
							cls: "hbox"
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
			})
		));
	}
}