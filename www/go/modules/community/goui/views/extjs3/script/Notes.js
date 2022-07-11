import { btn } from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import { tbar } from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import { comp, Component } from "../../../../../../../views/Extjs3/goui/script/component/Component.js";
import { splitter } from "../../../../../../../views/Extjs3/goui/script/component/Splitter.js";
import { NoteGrid } from "./NoteGrid.js";
import { notebookgrid } from "./NoteBookGrid.js";
import { NoteDetail } from "./NoteDetail.js";
import { checkboxselectcolumn, column } from "../../../../../../../views/Extjs3/goui/script/component/table/TableColumns.js";
import { NoteDialog } from "./NoteDialog.js";
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
        this.on("render", async () => {
            const records = await this.noteBookGrid.store.load();
            this.noteBookGrid.rowSelection.selected = [0];
        });
    }
    createEast() {
        return this.noteDetail = new NoteDetail();
    }
    createWest() {
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
                checkboxselectcolumn(),
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
        }, "->", 
        // textfield({
        // 	label: t("Search"),
        // 	buttons: [
        // 		btn({icon: "clear", handler:(btn) => (btn.parent!.parent! as Field).value = ""})
        // 	]
        // }),
        btn({
            cls: "primary",
            icon: "add",
            handler: () => {
                const dlg = new NoteDialog();
                const noteBookId = this.noteBookGrid.store.get(this.noteBookGrid.rowSelection.selected[0]).id;
                dlg.form.setValues({
                    noteBookId: noteBookId
                });
                dlg.show();
            }
        })), this.noteGrid);
    }
    showRecord(record) {
        // const records: DLRecord = [
        // 	['Number', record.number],
        // 	['Description', record.description],
        // 	['Created At', Format.date(record.createdAt)]
        // ];
        //
        // this.descriptionList.records = records;
        this.noteDetail.load(record.id);
    }
}
export const gouiTest = new Notes();
//# sourceMappingURL=Notes.js.map