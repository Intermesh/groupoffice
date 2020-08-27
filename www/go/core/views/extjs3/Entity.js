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
	 * The enetity that defines the custom fields. Usually null to make this.name.
	 * But sometimes a model extends another model.
	 */
	extends : null,
	
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
	},

	/**
	 * Find a relation by path. Eg. "creator" or "customFields.user"
	 * 
	 * For example returns;
	 * 
	 * {
	 * 		path: "customFields.", //Only with nested relations.
	 * 		store: "User",
	 * 		fk: "userId"
	 * }
	 * 
	 * 
	 * 
	 * @param {string} path 
	 * @return Object
	 */
	findRelation : function(path) {
		parts = path.split("."),last = parts.pop(), current = this.relations;

		parts.forEach(function(p) {
			if(!current[p]) {
				current[p] = {};
			}
			
			current = current[p];
		});

		if(!current[last]) {
			return false;
		}
		current[last].path = parts.length > 0 ? parts.join('.') + "." : "";
		return current[last];
	},

	applyCustomFieldFilters: function() {
		entityName = this.extends || this.name;

		var existingNames = this.filters.column("name"),
			customFieldFilters = go.customfields.CustomFields.getFilters(entityName),
			me = this;

		customFieldFilters = customFieldFilters.filter(function(f) {
			var exists = existingNames.indexOf(f.name) > -1;
			if(exists) {
				console.warn("Custom field name " + f.name+ " can't be filtered as the name conflicts with an existing filter for entity " + me.name);
			}
			return !exists;

		});
		this.filters = this.filters.concat(customFieldFilters);
	}

});