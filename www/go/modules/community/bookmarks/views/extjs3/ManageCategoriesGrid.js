/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ManageCategoriesGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Richard
 */

go.modules.community.bookmarks.ManageCategoriesGrid = Ext.extend(go.grid.GridPanel,{
    
    changed : false,
    layout: 'fit',
    autoScroll: true,
    split: true,
    border: false,	
    paging: true,

    initComponent: function() {
        this.store = new go.data.Store({
            fields: ['id', {name: 'creator', type: "relation"}, 'aclId', "name"],
            entityStore: "BookmarksCategory"
        });
    
        this.cm =  new Ext.grid.ColumnModel({
            defaults:{
                sortable:true
            },
            columns:[
            {
                header: t("Name"), 
                dataIndex: 'name'
            },{
                header: t("Owner"), 
                dataIndex: 'creator',
                renderer: function (v) {
                    return v ? v.displayName : "-";
                },
                sortable: false
            }		
            ]
        });
        
        this.view = new Ext.grid.GridView({
            autoFill: true,
            forceFit: true,
            emptyText: t("No items to display")		
        });
        
        this.sm = new Ext.grid.RowSelectionModel();
        this.loadMask=true;
        
        this.tbar=[{
            iconCls: 'ic-add',
            text: t("Add"),
            handler: function(){			
                this.categoryDialog = new go.modules.community.bookmarks.CategoryDialog();	
                this.categoryDialog.show();
            },
            scope: this
        },{
            iconCls: 'ic-delete',
            text: t("Delete"),
            handler: function(){
                this.deleteSelected();
                this.changed=true;
            },
            scope: this
        }];
        
        // initComponent
        go.modules.community.bookmarks.ManageCategoriesGrid.superclass.initComponent.call(this);
        
        this.on('rowdblclick', function(grid, rowIndex){
            var record = grid.getStore().getAt(rowIndex);
            this.edit(record.data.id);
        }, this);
    },
    edit: function(id) {
		this.categoryDialog = new go.modules.community.bookmarks.CategoryDialog();
		this.categoryDialog.load(id).show();
	}
});
