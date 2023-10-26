go.chart.ChartComponent = Ext.extend(Ext.BoxComponent, {
	
	/**
		* @cfg {String} backgroundColor
		* The background color of the chart. Defaults to <tt>'#ffffff'</tt>.
		*/
	cls:'chart-container',

	chartType: "bar",

	options: null,

	update: function(datasets, labels){

		console.warn(datasets, labels);

		if(!this.chart) {
			// this.on("resize", () => {
			// 	debugger;
			// 	this.chart.resize();
			// })
			this.on("destroy", () => {
				if(this.chart) {
					this.chart.destroy();
				}
			}, this);

			this.canvas = document.createElement("canvas");
			this.el.dom.appendChild(this.canvas);



			this.chart = new Chart(this.canvas, {

				type: this.chartType,
				data: {
					labels: labels,
					datasets: datasets
				},
				options: this.options || {}
			});


			this.canvas.onclick = (evt) => {
				const res = this.chart.getElementsAtEventForMode(
					evt,
					'nearest',
					{ intersect: true },
					true
				);
				// If didn't click on a bar, `res` will be an empty array
				if (res.length === 0) {
					return;
				}

				const label = this.chart.data.labels[res[0].index]

				this.fireEvent("chartclick", this, res.index, label, res);
			};

		} else {
			this.chart.data.datasets = datasets;
			this.chart.data.labels = labels;
			this.chart.update();
		}
	},

	download: function(filename) {

		this.canvas.toBlob((blob) => {

			// Create a temporary link element
			const link = document.createElement('a');

			// Set the download attribute and the filename
			link.download = filename;

			// Create a URL for the Blob object
			link.href = window.URL.createObjectURL(blob);

			// Simulate a click on the link element to trigger the download
			link.click();

			// Clean up the URL object
			window.URL.revokeObjectURL(link.href);

// Trigger the download
			a.click();
		});
	},

	copy: function() {
		try {
			this.canvas.toBlob((blob) => {
				navigator.clipboard.write([
					new ClipboardItem({
						'image/png': blob
					})
				]);
			})
		} catch (error) {
			console.error(error);
		}
	}
	
});