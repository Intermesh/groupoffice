go.chart.BarChart = Ext.extend(go.chart.ChartComponent, {
	
	initComponent : function(){
	 go.chart.BarChart.superclass.initComponent.call(this);
	},

	/**
	 * @var {Array}
	 */
	labels:null,

	/**
	 * @var {Object}
	 */
	options:null,
	
	update: function(series){

		var data = {
			series: series
		};

		if( this.labels ) {
			data.labels =  this.labels;
		}
		
		if(!this.chart) {
			this.chart = new Chartist.Bar("#" + this.el.id, data, this.options || {});
		} else
		{
			this.chart.update(data);
		}
		
	}
	
});