import {br, btn, comp, Component, h1, h3, hr, menu, t} from "@intermesh/goui";
import {
	addbutton,
	DetailPanel,
	Image,
	jmapds,
	linkbrowserbutton,
} from "@intermesh/groupoffice-core";
import {CommentDialog} from "./CommentDialog";

export class CommentDetail extends DetailPanel {
	private content: Component;

	constructor() {
		super("Comment");

		this.scroller.items.add(
			this.content = comp({
				cls: "normalize card pad"
			})
		);

		this.addLinks();

		this.toolbar.items.add(
			btn({
				icon: "edit",
				title: t("Edit"),
				handler: (button, ev) => {
					const dlg = new CommentDialog();
					void dlg.load(this.entity!.id);
					dlg.show();
				}
			}),
			addbutton(),
			linkbrowserbutton(),
			btn({
				icon: "more_vert",
				menu: menu({},
					btn({
						icon: "print",
						text: t("Print"),
						handler: () => {
							this.print();
						}
					}),
					hr(),
					btn({
						icon: "delete",
						text: "Delete",
						handler: () => {
							jmapds("Comment").confirmDestroy([this.entity!.id]);
						}
					})
				)
			})
		)

		this.on("load", ( {entity}) => {

			this.content.items.replace(Image.replace(entity.text));
		});
	}
}