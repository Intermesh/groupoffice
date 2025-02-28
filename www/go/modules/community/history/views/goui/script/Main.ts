import {
	checkbox,
	comp,
	Component,
	h3,
	hr,
	list,
	searchbtn,
	splitter,
	store,
	t,
	tbar
} from "@intermesh/goui";
import {principalcombo} from "@intermesh/groupoffice-core";
import {LogEntryGrid} from "./LogEntryGrid.js";

export class Main extends Component {
	private west: Component;
	private center: Component;

	private logEntryGrid!: LogEntryGrid;

	constructor() {
		super();

		this.cls = "hbox fit";

		this.items.add(
			this.west = this.createWest(),
			splitter({
				resizeComponentPredicate: "west"
			}),
			this.center = this.createCenter()
		)
	}

	private createWest() {
		return comp({
				itemId: "west",
				cls: "pad bg-low scroll ",
				width: 300
			},
			comp({
					cls: "vbox"
				},
				principalcombo({
					entity: "user",
					label: t("Users"),
					placeholder: t("All users"),
					required: false,
					listeners: {
						select: () => {

						}
					}
				})
			),
			hr(),
			h3({
				text: t("Actions")
			}),
			list({
				store: store({
					data: [
						{id: "create", label: t("Create")},
						{id: "update", label: t("Update")},
						{id: "delete", label: t("Delete")},
						{id: "login", label: t("Login")},
						{id: "logout", label: t("Logout")},
						{id: "badlogin", label: t("Bad login")},
						{id: "download", label: t("Download")},
						{id: "email", label: t("E-mail")},
					]
				}),
				renderer: (v) => {
					return [comp({}, checkbox({label: v.label}))]
				},
				rowSelectionConfig: {
					multiSelect: true,
					listeners: {
						selectionchange: (tableRowSelect) => {
							const optionsIds = tableRowSelect.getSelected().map((row) => row.record.id);

							if (optionsIds[0]) {

							}
						}
					}
				}
			}),
			hr(),
			h3({
				text: t("Types")
			}),
		)
	}

	private createCenter() {
		this.logEntryGrid = new LogEntryGrid();

		void this.logEntryGrid.store.load();

		return comp({
				itemId: "center",
				cls: "vbox bg-low",
				flex: 1
			},
			tbar({
					cls: "border-bottom"
				},
				// this.showWestButton(),
				"->",
				searchbtn({
					listeners: {
						input: (sender, text) => {
							this.logEntryGrid.store.setFilter("search", {text});
							void this.logEntryGrid.store.load();
						}
					}
				})
			),
			comp({
					cls: "scroll bg-lowest",
					flex: 1
				},
				this.logEntryGrid
			)
		)
	}
}