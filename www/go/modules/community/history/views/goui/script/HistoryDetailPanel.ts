import {LogEntryGrid} from "./LogEntryGrid.js";
import {BaseEntity, collapsebtn, comp, Component, EntityID, t, tbar} from "@intermesh/goui";

export class HistoryDetailPanel extends Component {
	private readonly grid: LogEntryGrid;
	private readonly type: string;

	constructor(type: string) {
		super();
		this.type = type;

		this.stateId = "history-detail";

		this.grid = new LogEntryGrid();

		this.style.maxHeight = "40rem";
		this.cls = "card vbox";

		this.items.add(

				tbar({},
					comp({
						tagName: "h3",
						text: t("History")
					}),
					"->",
					collapsebtn({target: this.grid})
				),
				comp({
					cls: "scroll",
					flex: 1
					},
					this.grid
				)

		);
	}

	public onLoad(entity: BaseEntity) {
		this.grid.store.setFilter("entity", {
			entity: this.type,
			entityId: entity.id
		});

		void this.grid.store.load();
	}
}