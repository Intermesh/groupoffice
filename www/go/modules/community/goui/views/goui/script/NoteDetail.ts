import {NoteDialog} from "./NoteDialog.js";
import {
	btn,
	Button,
	comp,
	Component,
	DefaultEntity,
	FunctionUtil,
	t,
	tbar,
	Toolbar,
	Window
} from "@intermesh/goui";
import {Image, jmapds} from "@intermesh/groupoffice-core";


export class NoteDetail extends Component {
	private titleCmp!: Component;
	private editBtn!: Button;
	private entity?: DefaultEntity;
	private content: Component;
	private scroller: Component;
	private detailView: any;
	private toolbar!: Toolbar;

	constructor() {
		super();

		this.cls = "vbox";
		this.width = 400;

		this.style.position = "relative";

		this.items.add(
			this.createToolbar(),
			this.scroller = comp({flex: 1, cls: "scroll vbox"},
				this.content = comp({
					cls: "normalize goui-card pad"
				})
			)
		);

		// Legacy stuff

		const ro = new ResizeObserver(FunctionUtil.buffer(100,() =>{
			this.detailView.doLayout();
		}));

		ro.observe(this.el);

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
					void dlg.load(this.entity!.id);
					dlg.show();
				}
			})
		);
	}

	set title(title: string) {
		super.title = title;
		this.titleCmp.text = title;
	}

	public async load(id: string) {

		this.mask();

		try {
			this.entity = await jmapds("Note").single(id);

			if(!this.entity) {
				throw "notfound";
			}

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