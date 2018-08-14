go.panels.CreateModifyTpl = Ext.extend(Ext.Panel, {
	tpl: new Ext.XTemplate('<p class="s6 pad">\
	<label>'+t("Created")+'</label>\
	<span class="avatar" style="{[this.avatar(values.createdBy || values.ownedBy)]}""></span>\
	<span>{[fm.date(values.createdAt)]}{ctime}</span><br>\
	<small>'+t("by")+' <a href="#">{[this.user(values.createdBy || values.ownedBy).displayName]}</a></small>\
	</p>\
	<p class="s6">\
	<label>'+t("Modified")+'</label>\
	<span class="avatar" style="{[this.avatar(values.modifiedBy)]}"></span>\
	<span>{[fm.date(values.modifiedAt)]}{mtime}</span><br>\
	<small>'+t("by")+' <a href="#">{[this.user(values.modifiedBy).displayName]}</a></small>\
	</p>',{
		avatar: function(userId) {
			return 'background-image: url('+go.Jmap.downloadUrl(this.user(userId).avatarId)+')';
		},
		user : function(userId) {
			return go.Stores.get('User').get(userId);
		}
	}),
	initComponent: function () {
		go.panels.CreateModifyTpl.superclass.initComponent.call(this, arguments);		

		go.Stores.get('User').on('changes', function() { this.update(this.ownerCt.data); }, this);

	}
	
});
