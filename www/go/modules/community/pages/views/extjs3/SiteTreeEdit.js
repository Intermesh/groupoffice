go.modules.community.pages.SiteTreeEdit = Ext.extend(go.grid.GridPanel, {
    siteId: '',
    hideHeaders: true,
    enableDragDrop: true,
    ddText: '',
    title: t('Reorder'),
    sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
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


	Ext.apply(this, {

	    columns: [
		{
		    id: 'id',
		    hidden: true,
		    sortable: false,
		    dataIndex: 'id'
		},
		{
		    id: 'pageName',
		    width: dp(75),
		    sortable: false,
		    dataIndex: 'pageName'
		},
		{
		    id: 'sortOrder',
		    hidden: true,
		    sortable: true,
		    dataIndex: 'sortOrder'
		}

	    ],

	    viewConfig: {
		emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
		headersDisabled: true,
	    },

	    collapsible: false,
	    autoExpandColumn: 'pageName',
	    stateful: true,
	    stateId: 'page-grid'
	});
	this.on('render', function () {
	    this.store.entityStore.on("changes", function () {
		this.store.baseParams = {filter: {'siteId': this.siteId}};
		this.store.reload;
	    }, this);
	}, this);
	this.store.on('load', function () {
	    this.getSelectionModel().selectFirstRow();
	}, this);
	go.modules.community.pages.SiteTreeEdit.superclass.initComponent.call(this);
    }

})