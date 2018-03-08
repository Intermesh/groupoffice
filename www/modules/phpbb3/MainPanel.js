GO.moduleManager.addModule('phpbb3', GO.panel.IFrameComponent, {
	title : GO.phpbb3.lang.forum,
	iconCls : 'go-tab-icon-forum',
	url:GO.url('phpbb3/bridge/redirect'),
	reloadOnShow:true,
	border:false,
	id:'phpbb3'
});
