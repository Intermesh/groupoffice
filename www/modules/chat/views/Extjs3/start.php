<!DOCTYPE html>
<html>
    <head>
        <script src="https://gowebrtc.me/latest.js"></script> 
    </head>
    <body>
        <video id="localVideo" style="height:300px;margin:20px auto"></video>
        <div id="remoteVideos"></div>
    </body>
    <script type='text/javascript'>
var webrtc;
$(document).ready(function() {
webrtc = new goWebRTC({
		url:'https://intermesh.group-office.com:5281/http-bind',
			
		domain: 'intermesh.group-office.com',
			
    // the id/element dom element that will hold "our" video
    localVideoEl: 'localVideo',
    // the id/element dom element that will hold remote videos
    remoteVideosEl: 'remoteVideos',
    // immediately ask for camera access
    autoRequestMedia: true
});
// we have to wait until it's ready
webrtc.on('readyToCall', function () {
    // you can name it anything that is a valid xmpp node
    // most importantly, this means you can not use spaces
    var roomname;
    if (window.location.hash.length > 1) {
	roomname = window.location.hash.substr(1);
    } else {
        roomname = Math.random().toString(36).substr(2, 8);
	//window.location.pushState({}, 'goWebRTC', roomname);
	window.location.hash = roomname;
    }
    webrtc.joinRoom(roomname);
});
// we are notified of this
webrtc.on('videoAdded', function (video, session) {
    console.log('videoAdded');
});
webrtc.on('videoRemoved', function (video, session) {
    // the remote video with the participants id was removed 
    console.log('videoRemoved');
});
});
    </script>

</html>