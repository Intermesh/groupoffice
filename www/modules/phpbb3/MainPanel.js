GO.moduleManager.addModule('phpbb3', GO.panel.IFrameComponent, {
	title : t("Forum", "phpbb3"),
	iconCls : 'go-tab-icon-forum',
	url:GO.url('phpbb3/bridge/redirect'),
	reloadOnShow:true,
	border:false,
	id:'phpbb3'
});
