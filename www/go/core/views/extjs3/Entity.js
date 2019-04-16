go.Entity = function (cfg) {
	if (cfg) {
		Ext.apply(this, cfg);
	}
	
	if (!this.title) {
		this.title = t(this.name);
	}

	if (!cfg.links) {
		this.links = [];
	}

	this.links.forEach(function (l) {

		l.entity = this.name;

		if (!l.title) {
			l.title = this.title;
		}
		if (!l.iconCls) {
			l.iconCls = "entity " + l.entity;
		}
	}, this);
	
	if(!this.filters) {
		this.filters = [{
				name: 'text',
				type: "string",
				multiple: false,
				title: "Query"
		}];
	}
	
	this.customFields = this.customFields || false;	
};


Ext.apply(go.Entity.prototype, {
	
	module: "",
	package: "",
	
	/**
	 * Supports custom fields or not. Can be true or an object with addtional properties:
	 * 
	 * 	customFields = {
				fieldSetDialog: "go.modules.community.addressbook.CustomFieldSetDialog"
			},
	 */
	customFields : false,
	
	/**
	 * True if this models holds an ACL Id in property aclId
	 */
	isAclOwner : false,
	
	/**
	 * If this is an acl owner this holds the default ACL id that will be copied
	 * when a new one is created. Used by go.defaultpermissions
	 */
	defaultAclId: null,
	/**
	 * 
	 * Array of filter definitions:
	 * [{
	 *	name: 'text',
	 *	type: 'string',
	 *	multiple: true, //can be separated with , in search field. eg. q: foo,bar
	 *	title: 'Query'
	 * }]
	 */
	filters: null,
	links: null,
	title: null,
	
	getRouterPath: function (id) {
		return this.name.toLowerCase() + "/" + id;
	},
	
	goto: function (id) {
		go.Router.goto(this.getRouterPath(id));
	}

});