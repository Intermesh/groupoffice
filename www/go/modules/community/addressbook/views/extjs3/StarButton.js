go.modules.community.addressbook.StarButton = Ext.extend(Ext.Button, {
	iconCls: "ic-star-border",
	cls: 'go-addressbook-star',
	contactId: null,
		
	initComponent: function() {
		go.modules.community.addressbook.StarButton.superclass.initComponent.call(this);
		
		//listen for changes in store
		go.Db.store("Contact").on('changes', function(store, added, changed, destroyed) {
			var id = this.getEntityId(), change = changed[id] || added[id];
			
			if(change && "starred" in change) {
				this.setIconClass(change.starred ? 'ic-star' : 'ic-star-border');				
			}
			
			if(destroyed.indexOf(id) > -1) {
				this.setIconClass('ic-star-border');
			}
		}, this);
		
	},
	
	handler: function() {
		//var starred = this.starButton.iconCls == "ic-star", id = this.data.id + "-" + go.User.id;								
		var update = {}, id = this.getEntityId();
		update[id] = {starred: this.isStarred() ? null : true};

		this.setIconClass(this.isStarred() ? 'ic-star' : 'ic-star-border');

		if(go.Db.store("ContactStar").data[id]) {
			go.Db.store("ContactStar").set({update: update});
		} else
		{
			update[id].contactId = this.contactId;
			update[id].userId = go.User.id;
			go.Db.store("ContactStar").set({create: update});
		}

	},
	isStarred : function() {
		return this.iconCls == "ic-star";
	},
	
	getEntityId : function() {
		return this.contactId + "-" + go.User.id;
	},
	
	setContactId : function(id) {
		this.contactId = id;
		
		go.Db.store("Contact").get([this.getEntityId()], function(entities) {
			var starred = entities && entities[0] && entities[0].starred;
			this.setIconClass(starred ? 'ic-star' : 'ic-star-border');
		}, this);
	}
});
