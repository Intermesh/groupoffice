go.showComposer = function(cfg) {

	if(GO.email && GO.email.showComposer && !go.User.emailSettings.use_desktop_composer) {
		GO.email.showComposer({values:cfg});
		return;
	}

	// when the email module is not installed of we want to use the desktop composer.
	// dissect the cfg passed to email composer
	if(cfg.loadUrl) {
		Ext.apply(cfg.loadParams, {
			content_type: 'plain',
			template_id: 0
		});
		GO.request({
			url: cfg.loadUrl,
			params: cfg.loadParams,
			failure:function(response, options) {
				GO.errorDialog.show(response.result.feedback)
			},
			success: function(response, options, result) {
				//console.log(response, options, result);
				go.util.mailto({
					to: result.data.to,
					body: result.data.plainbody,
					subject: result.data.subject
				});
			}
		})
	} else if(cfg.to) {
		go.util.mailto({
			to: cfg.to || '',
			name:cfg.name,
			body: cfg.body || '',
			subject: cfg.subject|| ''
		});
	} else {
		alert('incorrect showComposer config');
	}
}