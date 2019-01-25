go.modules.community.pages.SiteTreeEdit = Ext.extend(Ext.grid.GridPanel, {
    //using Ext.grid instead of go.grid since go.grid causes infinite scrolling 
    //in this panel and it has no required benefits over Ext.grid
    siteId: '',
    hideHeaders: true,
    enableDragDrop: true,
    ddText: '',
    title: t('Reorder'),
    initComponent: function () {
	this.store = new go.data.Store({
	    baseParams: {filter: {'siteId': this.siteId}},
	    fields: [
		'id',
		'pageName',
		'sortOrder',
		{name: 'createdAt', type: 'date'},
		{name: 'modifiedAt', type: 'date'},
		{name: 'creator', type: go.data.types.User, key: 'createdBy'},
		{name: 'modifier', type: go.data.types.User, key: 'modifiedBy'},
		'permissionLevel'
	    ],
	    entityStore: go.Stores.get("Page")
	});
	//this is the plugin that handles drag and drop.
	this.plugins = [new go.grid.plugin.Sortable(this.onSort, this, this.isDropAllowed)];
	this.store.setDefaultSort('sortOrder', 'ASC');
	//ensures the grid items have a unique id.
	var genId = Ext.id()
	Ext.apply(this, {

	    columns: [
		{
		    id: genId + '-id',
		    hidden: true,
		    sortable: false,
		    dataIndex: 'id'
		},
		{
		    id: genId + '-pageName',
		    width: dp(75),
		    sortable: false,
		    dataIndex: 'pageName'
		},
		{
		    id: genId + '-sortOrder',
		    hidden: true,
		    sortable: true,
		    dataIndex: 'sortOrder',
		    xtype: 'numbercolumn'
		}

	    ],

	    viewConfig: {
		emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
		headersDisabled: true,
	    },

	    collapsible: false,
	    autoExpandColumn: genId + '-pageName'
	});
	this.on('render', function () {
	    this.store.entityStore.on("changes", function () {
		this.store.baseParams = {filter: {'siteId': this.siteId}};
		this.store.reload;
	    }, this, {single: true});
	}, this);
	go.modules.community.pages.SiteTreeEdit.superclass.initComponent.call(this);

    },
    //this function is called after dropping an item
    onSort: function (sortable, selections, dragData, dd) {
    },

    //This function is called during d&d when moving an item
    isDropAllowed: function (selections, overRecord) {

	if (overRecord && selections.includes(overRecord)) {
	    var res = false;
	    var storeItems = overRecord.store.data.items;
	    selections.forEach(
		    function (record) {
			index = storeItems.indexOf(record)
			if (index && !storeItems[index + 1]) {
			    res = true;
			    return;
			}
		    })
	    if (res) {
		return false;
	    }
	} else if (!overRecord) {
	    console.warn('no record')
	    return false;
	}
	return true;
    },

});