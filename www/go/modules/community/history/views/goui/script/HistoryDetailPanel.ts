import {LogEntryGrid} from "./LogEntryGrid.js";
import {collapsebtn, comp, Component, EntityID, t, tbar} from "@intermesh/goui";

export class HistoryDetailPanel extends Component {
	private readonly grid: LogEntryGrid;

	constructor(type: string, entityId: EntityID) {
		super();

		this.stateId = "history-detail";

		this.grid = new LogEntryGrid();

		this.items.add(
			comp({
					cls: "card"
				},
				tbar({},
					comp({
						tagName: "h3",
						text: t("History")
					}),
					"->",
					collapsebtn({collapseEl: this.grid})
				),
				comp({
						cls: "fit scroll"
					},
					this.grid
				)
			)
		);

		this.grid.store.setFilter("entity", {
			entity: type,
			entityId: entityId
		});

		void this.grid.store.load();
	}
}