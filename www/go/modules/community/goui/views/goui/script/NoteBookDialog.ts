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

	constructor() {
		super();

		this.cls = "vbox";
		this.title = t("Note book");
		this.width = 600;
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

					sharepanel({
											
					}),
					

					comp({
						html: 'test',
						title: "Test"
					})
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