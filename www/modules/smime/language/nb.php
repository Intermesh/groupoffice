<?php


$l['name']='SMIME støtte';
$l['description']='Utvide e-postmodulen med støtte for SMIME signering og kryptering.';
$l['noPublicCertForEncrypt']="Kan ikke kryptere meldingen fordi du ikke har det offentlige sertifiktatet for %s. Du kan åpne en signert melding fra mottageren og kontrollere signaturen for å importere den offentlige nøkkelen.";
$l['noPrivateKeyForDecrypt']="Denne meldingen er kryptert, men du har ikke den private nøkkelen som kreves for å dekryptere den.";
$l['badGoLogin']="{product_name} passordet er feil.";
$l['smime_pass_matches_go']="Passordet til SMIME nøkkelen er det samme som GroupOffice passordet. Av sikkerhetsårsaker er dette ikke tillatt!";
$l['smime_pass_empty']="Denne SMIME nøkkelen har ikke noe passord. Av sikkerhetsårsaker er dette ikke tillatt!";
$l['invalidCert']="Sertifikatet er ugyldig!";
$l['validCert']="Gyldig sertifikat";
$l['certEmailMismatch']="Sertfikatet er gyldig, men e-postadressen sertifikatet gjelder for er forskjellig fra denne e-postens avsenderadresse.";
$l['decryptionFailed']='Feil ved SMIME dekryptering.';
$l["enterPassword"]="Oppgi passordet for ditt SMIME sertifikat.";
$l["messageEncrypted"]="Denne meldingen er sendt deg kryptert.";
$l["messageSigned"]="Denne meldingen er digitalt signert. Trykk her for å kontrollere signaturen og importere sertfikatet.";
$l["smimeCert"]="SMIME sertifikat";
$l["sign"]="Signere med SMIME";
$l["encrypt"]="Kryptere med SMIME";
$l["settings"]="SMIME innstillinger";
$l["deleteCert"]="Slett sertifikat";
$l["selectPkcs12Cert"]="Velg et nytt PKCS12 sertifikat";
$l["alwaysSign"]='Signer alltid meldinger';
$l["pkcs12Cert"]="PKCS12 sertifikat";
$l["pkcs12CertInfo"]="For å laste opp et nytt PCSK12 sertifikat må du oppgi ditt {product_name} passord. Av sikkerhetsårsaker må {product_name} passordet være forskjellig fra passordet til PCSK12 sertifikatet. Du kan heller ikke ha tomt passord.";
$l["pubCerts"]="Offentlige SMIME sertifikater";
$l["youHaveAcert"]='Du har allerede lastet opp et sertifikat. SMIME støtte er aktivert for denne kontoen.';
$l["downloadCert"]='Last ned sertifikat';
$l['email']="E-post";
$l['hash']="Hash";
$l['serial_number']="Serienummer";
$l['version']="Versjon";
$l['issuer']="Utsteder";
$l['valid_to']="Gyldig til";
$l['valid_from']="Gyldig fra";