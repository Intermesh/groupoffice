import {
	checkbox, CheckboxField,
	comp,
	Component, daterangefield, Filter,
	h3,
	hr, List,
	list, p,
	searchbtn,
	splitter,
	store,
	t,
	tbar
} from "@intermesh/goui";
import {modules, principalcombo} from "@intermesh/groupoffice-core";
import {LogEntryGrid} from "./LogEntryGrid.js";
import {TypeGrid} from "./TypeGrid.js";

export class Main extends Component {
	private west: Component;
	private center: Component;

	private logEntryGrid!: LogEntryGrid;
	private typeGrid!: TypeGrid;

	private selectedActions!: String[];

	constructor() {
		super();

		this.cls = "hbox fit";

		this.logEntryGrid = new LogEntryGrid();

		void this.logEntryGrid.store.load();

		this.items.add(
			this.west = this.createWest(),
			splitter({
				resizeComponentPredicate: "west"
			}),
			this.center = this.createCenter()
		)
	}

	private createWest() {
		this.typeGrid = new TypeGrid(this.logEntryGrid.store);
		void this.typeGrid.load();

		this.selectedActions = [];

		return comp({
				itemId: "west",
				cls: "pad bg-low scroll ",
				width: 320
			},
			comp({
					cls: "vbox"
				},
				daterangefield({
					label: t("Date"),
					listeners: {
						change: (field, newValue, oldValue) => {
							void this.logEntryGrid.store.setFilter("createdAt", {createdAt: newValue}).load();
						}
					}
				}),
				p(),
				principalcombo({
					entity: "user",
					label: t("Users"),
					placeholder: t("All users"),
					required: false,
					listeners: {
						select: (field, record) => {
							this.logEntryGrid.store.setFilter("user", {createdBy: field.value});
							void this.logEntryGrid.store.load();
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
				renderer: (v, el, list: List) => {
					return [comp({}, checkbox({
							label: v.label,
							listeners: {
								change: (field, newValue, oldValue) => {
									const record = list.store.find((i) => i.label == field.label);

									if (newValue) {
										this.selectedActions.push(record!.id);
									} else {
										this.selectedActions = this.selectedActions.filter(action => action !== record!.id);
									}

									this.logEntryGrid.store.setFilter("actions", {actions: this.selectedActions});

									void this.logEntryGrid.store.load();
								}
							}
						})
					)];
				},
				rowSelectionConfig: {
					multiSelect: true
				}
			}),
			hr(),
			h3({
				text: t("Types")
			}),
			this.typeGrid
		)
	}

	private createCenter() {
		return comp({
				itemId: "center",
				cls: "vbox bg-low",
				flex: 1
			},
			tbar({
					cls: "border-bottom"
				},
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