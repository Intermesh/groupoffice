#!/bin/bash

semgrep scan --config p/php --config p/typescript --config p/javascript --config p/secrets --config p/owasp-top-ten --config p/sql-injection --config p/default ../www

