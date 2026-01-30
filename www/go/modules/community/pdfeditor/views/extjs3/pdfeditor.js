go.Modules.register('community', 'pdfeditor', {
	title: "PDF editor",
	initModule: function () {}
});

go.modules.community.pdfeditor.openPDF = function (fileId) {

	const fileUrl = GO.settings.config.full_url + 'index.php?r=files/file/download&id=' + fileId + '&security_token=' + GO.securityToken;

	const viewerUrl = 'go/modules/community/pdfeditor/views/pdfjs/web/viewer.html?fileId=' + fileId + '&security_token=' + GO.securityToken + '&CSRFToken=' + encodeURIComponent(go.User.session.CSRFToken) + '&file=' + encodeURIComponent(fileUrl);

	window.open(viewerUrl, '_blank');
}