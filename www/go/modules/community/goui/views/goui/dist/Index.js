// script/Index.ts
import { modules } from "../../../../../../../views/goui/groupoffice-core/dist/Modules.js";

// script/Notes.ts
import { comp as comp2, Component as Component2 } from "../../../../../../../views/goui/goui/dist/component/Component.js";
import { splitter } from "../../../../../../../views/goui/goui/dist/component/Splitter.js";
import { tbar as tbar3 } from "../../../../../../../views/goui/goui/dist/component/Toolbar.js";
import { btn as btn3 } from "../../../../../../../views/goui/goui/dist/component/Button.js";
import { checkboxselectcolumn, column as column3 } from "../../../../../../../views/goui/goui/dist/component/table/TableColumns.js";

// script/NoteBookGrid.ts
import { Table } from "../../../../../../../views/goui/goui/dist/component/table/Table.js";
import { jmapstore } from "../../../../../../../views/goui/goui/dist/jmap/JmapStore.js";
import { t } from "../../../../../../../views/goui/goui/dist/Translate.js";
import { createComponent } from "../../../../../../../views/goui/goui/dist/component/Component.js";
import { column } from "../../../../../../../views/goui/goui/dist/component/table/TableColumns.js";
var NoteBookGrid = class extends Table {
  constructor() {
    super(jmapstore({
      entity: "NoteBook",
      sort: [{
        property: "name"
      }]
    }), [
      column({
        header: t("Name"),
        property: "name",
        sortable: true
      })
    ]);
  }
};
var notebookgrid = (config) => createComponent(new NoteBookGrid(), config);

// script/NoteBookCombo.ts
import { AutocompleteField } from "../../../../../../../views/goui/goui/dist/component/form/AutocompleteField.js";
import { t as t2 } from "../../../../../../../views/goui/goui/dist/Translate.js";
import { createComponent as createComponent2 } from "../../../../../../../views/goui/goui/dist/component/Component.js";
import { client } from "../../../../../../../views/goui/goui/dist/jmap/Client.js";
var NoteBookCombo = class extends AutocompleteField {
  constructor() {
    super(new NoteBookGrid());
    this.table.headers = false;
    this.label = t2("Notebook");
    this.name = "noteBookId";
    this.valueProperty = "id";
    this.on("autocomplete", async (field, input) => {
      this.table.store.queryParams = { filter: { text: input } };
      await this.table.store.load();
    });
    this.on("setvalue", async (field, newValue, oldValue) => {
      const loadText = async () => {
        if (this.input?.value == this.value) {
          const entityStore = client.store("NoteBook");
          const nb = await entityStore.single(this.value);
          this.setInputValue(nb.name);
        }
      };
      if (this.rendered) {
        await loadText();
      } else {
        this.on("render", () => {
          loadText();
        }, { once: true });
      }
    });
  }
};
var notebookcombo = (config) => createComponent2(new NoteBookCombo(), config);

