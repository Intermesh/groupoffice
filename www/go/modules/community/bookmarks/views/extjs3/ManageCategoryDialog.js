/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ManageCategoryDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */
go.modules.community.bookmarks.ManageCategoryDialog = Ext.extend(Ext.Window,{
    
    maximizable: true,
    layout:'fit',
    resizable: false,
    width: 600,
    height: 400,
    closeAction: 'hide',
    title: t("Administrate categories"),

    initComponent: function() {
        this.categoriesGrid = new go.modules.community.bookmarks.ManageCategoriesGrid();
        this.items = this.categoriesGrid; // grid in window
            
        //initComponent
        go.modules.community.bookmarks.ManageCategoryDialog.superclass.initComponent.call(this);
        this.categoriesGrid.store.load();
        this.addEvents({
            'change':true
        });
    }
});
