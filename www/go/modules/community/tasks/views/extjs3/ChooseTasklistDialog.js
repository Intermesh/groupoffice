
go.modules.community.tasks.ChooseTasklistDialog = Ext.extend(go.Window, {
	title: t("Choose a tasklist"),
    entityStore: "Task",
    layout: 'form',
	width: dp(800),
	height: dp(800),
    modal: true,

	initComponent: function () {
        this.chooseTasklistGrid = new go.modules.community.tasks.ChooseTasklistGrid({
            height: dp(700)
        });

        this.taskListFromCsvCB = new Ext.form.Checkbox({
            xtype: 'xcheckbox',
            boxLabel: t('Import task list ID from CSV file'),
            handler: (cb,checked) => {
                const el = this.chooseTasklistGrid.getEl();
                if(checked) {
                    el.mask()
                } else {
                    el.unmask();
                }
            }
        });

        this.openFileButton = new Ext.Button({
            iconCls: 'ic-search',
            text: t("Open file"),
            width: dp(40),
            height: dp(30),
            handler: function() {
                if(!this.chooseTasklistGrid.selectedId && !this.taskListFromCsvCB.checked) {
                    Ext.Msg.show({
                        title:t("Tasklist not selected"),
                        msg: t("You have not selected any tasklist. Select a tasklist before proceeding."),
                        buttons: Ext.Msg.OK,
                        animEl: 'elId',
                        icon: Ext.MessageBox.WARNING
                     });
                } else {
                    let TLvalues = {};
                    if(!this.taskListFromCsvCB.checked) {
                        TLvalues = {tasklistId: this.chooseTasklistGrid.selectedId};
                    }
                    go.util.importFile(
                        'Task', 
                        ".ics,.csv",
                        // { tasklistId: this.chooseTasklistGrid.selectedId },
                        TLvalues,
                        {},
                        {
                            labels: {
                                start: t("start"),
                                due: t("due"),
                                completed: t("completed"),
                                title: t("title"),
                                description: t("description"),
                                status: t("status"),
                                priority: t("priority"),
                                percentComplete: t("percentage completed"),
                                categories: t("categories")

                            }
                        });
                }
            },
            scope: this
        });

        const propertiesPanel = new Ext.Panel({
            hideMode : 'offsets',
            //title : t("Properties"),
            labelAlign: 'top',
            layout : 'form',
            autoScroll : true,
            items : [{
                xtype: "container",
                layout: "form",
                defaults: {
                    anchor: '100%'
                },
                labelWidth: 16,
                items: [
                    this.taskListFromCsvCB,
                    this.chooseTasklistGrid
                ]
            }]
        });

        this.buttons = [this.openFileButton];

		this.items = [
            // this.taskListFromCsvCB,
            // this.chooseTasklistGrid
            propertiesPanel
        ];

        go.modules.community.tasks.ChooseTasklistDialog.superclass.initComponent.call(this);
	}
});
