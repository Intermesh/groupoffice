go.chart.LineChart = Ext.extend(go.chart.ChartComponent, {
	
	initComponent : function(){
	 go.chart.LineChart.superclass.initComponent.call(this);
	},

	labels:[],
	options:{},
	
	update: function(data){
		
		console.log(this);
		
		var elSelector = "#"+this.el.id;
		new Chartist.Line(elSelector, {
			labels: this.labels,
			series: data
		}, this.options);
		
		
	}
	
});