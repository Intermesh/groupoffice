go.chart.ChartComponent = Ext.extend(Ext.BoxComponent, {
	
	/**
		* @cfg {String} backgroundColor
		* The background color of the chart. Defaults to <tt>'#ffffff'</tt>.
		*/
//	backgroundColor: '#ffffff',
	cls:'chart-container',
	
	initComponent : function(){
	 go.chart.ChartComponent.superclass.initComponent.call(this);

	 this.addEvents(
			 'initialize'
	 );
	},

//	onRender : function(){
//		go.chart.ChartComponent.superclass.onRender.apply(this, arguments);
//
////		var params = {
////			bgcolor: this.backgroundColor
////		};
//		this.dom = Ext.getDom(this.id);
//		this.el = Ext.get(this.dom);
//	},
	
	getId : function(){
		return this.id || (this.id = "gochartcmp" + (++Ext.Component.AUTO_ID));
	}
	
});