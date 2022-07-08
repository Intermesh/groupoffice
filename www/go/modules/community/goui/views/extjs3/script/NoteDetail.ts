import {comp, Component} from "../../../../../../../views/Extjs3/goui/script/component/Component.js";
import {tbar} from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import {btn, Button} from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {Window} from "../../../../../../../views/Extjs3/goui/script/component/Window.js";
import {client} from "../../../../../../../views/Extjs3/goui/script/api/Client.js";
import {Entity} from "../../../../../../../views/Extjs3/goui/script/api/EntityStore.js";
import {Image} from "../../../../../../../views/Extjs3/goui/script/api/Image.js";

declare global {
	var GO: any;
	var go: any;
}

export class NoteDetail extends Component {
	private titleCmp!: Component;
	private editBtn!: Button;
	private entity?: Entity;
	private content: Component;

	constructor() {
		super();

		this.cls = "vbox";
		this.width = 400;

		this.items.add(
			this.createToolbar(),
			this.content = comp({
				cls: "normalize card pad"
			})
		);


		this.on("render", () => {

			const detailView = new go.detail.Panel({
				width: undefined,
				entityStore: go.Db.store("Note"),
				header: false
			});


			detailView.addCustomFields();
			detailView.addLinks();
			detailView.addComments();
			detailView.addFiles();
			detailView.addHistory();

			this.items.add(detailView);
		});



	}

	private createToolbar() {
		return tbar({
				cls: "border-bottom"
			},
			this.titleCmp = comp({tagName: "h3"}),
			'->',
			this.editBtn = btn({
				icon: "edit",
				title: t("Edit")
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

		} catch (e) {
			Window.alert(t("Error"), e + "");
		} finally {
			this.unmask();
		}

		return this;
	}

	private legacyOnLoad() {

		this.items.forEach((item:any) =>  {
			if(item.internalLoad) {
				item.internalLoad(this.entity);
			}
		});

	}
}