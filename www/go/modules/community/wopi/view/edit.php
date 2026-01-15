<?php
/**
 * @var string $origin
 * @var string $action
 * @var \GO\Files\Model\File $file
 * @var \go\modules\business\wopi\model\Token $token
 * @var \go\modules\business\wopi\model\Service $service
 */
?><!doctype html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Enable IE Standards mode -->
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title></title>
    <meta name="description" content="">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">

    <!-- <link rel="shortcut icon"
          href="<OFFICE ONLINE APPLICATION FAVICON URL>" /> -->

    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            -ms-content-zooming: none;
        }

        #office_frame {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: 0;
            border: none;
            display: block;
        }
    </style>
</head>
<body>

<form id="office_form" name="office_form" target="office_frame"
      action="<?= $action; ?>" method="post">
    <input name="access_token" value="<?= $token->getToken(); ?>" type="hidden"/>
    <input name="access_token_ttl" value="<?= $token->getExpiresAt()->format('U') * 1000; ?>" type="hidden"/>
</form>

<span id="frameholder"></span>

<script type="text/javascript">
	var frameholder = document.getElementById('frameholder');
	var office_frame = document.createElement('iframe');
	office_frame.name = 'office_frame';
	office_frame.id = 'office_frame';

	// The title should be set for accessibility
	office_frame.title = 'Office Online Frame';

	// This attribute allows true fullscreen mode in slideshow view
	// when using PowerPoint Online's 'view' action.
	office_frame.setAttribute('allowfullscreen', 'true');

	// The sandbox attribute is needed to allow automatic redirection to the O365 sign-in page in the business user flow
	//Sandbox attribute breaks libreoffice online printing!
  <?php if($service->type == \go\modules\business\wopi\model\Service::TYPE_OFFICE_ONLINE) { ?>
	office_frame.setAttribute('sandbox', 'allow-downloads allow-scripts allow-same-origin allow-forms allow-popups allow-top-navigation allow-popups-to-escape-sandbox');
  <?php
  }
  ?>
	frameholder.appendChild(office_frame);

	document.getElementById('office_form').submit();


	function post( msgId, Values) {
		const msg = {
			MessageId: msgId,
			SendTime: Date.now()
		}
		if (Values) {
			msg.Values = Values;
		}

		console.log("POST", msg);

		office_frame.contentWindow.postMessage(JSON.stringify(msg), <?= json_encode($origin) ?>);
	}



	window.addEventListener("message", (event) => {

		console.log(event);

		let msg = event.data && typeof event.data === 'string' ? JSON.parse(event.data) : null;
		if (!msg) {
			return;
		}


		switch (msg.MessageId) {
			case 'App_LoadingStatus':
				if (msg.Values) {
                    if (msg.Values.Status == 'Initialized') {
                        post( 'Host_PostmessageReady');
                    }
                }
				break;

            case 'UI_SaveAs':


				const extension = (msg.Values && msg.Values.format) ? msg.Values.format : <?= json_encode($file->fsFile->extension()); ?>;
                // TODO make this a nice dialog
                const filename = prompt("Please enter the filename you want to save as:", <?= json_encode($file->fsFile->nameWithoutExtension()); ?> + "." +extension);

                post(
                    'Action_SaveAs',
                    {
                        Filename: filename,
                        Notify: true
                    })


                break;
        }

	});
</script>

</body>
</html>