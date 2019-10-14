go.chart.LineChart = Ext.extend(go.chart.ChartComponent, {
	
	initComponent : function(){
	 go.chart.LineChart.superclass.initComponent.call(this);
	},

	labels:[],
	options:{},
	
	update: function(series){
		var data = {
			labels: this.labels,
			series: series
		};
		
		if(!this.chart) {
			this.chart = new Chartist.Line("#" + this.el.id, data, this.options);
		} else
		{
			this.chart.update(data);
		}
		
	}
	
});