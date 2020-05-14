go.form.HtmlEditor.emojiPlugin = {
	init: function(cmp){
		cmp.on('render', function() {
			cmp.getToolbar().addButton(new Ext.Button({
				tooltip: t('Emoji'),
				overflowText: t('Emoji'),
				iconCls: 'ic-mood',
				menu: {
					xtype:'emojimenu',
					handler: function(menu,emoji) {
						cmp.insertAtCursor(emoji);
					},
					scope: this
				}
			}));
		}, this);
	}
};
