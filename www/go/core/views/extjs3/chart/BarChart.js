go.chart.BarChart = Ext.extend(go.chart.ChartComponent, {
	
	initComponent : function(){
	 go.chart.BarChart.superclass.initComponent.call(this);
	},

	chartType: "Bar",

	/**
	 * @var {Array}
	 */
	labels:null,

	/**
	 * @var {Object}
	 */
	options:null
	

	
});