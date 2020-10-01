go.detail.CreateModifyPanel = Ext.extend(Ext.Panel, {
	title: t("Info"),
	collapsible: true,
	entityStore: "User",
	cUserId: null,
	mUserId: null,
	tpl: new Ext.XTemplate('<div class="s6 pad"><div class="icons">\
	<p>\
		{[go.util.avatar(this.cUser.displayName,this.cUser.avatarId)]}\
		<span><tpl if="values.createdAt">{[go.util.Format.dateTime(values.createdAt)]}</tpl><tpl if="values.ctime">{ctime}</tpl></span>\
		<label>'+t("Created")+'<label>\
	</p>\
	</div>\
	</div>\
	<div class="s6 pad"><div class="icons">\
	<p>\
		{[go.util.avatar(this.mUser.displayName,this.mUser.avatarId)]}\
		<span><tpl if="values.modifiedAt">{[go.util.Format.dateTime(values.modifiedAt)]}</tpl><tpl if="values.mtime">{mtime}</tpl></span>\
		<label>'+t("Modified")+'<label>\
	</p>\
	</div>\
	</div>',{
		cUser: null,
		mUser: null
	}),
		
	update: function(data) {
		this.cUserId = data.createdBy || data.ownedBy || data.user_id;
		this.mUserId = data.modifiedBy || data.muser_id || this.cUserId;
		var ids = [];
		if(this.cUserId) {
			ids.push(this.cUserId);
		}
		
		if(this.mUserId) {
			ids.push(this.mUserId);	
		}
		this.tpl.cUser = {displayName: ''};
		this.tpl.mUser = {displayName: ''};
		
		this.entityStore.get(ids, function(entities, notFoundIds) {
			entities.forEach(function(e) {
				if(e.id === this.cUserId) {
					this.tpl.cUser = e;
				}
				if(e.id === this.mUserId) {
					this.tpl.mUser = e;
				}
			}, this);
			
			go.detail.CreateModifyPanel.superclass.update.call(this, data);
		},this);	
	},
	
	onChanges : function(store, added, changed) {		
		if(added[this.cUserId] || changed[this.cUserId] || added[this.mUserId] || changed[this.mUserId]) {		
			this.update(this.ownerCt.data); 
		}
	}	
});
