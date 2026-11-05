go.Modules.register('community', 'pdfeditor', {
	title: "PDF editor",
	initModule: function () {}
});

go.modules.community.pdfeditor.openPDF = function (fileId) {

	const fileUrl = GO.url('files/file/download', {id: fileId});

	const viewerUrl = 'go/modules/community/pdfeditor/views/pdf.js/web/viewer.html?fileId=' + fileId + '&CSRFToken=' + encodeURIComponent(go.User.session.CSRFToken) + '&file=' + encodeURIComponent(fileUrl);

	window.open(viewerUrl, '_blank');
}