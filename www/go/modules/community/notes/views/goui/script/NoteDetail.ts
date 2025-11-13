import {br, btn, Button, comp, Component, datasourceform, DataSourceForm, h3, hr, menu, t} from "@intermesh/goui";
import {
	AclLevel,
	addbutton,
	customFields,
	DetailFieldset,
	DetailPanel,
	filesbutton,
	Image,
	linkbrowsebutton, LinkDetail,
	modules
} from "@intermesh/groupoffice-core";
import {NoteDialog} from "./NoteDialog";
import {CommentsPanel} from "@intermesh/community/comments";
import {Note, noteDS} from "./Index";
import {HistoryDetailPanel} from "@intermesh/community/history";

export class NoteDetail extends DetailPanel<Note> {
	private content: Component;
	private editBtn: Button;
	private deleteBtn: Button;
	private form: DataSourceForm;

	constructor() {
		super("Note");

		this.scroller.items.add(
			this.content = comp({
				cls: "normalize card pad"
			})
		);

		this.scroller.items.add(this.form = datasourceform({dataSource: noteDS}, ...customFields.getFieldSets("Note").map(fs => new DetailFieldset(fs))))

		this.scroller.items.add(new CommentsPanel(this.entityName));

		this.addFiles();
		// this.addLinks();

		this.scroller.items.add(...LinkDetail.getAll());

		this.scroller.items.add(new HistoryDetailPanel(this.entityName));

		this.toolbar.items.add(
			this.editBtn = btn({
				icon: "edit",
				title: t("Edit"),
				handler: () => {
					const dlg = new NoteDialog();
					void dlg.load(this.entity!.id);
					dlg.show();
				}
			}),
			addbutton(),
			linkbrowsebutton(),
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
					this.deleteBtn = btn({
						icon: "delete",
						text: "Delete",
						handler: () => {
							void noteDS.confirmDestroy([this.entity!.id]);
						}
					})
				)
			})
		)

		if (modules.isAvailable("legacy", "files")) {
			this.toolbar.items.insert(-1, filesbutton());
		}

		this.on("load", ({entity}) => {
			this.title = entity.name;

			this.deleteBtn.disabled = entity.permissionLevel < AclLevel.DELETE;
			this.editBtn.disabled = entity.permissionLevel < AclLevel.WRITE;

			this.content.items.clear();
			this.content.items.add(h3({
				text: entity.name
			}));
			this.content.items.add(br());
			this.content.items.add(Image.replace(entity.content));

			void this.form.load(entity.id);

		});
	}
}