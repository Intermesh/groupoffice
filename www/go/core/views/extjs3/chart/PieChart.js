go.chart.PieChart = Ext.extend(go.chart.ChartComponent, {

	update: function(data){

		if(!this.chart) {
			this.chart = new Chartist.Pie("#" + this.getEl().id, data, this.options || {});
		} else
		{
			this.chart.update(data);
		}

	}

});