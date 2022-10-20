go.chart.ChartComponent = Ext.extend(Ext.BoxComponent, {
	
	/**
		* @cfg {String} backgroundColor
		* The background color of the chart. Defaults to <tt>'#ffffff'</tt>.
		*/
	cls:'chart-container',

	chartType: "Line",

	update: function(series, labels){

		var data = {
			series: series
		};

		if( this.labels && !labels) {
			data.labels =  this.labels;
		}

		if(labels) {
			data.labels = labels;
		}

		if(!this.chart) {
			this.chart = new Chartist[this.chartType]("#" + this.el.id, data, this.options || {});
		} else
		{
			this.chart.update(data);
		}

	}
	
});