// script/NoteDialog.ts
import { cardmenu } from "../../../../../../../views/goui/goui/dist/component/CardMenu.js";
import { cards } from "../../../../../../../views/goui/goui/dist/component/CardContainer.js";
import { containerfield } from "../../../../../../../views/goui/goui/dist/component/form/ContainerField.js";
import { form } from "../../../../../../../views/goui/goui/dist/component/form/Form.js";
import { fieldset } from "../../../../../../../views/goui/goui/dist/component/form/Fieldset.js";
import { textfield } from "../../../../../../../views/goui/goui/dist/component/form/TextField.js";
import { tbar } from "../../../../../../../views/goui/goui/dist/component/Toolbar.js";
import { t as t3 } from "../../../../../../../views/goui/goui/dist/Translate.js";
import { root } from "../../../../../../../views/goui/goui/dist/component/Root.js";
import { client as client2 } from "../../../../../../../views/goui/goui/dist/jmap/Client.js";
import { htmlfield } from "../../../../../../../views/goui/goui/dist/component/form/HtmlField.js";
import { EntityStore } from "../../../../../../../views/goui/goui/dist/jmap/EntityStore.js";
import { btn } from "../../../../../../../views/goui/goui/dist/component/Button.js";
import { Window } from "../../../../../../../views/goui/goui/dist/component/Window.js";
import { Notifier } from "../../../../../../../views/goui/goui/dist/Notifier.js";
var NoteDialog = class extends Window {
  form;
  entityStore;
  currentId;
  cards;
  general;
  constructor() {
    super();
    this.entityStore = new EntityStore("Note", client2);
    this.cls = "vbox";
    this.title = t3("Note");
    this.width = 600;
    this.height = 400;
    this.stateId = "note-dialog";
    this.maximizable = true;
    this.items.add(this.form = form({
      cls: "vbox",
      flex: 1,
      handler: async (form2) => {
        try {
          await this.entityStore.save(form2.value, this.currentId);
          this.close();
        } catch (e) {
          Window.alert(t3("Error"), e);
        } finally {
          this.unmask();
        }
      }
    }, cardmenu(), this.cards = cards({ flex: 1 }, this.general = fieldset({ cls: "scroll fit", title: t3("General") }, notebookcombo(), textfield({
      name: "name",
      label: t3("Name"),
      required: true
    }), htmlfield({
      name: "content",
      listeners: {
        insertimage: (htmlfield2, file, img) => {
          root.mask();
          client2.upload(file).then((r) => {
            if (img) {
              img.dataset.blobId = r.blobId;
              img.removeAttribute("id");
            }
            Notifier.success("Uploaded " + file.name + " successfully");
          }).catch((err) => {
            console.error(err);
            Notifier.error("Failed to upload " + file.name);
          }).finally(() => {
            root.unmask();
          });
        }
      }
    }))), tbar({ cls: "border-top" }, "->", btn({
      type: "submit",
      text: t3("Save")
    }))));
    this.addCustomFields();
  }
  async load(id) {
    this.mask();
    try {
      this.form.value = await this.entityStore.single(id);
      this.currentId = id;
    } catch (e) {
      Window.alert(t3("Error"), e + "");
    } finally {
      this.unmask();
    }
    return this;
  }
  addCustomFields() {
    const es = "Note";
    if (go.Entities.get(es).customFields) {
      var fieldsets = go.customfields.CustomFields.getFormFieldSets(es);
      fieldsets.forEach((fs) => {
        fs.cascade((item) => {
          if (item.getName) {
            let fieldName = item.getName().replace("customFields.", "");
            item.name = item.hiddenName = fieldName;
          }
        });
        if (fs.fieldSet.isTab) {
          fs.title = null;
          fs.collapsible = false;
          this.cards.items.add(containerfield({ name: "customFields", cls: "scroll", title: fs.fieldSet.name }, fs));
        } else {
          fs.columnWidth = 1;
          this.general.items.add(containerfield({ name: "customFields" }, fs));
        }
      }, this);
    }
  }
};

// script/NoteGrid.ts
import { Table as Table2 } from "../../../../../../../views/goui/goui/dist/component/table/Table.js";
import { jmapstore as jmapstore2 } from "../../../../../../../views/goui/goui/dist/jmap/JmapStore.js";
import { t as t4 } from "../../../../../../../views/goui/goui/dist/Translate.js";
import { column as column2, datetimecolumn } from "../../../../../../../views/goui/goui/dist/component/table/TableColumns.js";
var NoteGrid = class extends Table2 {
  constructor() {
    super(jmapstore2({
      entity: "Note",
      sort: [{
        property: "name"
      }]
    }), [
      column2({
        header: t4("Name"),
        property: "name",
        sortable: true
      }),
      datetimecolumn({
        header: t4("Created At"),
        property: "createdAt",
        sortable: true
      })
    ]);
    this.on("rowdblclick", async (table, rowIndex, ev) => {
      const dlg = new NoteDialog();
      dlg.show();
      await dlg.load(table.store.get(rowIndex).id);
    });
  }
};

