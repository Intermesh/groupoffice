go.modules.community.pages.SiteTreeLoader = Ext.extend(go.tree.EntityLoader, {

    entityStore: null,
    loading: false,
    siteId: '',

    constructor: function (config) {
	config = config || {};
	go.modules.community.pages.SiteTreeLoader.superclass.constructor.call(this, config);
	this.baseAttrs.uiProvider = null;
    },

    load: function (node, callback, scope) {
	if (this.siteId) {
	    if (this.clearOnLoad) {
		while (node.firstChild) {
		    node.removeChild(node.firstChild);
		}
	    }
	    if (this.doPreload(node)) { // preloaded json children
		this.runCallback(callback, scope || node, [node]);
	    } else if (this.entityStore) {
		this.loading = true;
		if(node.attributes.leaf){
		    var response = {
			argument: {callback: callback, node: node, scope: scope},
			responseData: {}
		    };
		    this.handleResponse(response);
		    this.loading = false;
		}else if (!node.attributes.isPage) {
		    this.requestEntityData(node, callback, scope || node);
		} else if(node.attributes.isPage){
		    this.getHeaders(node, callback, scope || node);
		}
	    }
	}
    },

    convertEntityToNode: function (entityData) {
	return {
	    id: "page-" + entityData.id,
	    entityId: entityData.id || null,
	    data: entityData,
	    entity: this.entityStore.entity,
	    text: entityData.pageName,
	    entitySlug: entityData.slug,
	    secondaryText: this.secondaryTextTpl.apply(entityData),
	    nodeType: 'groupoffice',
	    loader: this,
	    isPage: true,
	    sortOrder: entityData.sortOrder,
	};
    },

    getParams: function (node) {
	var filter = {
	    siteId: this.siteId
	};
	return {
	    filter: filter
	};
    },
    
    getHeaders: function (node, cb, scope){
	var headers = [];
	var params = {pageSlug: node.attributes.entitySlug};
	go.Jmap.request({
	    method: "page/getHeaders",
	    params: params,
	    scope: this,
	    callback: function (options, success, response) {
		if (response.items && response.items.length > 0) {
		    response.items.forEach(function (entity) {
			subHeaders = []
			if (entity.items && entity.items.length > 0) {
			    entity.items.forEach(function (subEntity) {
				subHeaders.push({
				    id: 'header-'+ subEntity.slug,
				    text: subEntity.name,
				    entitySlug: subEntity.slug,
				    nodeType: 'groupoffice',
				    isPage: false,
				    leaf: true
				});
			    });
			}
			header = {
			    id: 'header-'+ entity.slug,
			    text: entity.name,
			    entitySlug: entity.slug,
			    nodeType: 'groupoffice',
			    isPage: false,
			    children: subHeaders
			}
			if(!header.children.length > 0){
			    header.expanded = true;
			}
			headers.push(header);
		    });
		}else{
		    console.warn("Failed to load header treenodes.")
		}
		var headersResponse = {
		    argument: {callback: cb, node: node, scope: scope},
		    responseData: headers
		};
		this.handleResponse(headersResponse);
		this.loading = false;
	    }
	});
    }
});