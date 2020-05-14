go.modules.community.tasks.ChooseTasklistDialog = Ext.extend(Ext.Window, {
	title: t("Choose a tasklist"),
    entityStore: "Task",
    layout: 'fit',
	width: dp(800),
	height: dp(800),

	initComponent: function () {
        this.chooseTasklistGrid = new go.modules.community.tasks.ChooseTasklistGrid();

        this.openFileButton = new Ext.Button({
            iconCls: 'ic-search',
            text: t("Open file"),
            width: dp(40),
            height: dp(30),
            handler: function() {
                if(!this.chooseTasklistGrid.selectedId) {
                    Ext.Msg.show({
                        title:t("Tasklist not selected"),
                        msg: t("You have not selected any tasklist. Select a tasklist before proceeding."),
                        buttons: Ext.Msg.OK,
                        animEl: 'elId',
                        icon: Ext.MessageBox.WARNING
                     });
                } else {
                    go.util.importFile(
                        'Task', 
                        "text/vcalendar,text/csv",
                        { tasklistId: this.chooseTasklistGrid.selectedId },
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
                                percentageComplete: t("percentage completed"),
                                "alerts.remindDate": t("remind date"),
                                "alerts.remindTime": t("remind time"),
                                categories: t("categories")

                            }
                        });
                }
            },
            scope: this
        });

        this.buttons = [this.openFileButton];

		this.items = [
            this.chooseTasklistGrid
        ];

        go.modules.community.tasks.ChooseTasklistDialog.superclass.initComponent.call(this);
	}
});
