import {comp, Component} from "../../../../../../../views/Extjs3/goui/script/component/Component.js";
import {tbar, Toolbar} from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import {btn, Button} from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {Window} from "../../../../../../../views/Extjs3/goui/script/component/Window.js";
import {client} from "../../../../../../../views/Extjs3/goui/script/api/Client.js";
import {Entity} from "../../../../../../../views/Extjs3/goui/script/api/EntityStore.js";
import {Image} from "../../../../../../../views/Extjs3/goui/script/api/Image.js";
import {NoteDialog} from "./NoteDialog.js";


export class NoteDetail extends Component {
	private titleCmp!: Component;
	private editBtn!: Button;
	private entity?: Entity;
	private content: Component;
	private scroller: Component;
	private detailView: any;
	private toolbar!: Toolbar;

	constructor() {
		super();

		this.cls = "vbox";
		this.width = 400;

		this.items.add(
			this.createToolbar(),
			this.scroller = comp({flex: 1, cls: "scroll vbox"},
				this.content = comp({
					cls: "normalize card pad"
				})
			)
		);

		// Legacy stuff
		this.detailView = new go.detail.Panel({
			width: undefined,
			entityStore: go.Db.store("Note"),
			header: false
		});
		this.detailView.addCustomFields();
		this.detailView.addLinks();
		this.detailView.addComments();
		this.detailView.addFiles();
		this.detailView.addHistory();

		this.scroller.items.add(this.detailView);
	}

	private createToolbar() {
		return this.toolbar = tbar({
				disabled: true,
				cls: "border-bottom"
			},
			this.titleCmp = comp({tagName: "h3"}),
			'->',
			this.editBtn = btn({
				icon: "edit",
				title: t("Edit"),
				handler: (button, ev) => {
					const dlg = new NoteDialog();
					dlg.load(this.entity!.id);
					dlg.show();
				}
			})
		);
	}

	set title(title: string) {
		super.title = title;
		this.titleCmp.text = title;
	}

	public async load(id: number) {

		this.mask();

		try {
			this.entity = await client.store("Note").single(id);

			this.title = this.entity.name;

			this.content.items.clear();
			this.content.items.add(Image.replace(this.entity.content));

			this.legacyOnLoad();

			this.toolbar.disabled = false;

		} catch (e) {
			Window.alert(t("Error"), e + "");
		} finally {
			this.unmask();
		}

		return this;
	}

	private legacyOnLoad() {
		this.detailView.currentId = this.entity!.id;
		this.detailView.internalLoad(this.entity);
	}
}