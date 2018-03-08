GO.mainLayout.onReady(function(){
	 Ext.Msg.confirm(GO.demodata.lang.addDemoData, GO.demodata.lang.confirm, function(btn){
		if(btn=='yes'){
			document.location=GO.url('demodata/demodata/create');
		}else
		{
			GO.request({
				url:'modules/module/delete',
				params:{
					id:'demodata'
				}
			})
		}
	 });
});