// script/NoteDetail.ts
import { btn as btn2 } from "../../../../../../../views/goui/goui/dist/component/Button.js";
import { tbar as tbar2 } from "../../../../../../../views/goui/goui/dist/component/Toolbar.js";
import { t as t5 } from "../../../../../../../views/goui/goui/dist/Translate.js";
import { client as client3 } from "../../../../../../../views/goui/goui/dist/jmap/Client.js";
import { Window as Window2 } from "../../../../../../../views/goui/goui/dist/component/Window.js";
import { Image } from "../../../../../../../views/goui/goui/dist/jmap/Image.js";
import { comp, Component } from "../../../../../../../views/goui/goui/dist/component/Component.js";
import { FunctionUtil } from "../../../../../../../views/goui/goui/dist/util/FunctionUtil.js";
var NoteDetail = class extends Component {
  titleCmp;
  editBtn;
  entity;
  content;
  scroller;
  detailView;
  toolbar;
  constructor() {
    super();
    this.cls = "vbox";
    this.width = 400;
    this.style.position = "relative";
    this.items.add(this.createToolbar(), this.scroller = comp({ flex: 1, cls: "scroll vbox" }, this.content = comp({
      cls: "normalize goui-card pad"
    })));
    const ro = new ResizeObserver(FunctionUtil.buffer(100, () => {
      this.detailView.doLayout();
    }));
    ro.observe(this.el);
    this.detailView = new go.detail.Panel({
      width: void 0,
      entityStore: go.Db.store("Note"),
      header: false
    });
    this.detailView.addCustomFields();
    this.detailView.addLinks();
    this.detailView.addComments();
    this.detailView.addFiles();
    this.detailView.addHistory();
    this.scroller.items.add(this.detailView);
  }
  createToolbar() {
    return this.toolbar = tbar2({
      disabled: true,
      cls: "border-bottom"
    }, this.titleCmp = comp({ tagName: "h3" }), "->", this.editBtn = btn2({
      icon: "edit",
      title: t5("Edit"),
      handler: (button, ev) => {
        const dlg = new NoteDialog();
        dlg.load(this.entity.id);
        dlg.show();
      }
    }));
  }
  set title(title) {
    super.title = title;
    this.titleCmp.text = title;
  }
  async load(id) {
    this.mask();
    try {
      this.entity = await client3.store("Note").single(id);
      this.title = this.entity.name;
      this.content.items.clear();
      this.content.items.add(Image.replace(this.entity.content));
      this.legacyOnLoad();
      this.toolbar.disabled = false;
    } catch (e) {
      Window2.alert(t5("Error"), e + "");
    } finally {
      this.unmask();
    }
    return this;
  }
  legacyOnLoad() {
    this.detailView.currentId = this.entity.id;
    this.detailView.internalLoad(this.entity);
  }
};

// script/Notes.ts
import { router } from "../../../../../../../views/goui/goui/dist/Router.js";
var Notes = class extends Component2 {
  noteBookGrid;
  noteGrid;
  noteDetail;
  constructor() {
    super();
    this.cls = "hbox fit";
    const west = this.createWest();
    this.noteDetail = new NoteDetail();
    this.items.add(west, splitter({
      stateId: "gouidemo-splitter-west",
      resizeComponentPredicate: west
    }), this.createCenter(), splitter({
      stateId: "gouidemo-splitter-east",
      resizeComponentPredicate: this.noteDetail
    }), this.noteDetail);
    this.on("render", async () => {
      const records = await this.noteBookGrid.store.load();
      this.noteBookGrid.rowSelection.selected = [0];
    });
  }
  createWest() {
    return comp2({
      cls: "vbox",
      width: 300
    }, tbar3({
      cls: "border-bottom"
    }, comp2({
      tagName: "h3",
      text: "Notebooks",
      flex: 1
    }), btn3({
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
        column3({
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
    this.noteGrid.cls = "fit light-bg";
    this.noteGrid.rowSelectionConfig = {
      multiSelect: true,
      listeners: {
        selectionchange: (tableRowSelect) => {
          if (tableRowSelect.selected.length == 1) {
            const table = tableRowSelect.table;
            const record = table.store.get(tableRowSelect.selected[0]);
            router.goto("goui-notes/" + record.id);
          }
        }
      }
    };
    return comp2({
      cls: "vbox",
      flex: 1
    }, tbar3({
      cls: "border-bottom"
    }, "->", btn3({
      cls: "primary",
      icon: "add",
      handler: () => {
        const dlg = new NoteDialog();
        const noteBookId = this.noteBookGrid.store.get(this.noteBookGrid.rowSelection.selected[0]).id;
        dlg.form.setValues({
          noteBookId
        });
        dlg.show();
      }
    })), this.noteGrid);
  }
  showNote(noteId) {
    this.noteDetail.load(noteId);
  }
};

// script/Index.ts
import { router as router2 } from "../../../../../../../views/goui/groupoffice-core/dist/Router.js";
modules.register({
  package: "community",
  name: "goui",
  init() {
    let notes;
    router2.add(/^goui-notes\/(\d+)$/, (noteId) => {
      modules.openMainPanel("goui-notes");
      notes.showNote(parseInt(noteId));
    });
    modules.addMainPanel("goui-notes", "GOUI Notes", () => {
      notes = new Notes();
      return notes;
    });
  }
});
//# sourceMappingURL=Index.js.map
