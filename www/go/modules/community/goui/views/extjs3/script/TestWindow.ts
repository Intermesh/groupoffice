import {Window} from "../../../../../../../views/Extjs3/goui/script/component/Window.js";
import {column, datecolumn, table, Table} from "../../../../../../../views/Extjs3/goui/script/component/Table.js";
import {store, StoreRecord} from "../../../../../../../views/Extjs3/goui/script/data/Store.js";
import {form, Form} from "../../../../../../../views/Extjs3/goui/script/component/form/Form.js";
import {fieldset} from "../../../../../../../views/Extjs3/goui/script/component/form/Fieldset.js";
import {textfield, TextField} from "../../../../../../../views/Extjs3/goui/script/component/form/TextField.js";
import {htmlfield} from "../../../../../../../views/Extjs3/goui/script/component/form/HtmlField.js";
import {ContainerField} from "../../../../../../../views/Extjs3/goui/script/component/form/ContainerField.js";
import {cards} from "../../../../../../../views/Extjs3/goui/script/component/CardContainer.js";
import {cardmenu} from "../../../../../../../views/Extjs3/goui/script/component/CardMenu.js";
import {tbar} from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import {btn, Button} from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import {datefield} from "../../../../../../../views/Extjs3/goui/script/component/form/DateField.js";
import {Menu} from "../../../../../../../views/Extjs3/goui/script/component/menu/Menu.js";
import {DateTime} from "../../../../../../../views/Extjs3/goui/script/util/DateTime.js";

export class TestWindow extends Window {
	stateId = "goui-window"
	modal = false
	title = "Window test"
	width = 800
	height = 600

	focus(o?: FocusOptions) {
		//focus card panel, card panel will focus active item
		this.getItems().get(1).focus(o);
	}

	protected init() {
		super.init();

		this.getHeader().getItems().insert(-1, this.createHeaderMenu())

		const records: StoreRecord[] = [];

		for (let i = 1; i <= 20; i++) {
			records.push({
				number: i,
				description: "Test " + i,
				createdAt: (new DateTime()).addDays(Math.ceil(Math.random() * -365)).format("c")
			});
		}

		const tbl = table({
			title: "Table",
			store: store({
				records: records,
				sort: [{property: "number", isAscending: true}]
			}),
			cls: "fit",
			columns: [
				column({
					header: "Number",
					property: "number",
					sortable: true,
					resizable: true,
					width: 200
				}),
				column({
					header: "Description",
					property: "description",
					sortable: true,
					resizable: true,
					width: 300
				}),
				datecolumn({
					header: "Created At",
					property: "createdAt",
					sortable: true
				})
			]
		});

		const f = form({
				title: "Form",
				cls: "scroll fit",
				handler: (form) => {

					console.log(form.getValues());

					const sub = form.findField("sub") as ContainerField;
					const test1 = sub.findField("test1") as TextField;
					test1.setInvalid("Hey something went wrong!");
				}
			},
			fieldset({},
				textfield({
					label: "Required field",
					// placeholder: "Here's the placeholder",
					name: "test",
					required: true,
					hint: "Please fill in something awesome"
				}),

				datefield({
					label: "Date",
					name: "date"

				}),
				htmlfield({
					label: "Html",
					hint: "Attach files by dropping or pasting them",
					// cls: "frame-hint"
				})
			)
		)

		// const cards = CardContainer.create({
		// 	flex: 1,
		// 	items: [form, table]
		// })

		this.getItems().add(
			cardmenu(),

			cards({flex: 1},
				f, tbl
			),

			tbar({
					cls: "bottom"
				},

				btn({
					html: "Close",
					handler: () => {
						this.close();
					}
				}),

				'->',
				btn({
					cls: "primary",
					html: "Save",
					handler: () => {
						f.submit();
					}
				})
			)
		);


	}

	private createHeaderMenu() {
		const items = [];
		for (let i = 0; i < 10; i++) {
			items.push(Button.create({
				html: "Button " + i
			}))
		}
		return btn({
			text: "Menu",
			menu: Menu.create({
				expandLeft: true,
				items: items
			})
		});
	}

}