<?php
//If this file works without the extension then it's not encoded
if(!extension_loaded('ionCube Loader')) {
	return true;
}    

if(!ioncube_file_is_encoded()) {
  return true;
}

return ioncube_license_matches_server() && !ioncube_license_has_expired();