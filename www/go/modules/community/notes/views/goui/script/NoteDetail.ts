import {br, btn, comp, Component, h1, h3, hr, menu, t} from "@intermesh/goui";
import {
	addbutton,
	DetailPanel,
	filesbutton,
	Image,
	jmapds,
	linkbrowserbutton,
	modules
} from "@intermesh/groupoffice-core";
import {NoteDialog} from "./NoteDialog";
import {CommentsPanel} from "@intermesh/community/comments";

export class NoteDetail extends DetailPanel {
	private content: Component;

	constructor() {
		super("Note");

		this.scroller.items.add(
			this.content = comp({
				cls: "normalize card pad"
			})
		);
		this.scroller.items.add(new CommentsPanel(this.entityName));
		this.addCustomFields();


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
							jmapds("Note").confirmDestroy([this.entity!.id]);
						}
					})
				)
			})
		)

		if (modules.isAvailable("legacy", "files")) {
			this.toolbar.items.insert(-1, filesbutton());
		}

		this.on("load", (detailPanel, entity) => {
			this.title = entity.name;

			this.content.items.clear();
			this.content.items.add(h3({
				text: entity.name
			}));
			this.content.items.add(br());
			this.content.items.add(Image.replace(entity.content));
		});
	}
}