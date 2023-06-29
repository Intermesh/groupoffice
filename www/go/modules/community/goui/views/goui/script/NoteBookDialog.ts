import {
	btn,
	CardContainer,
	cardmenu,
	cards,
	comp,
	datasourceform,
	DataSourceForm,
	EntityID,
	fieldset,
	t,
	tbar,
	textfield,
	Window
} from "@intermesh/goui";
import {jmapds, sharepanel, SharePanel} from "@intermesh/groupoffice-core";

export class NoteBookDialog extends Window {
	readonly form: DataSourceForm;

	private currentId?: EntityID;
	private cards: CardContainer;
	private readonly sharePanel: SharePanel;

	constructor() {
		super();

		this.cls = "vbox";
		this.title = t("Note book");
		this.width = 500;
		this.height = 400;
		this.stateId = "note-book-dialog";
		// this.maximizable = true;

		this.items.add(
			this.form = datasourceform(
				{
					dataSource: jmapds("NoteBook"),
					cls: "vbox",
					flex: 1,
					listeners: {
						submit: ()=> {
							this.close();
						},
						load: (form1, data) => {
							this.sharePanel.setEntity("NoteBook", data.id);
							this.sharePanel.load();
						}
					}
				},
			

				cardmenu(),

				this.cards = cards({flex: 1},

					fieldset({
							cls: "scroll fit",
							title: t("General")
						},

						comp({cls: "hbox gap"},
							textfield({
								flex: 2,
								name: "name",
								label: t("Name"),
								required: true
							})
						)
					),

					this.sharePanel = sharepanel({
						cls: "fit"
					}),

				),

				tbar({cls: "border-top"},
					"->",
					btn({
						type: "submit",
						text: t("Save")
					})
				)
			)
		)

	}

	public async load(id: EntityID) {

		this.mask();

		try {
			await this.form.load(id);
			this.currentId = id;
		} catch (e) {
			void Window.alert(t("Error"), e + "");
		} finally {
			this.unmask();
		}

		return this;
	}
}