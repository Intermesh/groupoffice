go.modules.community.bookmarks.BookmarkCombo = Ext.extend(go.form.ComboBox, {
    initComponent: function() {
        
        go.modules.community.bookmarks.BookmarkCombo.superclass.initComponent.call(this);
        this.store.on("load",function() { 
            if(this.getValue() > 0)
                return;
            
            var record = this.store.getAt(0);
            if(record) {
                this.setValue(record.id);
            }
        },this);
    },
	fieldLabel: t("Category"),
	hiddenName: 'categoryId',
	anchor: '100%',
	emptyText: t("Please select..."),
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
    allowBlank: false,
    mode: 'local',
	store: {
		xtype: "gostore",
		fields: ['id', {name: 'creator', type: "relation"}, 'aclId', "name"],
        entityStore: "BookmarksCategory",
        autoLoad: true,
		baseParams: {
			filter: {
					permissionLevel: go.permissionLevels.write
            },
            limit: 0
        }
	}
});