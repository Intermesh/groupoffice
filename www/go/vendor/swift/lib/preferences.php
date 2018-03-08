<?php
/****************************************************************************/
/*                                                                          */
/* YOU MAY WISH TO MODIFY OR REMOVE THE FOLLOWING LINES WHICH SET DEFAULTS  */
/*                                                                          */
/****************************************************************************/

$preferences = Swift_Preferences::getInstance();

// Sets the default charset so that setCharset() is not needed elsewhere
$preferences->setCharset('utf-8');

if(\GO\Base\Util\Common::isWindows()){
	$preferences->setTempDir(GO::config()->tmpdir)
					->setCacheType('array');
}  else {
	$preferences->setTempDir(GO::config()->tmpdir)
        ->setCacheType('disk');
}
// this should only be done when Swiftmailer won't use the native QP content encoder
// see mime_deps.php
if (version_compare(phpversion(), '5.4.7', '<')) {
    $preferences->setQPDotEscape(\GO::config()->swift_qp_dot_escape);
}
