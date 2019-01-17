go.modules.community.pages.SiteTreeEdit = Ext.extend(Ext.grid.GridPanel, {
    //using Ext.grid instead of go.grid since go.grid causes infinite scrolling 
    //in this panel for some reason and it has no required benefits over Ext.grid
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
	this.plugins = [new go.grid.plugin.Sortable(this.onSort, this, this.isDropAllowed)];
	this.store.setDefaultSort('sortOrder', 'ASC');
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
	    autoExpandColumn: genId + '-pageName',
//	    stateful: true,
//	    stateId: genId+'-page-grid'
	});
	this.on('render', function () {
	    this.store.entityStore.on("changes", function () {
		this.store.baseParams = {filter: {'siteId': this.siteId}};
		this.store.reload;
	    }, this, {single: true});
	}, this);
	go.modules.community.pages.SiteTreeEdit.superclass.initComponent.call(this);

    },

    onSort: function (sortable, selections, dragData, dd) {
    },

    isDropAllowed: function (selections, overRecord) {
	
//	console.log(overRecord)
//	console.log(selections.includes(overRecord))
	if (overRecord && selections.includes(overRecord)) {
//	    debugger;
	    var res = false;
	    var storeItems = overRecord.store.data.items;
	    selections.forEach(
		    function (record) {
//			console.log(overRecord.store.data.items[overRecord.store.data.length - 1].id)
//			console.log(record)
//			if (overRecord.store.data.items[overRecord.store.data.length - 1].id === record.id) {
			index = storeItems.indexOf(record)
//			console.log(index);
//			console.log(storeItems[index+1])
			if(index && !storeItems[index+1]){
//			    console.log(overRecord.store.data.items[overRecord.store.data.length - 1].id === record.id);
//			    console.log('_')
			    res = true;
			    return;
			}
		    })
		if (res){
//		console.log('last item + duplicate')
		return false;
	    }
	}else if(!overRecord){
	    console.log('no record')
	    return false;
	}
//	console.log('---')
	return true;
    },

});