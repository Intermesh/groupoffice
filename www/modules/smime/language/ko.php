<?php


$l["enterPassword"]="Please enter the password of your SMIME certificate.";
$l["messageEncrypted"]="This message was sent to you encrypted.";
$l["messageSigned"]="This message is digitally signed. Click here to verify the signature and import the certificate.";
$l["smimeCert"]="SMIME Certificate";
$l["sign"]="Sign with SMIME";
$l["encrypt"]="Encrypt with SMIME";
$l["settings"]="SMIME setttings";
$l["deleteCert"]="Delete certificate";
$l["selectPkcs12Cert"]="Select new PKCS12 Certificate";
$l["alwaysSign"]='Always sign messages';
$l["pkcs12Cert"]="PKCS12 certificate";
$l["pkcs12CertInfo"]="To upload a new PCSK12 certificate you must enter your {product_name} password. This password may not match the password of your PCSK12 certificate for security reasons. No password is also prohibited.";

$l['name']='SMIME support';
$l['description']='Extend the mail module with SMIME signing and encryption.';
$l['noPublicCertForEncrypt']="Could not encrypt message because you don't have the public certificate for %s. Open a signed message of the recipient and verify the signature to import the public key.";
$l['noPrivateKeyForDecrypt']="This message is encrypted and you don't have the private key to decrypt this message.";
$l['badGoLogin']="The {product_name} password was incorrect.";
$l['smime_pass_matches_go']="Your SMIME key password matches your {product_name} password. This is prohibited for security reasons!";
$l['smime_pass_empty']="Your SMIME key has no password. This is prohibited for security reasons!";
$l['invalidCert']="The certificate is invalid!";
$l['validCert']="Valid certificate";
