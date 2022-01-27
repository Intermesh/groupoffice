/* global go, Ext */

go.modules.community.addressbook.TreeLoader = Ext.extend(go.tree.EntityLoader, {

	entityStore: "AddressBook",

	pageSize: 20,
	
	secondaryTextTpl: '<button class="icon">more_vert</button>',
	
	constructor : function(config) {
		
		config = config || {};
		
		go.modules.community.addressbook.TreeLoader.superclass.constructor.call(this, config);
		
		this.baseAttrs.iconCls = 'ic-import-contacts';
		
		this.groupLoader = new go.tree.EntityLoader({
			entityStore: "AddressBookGroup",
			secondaryTextTpl: Ext.isDefined(config.secondaryTextTpl) ? config.secondaryTextTpl : '<button class="icon">more_vert</button>',
			baseAttrs: {			
				nodeType: "groupoffice",
				iconCls: 'ic-group',
				children: [],
				expanded: true
			},			
			getParams: function(node) {
				return {sort: [{property: "name", isAscending: true }], filter: {addressBookId: node.attributes.data.id}};
			}
		});
	},

	position: 0,

	getParams: function(node) {
		return Ext.apply({limit: this.pageSize, position: this.position, calculateHasMore: true, sort: [{property: "name", isAscending: true }]}, this.baseParams);
	},



	handleResponse : function(r) {

		if(this.position == 0 && !this.getFilter("tbsearch")) {
			r.responseData.unshift({
				leaf: true,
				iconCls: "ic-star",
				text: t("Starred", "addressbook", "community"),
				id: "starred"
			});

			r.responseData.unshift({
				leaf: true,
				iconCls: "ic-select-all",
				text: t("All contacts", "addressbook", "community"),
				id: "all"
			});
		}
		go.modules.community.addressbook.TreeLoader.superclass.handleResponse.call(this, r);	
		
	},
	
	convertEntityToNode : function(entity) {
		
		var attr = go.modules.community.addressbook.TreeLoader.superclass.convertEntityToNode.call(this, entity);
		attr.loader = this.groupLoader;
		if(entity.groups.length === 0) {
			attr.children = [];
			attr.expanded = true;
		}
		return attr;
	}

});
