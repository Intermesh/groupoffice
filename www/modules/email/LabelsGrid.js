GO.email.LabelsGrid = Ext.extend(GO.grid.GridPanel, {
    changed: false,

    initComponent: function () {

        Ext.apply(this, {
            standardTbar: true,
            title : t("Labels", "email"),
            store: new GO.data.JsonStore({
                url : GO.url("email/label/store"),
                baseParams : {
                    account_id : 0
                },
				listeners: {
					load: function() { 
						Ext.ComponentMgr.get('email-messages-labels-menu').store.baseParams.account_id = null; //this.store.baseParams.account_id;
					},
					scope:this
				},
                fields : ['id', 'name', 'flag', 'color', 'default'],
                remoteSort : false
            }),
            border: false,
            paging: true,
            view: new Ext.grid.GridView({
                autoFill: true,
                forceFit: true,
                emptyText: t("No items to display")
            }),
            cm: new Ext.grid.ColumnModel({
                defaults: {
                    sortable: true
                },
                columns: [
                    {
                        header: t("Name"),
                        dataIndex: 'name'
                    },
                    {
                        header: t("Color"),
                        dataIndex: 'color',
                        renderer: function (value, metaData, record) {
                            return '<div style="display:inline-block; width:38px; height:14px; background-color:#' + value + '; margin-right:4px;"></div>';
                        }
                    }
                ]
            })
        });

        GO.email.LabelsGrid.superclass.initComponent.call(this);
    },

    setAccountId : function(id){
        this.store.baseParams.account_id=id;
		  var tabPanel = this.findParentByType('tabpanel');
		  if(!id) {
				tabPanel.hideTabStripItem(this);
			} else {
				tabPanel.unhideTabStripItem(this);
			}
        this.setDisabled(!id); 
        this.store.load();       
    },

    dblClick: function (grid, record) {
        this.showLabelDialog(record.id);
    },

    btnAdd: function () {
        this.showLabelDialog();
    },

    showLabelDialog: function (id) {
        if (!this.labelDialog) {
            this.labelDialog = new GO.email.LabelDialog();

            this.labelDialog.on('save', function () {
                this.store.load();
                this.changed = true;
            }, this);
        }

        this.labelDialog.formPanel.baseParams.account_id=this.store.baseParams.account_id;
        this.labelDialog.show(id);
    },

    deleteSelected: function () {
        GO.email.LabelsGrid.superclass.deleteSelected.call(this);
        this.changed = true;
    }
});
