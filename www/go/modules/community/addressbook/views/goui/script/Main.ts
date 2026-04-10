import {filterpanel, MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {btn, checkbox, comp, Component, h3, radio, searchbtn, t, tbar} from "@intermesh/goui";
import {AddressBookGrid, addressbookgrid} from "./AddressBookGrid.js";

export class Main extends MainThreeColumnPanel {
	private addressBookGrid!: AddressBookGrid;

	constructor() {
		super("addressbook");

		this.on("render", async () => {
			void this.addressBookGrid.store.load();
		});
	}

	protected createWest(): Component {
		return comp({
				cls: "scroll",
				width: 300
			},
			tbar({},
				'->',
				this.showCenterButton()
			),
			// todo color on RadioOption?
			radio({
				flex: 1,
				type: "list",
				options: [
					{value: "all", text: t("All contacts"), icon: "select_all"},
					{value: "starred", text: t("Starred"), icon: "star"},
					{value: "orgs", text: t("Organization"), icon: "business"},
					{value: "contacts", text: t("Contacts"), icon: "person"}
				],
				listeners: {
					change: () => {
						// todo
					}
				}
			}),
			tbar({},
				checkbox({
					listeners: {
						change: ({newValue}) => {
							const rs = this.addressBookGrid.rowSelection!;
							newValue ? rs.selectAll() : rs.clear();
						}
					}
				}),
				h3(t("Address books")),
				'->',
				searchbtn({
					listeners: {
						input: ({text}) => {
							this.addressBookGrid.store.setFilter("search", {text});
							void this.addressBookGrid.store.load();
						}
					}
				}),
				btn({
					icon: "add",
					handler: () => {
						// todo
					}
				})
			),
			this.addressBookGrid = addressbookgrid({
				flex: 1,
				cls: "no-row-lines",
				rowSelectionConfig: {
					multiSelect: true,
					listeners: {
						selectionchange: () => {
							// todo
						}
					}
				}
			}),
			filterpanel({
				flex: 1,
				// store: , todo
				entityName: "Contact"
			})
		);
	}

	protected createCenter(): Component {
		return comp({});
	}

	protected createEast(): Component {
		return comp({});
	}
}