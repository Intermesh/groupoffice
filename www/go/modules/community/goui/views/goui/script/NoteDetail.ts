import {NoteDialog} from "./NoteDialog.js";
import {btn, comp, Component, t} from "@intermesh/goui";
import {DetailPanel, Image} from "@intermesh/groupoffice-core";


export class NoteDetail extends DetailPanel {
	private content: Component;

	constructor() {
		super("Note");

		this.scroller.items.add(
			this.content = comp({
				cls: "normalize card pad"
			})
		)

		this.addCustomFields();
		this.addComments();
		this.addFiles();
		this.addLinks();
		this.addHistory();

		this.toolbar.items.add(
			btn({
				icon: "edit",
				title: t("Edit"),
				handler: (button, ev) => {
					const dlg = new NoteDialog();
					void dlg.load(this.entity!.id);
					dlg.show();
				}
			})
		)

		this.on("load",(detailPanel, entity) => {
			this.title = entity.name;

			this.content.items.clear();
			this.content.items.add(Image.replace(entity.content));
		});
	}
}