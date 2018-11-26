go.panels.CreateModifyTpl = Ext.extend(Ext.Panel, {
	entityStore: "User",
	cUserId: null,
	mUserId: null,
	tpl: new Ext.XTemplate('<p class="s6 pad">\
	<label>'+t("Created")+'</label>\
	<span class="avatar" style="{[this.avatar(this.cUser)]}"></span>\
	<span>{[fm.date(values.createdAt)]}{ctime}</span><br>\
	<small>'+t("by")+' <span>{[this.cUser.displayName]}</span></small>\
	</p>\
	<p class="s6">\
	<label>'+t("Modified")+'</label>\
	<span class="avatar" style="{[this.avatar(this.mUser)]}"></span>\
	<span>{[fm.date(values.modifiedAt)]}{mtime}</span><br>\
	<small>'+t("by")+' <span>{[this.mUser.displayName]}</span></small>\
	</p>',{
		avatar: function(user) {
			if(!user) {
				return '';
			}
			return 'background-image: url('+go.Jmap.downloadUrl(user.avatarId)+')';
		},
		cUser: null,
		mUser: null
	}),
		
	update: function(data) {
		this.cUserId = data.createdBy || data.ownedBy || data.user_id;
		this.mUserId = data.modifiedBy || data.muser_id;
		
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
					this.tpl.cUser = e;
				}
			}, this);
			
			go.panels.CreateModifyTpl.superclass.update.call(this, data);
		},this);	
	},
	
	onChanges : function(store, added, changed) {		
		if(added[this.cUserId] || changed[this.cUserId] || added[this.mUserId] || changed[this.mUserId]) {		
			this.update(this.ownerCt.data); 
		}
	}	
});
