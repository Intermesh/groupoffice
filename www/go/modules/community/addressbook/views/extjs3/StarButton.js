go.modules.community.addressbook.StarButton = Ext.extend(Ext.Button, {
	iconCls: "ic-star-border",
	cls: 'go-addressbook-star',
	contactId: null,
		
	initComponent: function() {
		go.modules.community.addressbook.StarButton.superclass.initComponent.call(this);
		
		//listen for changes in store
		go.Stores.get("Contact").on('changes', function(store, added, changed, destroyed) {
			if(added.concat(changed).indexOf(this.getEntityId()) > -1) {
				go.Stores.get("Contact").get([this.getEntityId()], function(contacts){
					this.setIconClass(contacts[0].starred ? 'ic-star' : 'ic-star-border');
				}, this);
			}
			
			if(destroyed.indexOf(this.getEntityId()) > -1) {
				this.setIconClass('ic-star-border');
			}
		}, this);
		
	},
	
	handler: function() {
		//var starred = this.starButton.iconCls == "ic-star", id = this.data.id + "-" + go.User.id;								
		var update = {}, id = this.getEntityId();
		update[id] = {starred: this.isStarred() ? null : true};

		this.setIconClass(this.isStarred() ? 'ic-star' : 'ic-star-border');

		if(go.Stores.get("ContactStar").data[id]) {
			go.Stores.get("ContactStar").set({update: update});
		} else
		{
			update[id].contactId = this.contactId;
			update[id].userId = go.User.id;
			go.Stores.get("ContactStar").set({create: update});
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
		
		go.Stores.get("Contact").get([this.getEntityId()], function(entities) {
			var starred = entities && entities[0] && entities[0].starred;
			this.setIconClass(starred ? 'ic-star' : 'ic-star-border');
		}, this);
	}
});
