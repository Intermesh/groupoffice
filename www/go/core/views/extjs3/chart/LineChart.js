go.chart.LineChart = Ext.extend(go.chart.ChartComponent, {
	
	initComponent : function(){
	 go.chart.LineChart.superclass.initComponent.call(this);
	},

	chartType: "line",

	/**
	 * @var {Array}
	 */
	labels:null,

	/**
	 * @var {Object}
	 */
	options:null
	

	
});