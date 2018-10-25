go.panels.CreateModifyTpl = Ext.extend(Ext.Panel, {
	tpl: new Ext.XTemplate('<p class="s6 pad">\
	<label>'+t("Created")+'</label>\
	<span class="avatar" style="{[this.avatar(values.createdBy || values.ownedBy || values.user_id)]}"></span>\
	<span>{[fm.date(values.createdAt)]}{ctime}</span><br>\
	<small>'+t("by")+' <a href="#">{[values.username || this.user(values.createdBy || values.ownedBy).displayName]}</a></small>\
	</p>\
	<p class="s6">\
	<label>'+t("Modified")+'</label>\
	<span class="avatar" style="{[this.avatar(values.modifiedBy || values.muser_id)]}"></span>\
	<span>{[fm.date(values.modifiedAt)]}{mtime}</span><br>\
	<small>'+t("by")+' <a href="#">{[values.musername || this.user(values.modifiedBy).displayName]}</a></small>\
	</p>',{
		avatar: function(id) {
			if(!id) {
				return '';
			}
			var user = this.user(id);
			if(!user[0]) {
				return '';
			}
			console.log('avatar',user[0]);
			return 'background-image: url('+go.Jmap.downloadUrl(user[0].avatarId)+')';
		},
		user : function(id) {
			if(!id) {
				return {avatarId:''};
			}
			return go.Stores.get('User').get([id]);
		}
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
	
	onChanges : function() { 
		this.update(this.ownerCt.data); 
	}
	
});
