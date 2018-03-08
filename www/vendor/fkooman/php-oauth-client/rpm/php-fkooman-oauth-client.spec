%global composer_vendor  fkooman
%global composer_project oauth-client

%global github_owner     fkooman
%global github_name      php-oauth-client

Name:       php-%{composer_vendor}-%{composer_project}
Version:    0.5.2
Release:    1%{?dist}
Summary:    OAuth 2.0 "Authorization Code Grant" client written in PHP

Group:      System Environment/Libraries
License:    AGPLv3+
URL:        https://github.com/%{github_owner}/%{github_name}
Source0:    https://github.com/%{github_owner}/%{github_name}/archive/%{version}.tar.gz
BuildArch:  noarch

Provides:   php-composer(%{composer_vendor}/%{composer_project}) = %{version}

Requires:   php >= 5.3.3

Requires:   php-openssl
Requires:   php-pdo

Requires:   php-composer(fkooman/oauth-common) >= 0.5.0
Requires:   php-composer(fkooman/oauth-common) < 0.6.0
Requires:   php-pear(guzzlephp.org/pear/Guzzle) >= 3.8.0
Requires:   php-pear(guzzlephp.org/pear/Guzzle) < 4.0

%description
This project provides an OAuth 2.0 "Authorization Code Grant" client as 
described in RFC 6749, section 4.1.

The client can be controlled through a PHP API that is used from the 
application trying to access an OAuth 2.0 protected resource server. 

%prep
%setup -qn %{github_name}-%{version}

%build

%install
mkdir -p ${RPM_BUILD_ROOT}%{_datadir}/php
cp -pr src/* ${RPM_BUILD_ROOT}%{_datadir}/php

%files
%defattr(-,root,root,-)
%dir %{_datadir}/php/%{composer_vendor}/OAuth/Client
%{_datadir}/php/%{composer_vendor}/OAuth/Client/*
%doc README.md CHANGES.md lgpl-3.0.txt composer.json docs/ example/ schema/

%changelog
* Thu Sep 25 2014 François Kooman <fkooman@tuxed.net> - 0.5.2-1
- update to 0.5.2

* Thu Sep 11 2014 François Kooman <fkooman@tuxed.net> - 0.5.1-1
- update to 0.5.1

* Sat Aug 30 2014 François Kooman <fkooman@tuxed.net> - 0.5.0-2
- use github tagged release sources
- update group to System Environment/Libraries

* Tue Aug 19 2014 François Kooman <fkooman@tuxed.net> - 0.5.0-1
- initial package
