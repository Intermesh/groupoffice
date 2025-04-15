
INSERT INTO email_account (id,name,email,quota,mdaDsn, mtaDsn, modifiedAt,createdAt,aclId)
	  SELECT e.id, a.name, a.email, 0,
		CONCAT('imap:host=',host,';port=',port,';user=',username,';pass=',password,';encryption=',CASE
					WHEN imap_encryption = 'ssl' THEN 'ssl'
					WHEN imap_encryption = 'tls' THEN 'tls'
					ELSE 'none'
			END,IF(novalidate_cert,';novalidate=true','')),
		CONCAT('smtp:host=',smtp_host,';port=',smtp_port,';user=',IF(force_smtp_login, username,smtp_username),';pass=',IF(force_smtp_login,password,smtp_password),';encryption=',CASE
				WHEN smtp_encryption = 'ssl' THEN 'ssl'
				WHEN smtp_encryption = 'tls' THEN 'tls'
			  ELSE 'none'
			END,IF(smtp_allow_self_signed,';novalidate=true','')),
	         '2025-06-01','2025-04-01',acl_id FROM em_accounts e JOIN em_aliases a ON a.account_id = e.id AND a.default=1;

INSERT INTO email_identity (id,accountId,name,email,replyTo,bcc,textSignature,htmlSignature)
  SELECT id, account_id, name, email, null,null,signature,signature FROM em_aliases;

-- do we need to migrate other settings?