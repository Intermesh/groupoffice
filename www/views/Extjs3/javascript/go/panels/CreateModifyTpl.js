go.panels.CreateModifyTpl = Ext.extend(Ext.Panel, {
	cUserId: null,
	mUserId: null,
	tpl: new Ext.XTemplate('<p class="s6 pad">\
	<label>'+t("Created")+'</label>\
	<span class="avatar" style="{[this.avatar(this.cUser)]}"></span>\
	<span>{[fm.date(values.createdAt)]}{ctime}</span><br>\
	<small>'+t("by")+' <span>{[values.username || this.cUser.displayName]}</span></small>\
	</p>\
	<p class="s6">\
	<label>'+t("Modified")+'</label>\
	<span class="avatar" style="{[this.avatar(this.mUser)]}"></span>\
	<span>{[fm.date(values.modifiedAt)]}{mtime}</span><br>\
	<small>'+t("by")+' <span>{[values.musername || this.mUser.displayName]}</span></small>\
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
	initComponent: function () {
		go.panels.CreateModifyTpl.superclass.initComponent.call(this, arguments);		
		
		this.on("afterrender", function() {
			go.Stores.get('User').on('changes', this.onChanges, this);
			
		}, this);
		
		this.on("destroy", function() {
			go.Stores.get('User').un('changes', this.onChanges, this);
		}, this);

	},
	
	update: function(data) {
		this.cUserId = data.createdBy || data.ownedBy || data.user_id;
		this.mUserId = data.modifiedBy || data.muser_id;
		go.Stores.get('User').get([this.cUserId,this.mUserId],function(entities) {
			this.tpl.cUser = entities[0] || {displayName: ''};
			this.tpl.mUser = entities[1] || {displayName: ''};
			go.panels.CreateModifyTpl.superclass.update.call(this, data);
		},this);	
	},
	
	onChanges : function(store, added, changed) {
		var ids = added.concat(changed),
			 needToRender = false;
		for(var i = 0, id; id = ids[i]; i++) {
			if(id == this.cUserId || id == this.mUserId) {
				needToRender = true;
			}
		}
		if(needToRender) {
			go.panels.CreateModifyTpl.superclass.update.call(this, this.ownerCt.data);
			//this.update(this.ownerCt.data); 
		}
	}
	
});
