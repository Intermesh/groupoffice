import {MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {btn, comp, menu, searchbtn, t, tbar} from "@intermesh/goui";
import {TasklistGrid} from "./TasklistGrid.js";
import {CategoryGrid} from "./CategoryGrid.js";
import {TaskGrid} from "./TaskGrid.js";

export class Main extends MainThreeColumnPanel {
	private taskGrid!: TaskGrid;

	constructor() {
		super("tasks");
	}

	protected createWest() {
		const tasklistGrid = new TasklistGrid();
		void tasklistGrid.store.load();

		const categoryGrid = new CategoryGrid();
		void categoryGrid.store.load();

		return comp({
				cls: "vbox",
				width: 300
			},
			tbar({}, this.showCenterButton()),
			tasklistGrid,
			comp({cls: "pad"}),
			categoryGrid
		);
	}

	protected createCenter() {
		this.taskGrid = new TaskGrid();
		this.taskGrid.fitParent = true;
		void this.taskGrid.store.load();

		return comp({
				cls: "vbox bg-lowest",
				flex: 1
			},
			tbar({
				cls: "bg-mid border-bottom"
				},
				this.showWestButton(),
				"->",
				searchbtn({
					listeners: {
						input: (sender,text) => {

						}
					}
				}),
				btn({
					cls: "primary filled",
					icon: "add",
					handler: () => {

					}
				}),
				btn({
					icon: "more_vert",
					menu: menu({},
						btn({
							icon: "cloud_upload",
							text: t("Import"),
							handler: () => {

							}
						}),
						btn({
							icon: "cloud_download",
							text: t("Export")
						}),
						btn({
							icon: "delete",
							text: t("Delete"),
							handler: () => {

							}
						})
					)
				})
			),
			comp({cls: "scroll", flex: 1},
			this.taskGrid
			)
		);
	}

	protected createEast() {
		return comp();
	}
}