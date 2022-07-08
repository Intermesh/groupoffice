import { btn } from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import { Notifier } from "../../../../../../../views/Extjs3/goui/script/Notifier.js";
import { tbar } from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import { column } from "../../../../../../../views/Extjs3/goui/script/component/Table.js";
import { comp, Component } from "../../../../../../../views/Extjs3/goui/script/component/Component.js";
import { splitter } from "../../../../../../../views/Extjs3/goui/script/component/Splitter.js";
import { dl } from "../../../../../../../views/Extjs3/goui/script/component/DescriptionList.js";
import { Format } from "../../../../../../../views/Extjs3/goui/script/util/Format.js";
import { NoteGrid } from "./NoteGrid.js";
import { notebookgrid } from "./NoteBookGrid.js";
;
class Notes extends Component {
    constructor() {
        super();
        this.cls = "hbox fit";
        const center = this.createCenter(), west = this.createWest(), east = this.createEast();
        this.items.add(west, splitter({
            stateId: "gouidemo-splitter-west",
            resizeComponentPredicate: west
        }), center, splitter({
            stateId: "gouidemo-splitter-east",
            resizeComponentPredicate: east
        }), east);
        this.on("render", () => {
            this.noteBookGrid.store.load();
        });
    }
    createEast() {
        this.descriptionList = dl({
            cls: "pad"
        });
        return comp({
            cls: "fit vbox",
            width: 300
        }, tbar({}, comp({
            flex: 1,
            tagName: "h3",
            text: "Detail"
        }), btn({
            icon: "edit"
        })), this.descriptionList);
    }
    createWest() {
        const records = [];
        for (let i = 1; i <= 20; i++) {
            records.push({
                id: i,
                name: "Test " + i,
                selected: i == 1
            });
        }
        return comp({
            cls: "vbox",
            width: 300
        }, tbar({
            cls: "border-bottom"
        }, comp({
            tagName: "h3",
            text: "Notebooks",
            flex: 1
        }), btn({
            icon: "add",
            handler: () => {
            }
        })), this.noteBookGrid = notebookgrid({
            flex: 1,
            cls: "fit no-row-lines",
            rowSelectionConfig: {
                multiSelect: true,
                listeners: {
                    selectionchange: (tableRowSelect) => {
                        const noteBookIds = tableRowSelect.selected.map((index) => tableRowSelect.table.store.get(index).id);
                        this.noteGrid.store.queryParams.filter = {
                            noteBookId: noteBookIds
                        };
                        this.noteGrid.store.load();
                    }
                }
            },
            columns: [
                // checkboxcolumn({
                // 	property: "selected"
                // }),
                column({
                    header: "Name",
                    property: "name",
                    sortable: true,
                    resizable: false
                })
            ]
        }));
    }
    createCenter() {
        this.noteGrid = new NoteGrid();
        this.noteGrid.flex = 1;
        this.noteGrid.title = "Notes";
        this.noteGrid.cls = "fit";
        this.noteGrid.rowSelectionConfig = {
            multiSelect: true,
            listeners: {
                selectionchange: (tableRowSelect) => {
                    if (tableRowSelect.selected.length == 1) {
                        const table = tableRowSelect.table;
                        const record = table.store.get(tableRowSelect.selected[0]);
                        this.showRecord(record);
                    }
                }
            }
        };
        return comp({
            cls: "vbox",
            flex: 1
        }, tbar({
            cls: "border-bottom"
        }, btn({
            text: "Test GOUI!",
            handler: () => {
                Notifier.success("Hurray! GOUI has made it's way into Extjs 3.4 :)");
            }
        }), btn({
            text: "Open files",
            handler: () => {
                // window.GO.mainLayout.openModule("files");
                window.GO.files.openFolder();
            }
        })), this.noteGrid);
    }
    showRecord(record) {
        const records = [
            ['Number', record.number],
            ['Description', record.description],
            ['Created At', Format.date(record.createdAt)]
        ];
        this.descriptionList.records = records;
    }
}
export const gouiTest = new Notes();
//# sourceMappingURL=GouiTest.js.map