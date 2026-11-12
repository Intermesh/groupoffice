#!/usr/bin/env python3
"""
The marketplace pair, end to end, over real HTTP.

Everything here goes through the customer instance's public JMAP API as an
administrator — the same calls the System Settings panel makes — so the proof
covers the controller, the HTTP client, the vendor's page API, bearer auth, the
package signature and the offline license gate. The few things the public API
deliberately does not expose (clicking a verification link, granting and
revoking an entitlement) are done on the vendor with container/customer.php.

Run through ./run.sh test, which supplies the environment.
"""

import json
import os
import subprocess
import sys
import urllib.error
import urllib.request

CLIENT_URL = os.environ.get("MP_CLIENT_URL", "http://localhost:8082")
SERVER_INTERNAL = os.environ.get("MP_SERVER_INTERNAL", "http://mpserver")
PACKAGE = os.environ.get("MP_PACKAGE", "mpe2e")
MODULE = os.environ.get("MP_MODULE", "mpdemo")
VERSION = os.environ.get("MP_VERSION", "1.0.0")
CUSTOMER_EMAIL = "e2e-customer@example.test"
CUSTOMER_PASSWORD = "e2e-Customer-123"
IN = "/var/www/html/go/modules/community/marketplace/tests/e2e/container"

failures = []
checks = 0


def check(label, ok, detail=""):
    global checks
    checks += 1
    if ok:
        print("  ok   %s" % label)
    else:
        print("  FAIL %s%s" % (label, ("  <- " + str(detail)) if detail else ""))
        failures.append(label)
    return ok


def post(url, payload, token=None):
    body = json.dumps(payload).encode()
    req = urllib.request.Request(url, data=body, method="POST")
    req.add_header("Content-Type", "application/json")
    if token:
        req.add_header("Authorization", "Bearer " + token)
    try:
        with urllib.request.urlopen(req, timeout=180) as r:
            return r.status, json.loads(r.read().decode() or "{}")
    except urllib.error.HTTPError as e:
        raw = e.read().decode()
        try:
            return e.code, json.loads(raw or "{}")
        except ValueError:
            return e.code, {"raw": raw}


def login():
    status, data = post(CLIENT_URL + "/api/auth.php",
                        {"username": "admin", "password": "admin123"})
    if status not in (200, 201) or "accessToken" not in data:
        print("!! could not log in to the customer instance: %s %s" % (status, data))
        sys.exit(1)
    return data["accessToken"]


def jmap(token, method, params):
    """One JMAP call. Returns (name, payload) — name is 'error' when it failed."""
    status, data = post(CLIENT_URL + "/api/jmap.php", [[method, params, "c0"]], token)
    if not isinstance(data, list) or not data:
        return "error", {"httpStatus": status, "body": data}
    name, payload, _ = data[0][0], data[0][1], data[0][2]
    return name, payload


def on_server(*args):
    out = subprocess.run(["docker", "exec", "-u", "www-data", "mpserver", "php",
                          IN + "/customer.php"] + list(args),
                         capture_output=True, text=True)
    return out.returncode, (out.stdout + out.stderr).strip()


def probe_client():
    out = subprocess.run(["docker", "exec", "-u", "www-data", "mpclient", "php",
                          IN + "/probe-client.php", PACKAGE, MODULE],
                         capture_output=True, text=True)
    try:
        return json.loads(out.stdout.strip().splitlines()[-1])
    except (ValueError, IndexError):
        return {"_error": (out.stdout + out.stderr).strip()}


def client_files():
    out = subprocess.run(["docker", "exec", "mpclient", "ls", "-1",
                          "/var/www/html/go/modules/%s/%s" % (PACKAGE, MODULE)],
                         capture_output=True, text=True)
    return sorted(out.stdout.split())


def main():
    token = login()

    # Start from a known state: a customer left over from an earlier run would
    # already be verified, and "an unverified account cannot sign in" would fail
    # for a reason that has nothing to do with the code.
    rc, out = on_server("reset", CUSTOMER_EMAIL)
    if not check("previous run's customer cleared", rc == 0, out):
        return

    # And the customer side: a repository left over from an earlier run holds
    # this package, which the "one repository per package" rule would reject.
    # Deleting it through the API is also the only exercise the destroy path gets.
    name, res = jmap(token, "MarketplaceRepository/query", {})
    old_ids = res.get("ids") or [] if name != "error" else []
    if old_ids:
        jmap(token, "MarketplaceRepository/set", {"destroy": old_ids})
    subprocess.run(["docker", "exec", "mpclient", "bash", "-c",
                    "find /var/www/html/go/modules/%s -mindepth 1 -delete 2>/dev/null; true" % PACKAGE],
                   capture_output=True)
    name, res = jmap(token, "MarketplaceRepository/query", {})
    check("previous run's repository cleared", (res.get("ids") or []) == [], res)

    print("\n== 1. self-registration against the vendor")
    name, res = jmap(token, "MarketplaceRepository/register", {
        "url": SERVER_INTERNAL, "email": CUSTOMER_EMAIL,
        "name": "E2E Customer", "password": CUSTOMER_PASSWORD,
        "companyName": "E2E Ltd",
    })
    check("register succeeds", name != "error", res)
    check("register says the account needs verifying", bool(res.get("verifyRequired")), res)
    # By design there is no token here: issuing one on the "new account" path but
    # not on the duplicate path would tell a caller which it hit.
    check("register hands out no token", "token" not in res, res)

    print("\n== 2. an unverified account cannot sign in")
    name, res = jmap(token, "MarketplaceRepository/login", {
        "url": SERVER_INTERNAL, "email": CUSTOMER_EMAIL, "password": CUSTOMER_PASSWORD})
    check("login is refused and says why", name != "error" and res.get("verifyRequired") is True, res)

    rc, out = on_server("verify", CUSTOMER_EMAIL)
    check("vendor verifies the account", rc == 0, out)

    print("\n== 2b. after verifying, login issues the API token")
    name, res = jmap(token, "MarketplaceRepository/login", {
        "url": SERVER_INTERNAL, "email": CUSTOMER_EMAIL, "password": CUSTOMER_PASSWORD})
    check("login returns a token", name != "error" and bool(res.get("token")), res)
    api_token = res.get("token") or ""

    name, res = jmap(token, "MarketplaceRepository/login", {
        "url": SERVER_INTERNAL, "email": CUSTOMER_EMAIL, "password": "wrong-password"})
    check("a wrong password is refused", name == "error", res)

    print("\n== 3. adding the repository pins package, name and signing key")
    name, res = jmap(token, "MarketplaceRepository/set", {
        "create": {"c2": {"url": SERVER_INTERNAL, "token": api_token}}})
    created = (res.get("created") or {}).get("c2") if name != "error" else None
    check("repository saved", bool(created), res)
    if not created:
        return
    repo_id = created["id"]
    check("package pinned from the vendor's /info", created.get("package") == PACKAGE, created)
    check("the token is never echoed back", "token" not in created, list(created.keys()))
    check("tokenConfigured is exposed instead", created.get("tokenConfigured") is True, created)

    print("\n== 4. catalogue over the API")
    name, cat = jmap(token, "MarketplaceRepository/catalog", {"repositoryId": repo_id})
    products = cat.get("products") or [] if name != "error" else []
    demo = next((p for p in products if p.get("moduleName") == MODULE), None)
    check("the demo product is listed", demo is not None, cat)
    if demo:
        check("its release matches this instance's branch",
              (demo.get("release") or {}).get("version") == VERSION, demo.get("release"))
        check("it is not owned yet", not demo.get("owned"), demo)

    print("\n== 5. download without an entitlement is refused")
    name, res = jmap(token, "MarketplaceRepository/download",
                     {"repositoryId": repo_id, "module": MODULE, "version": ""})
    check("refused", name == "error", res)
    check("and says why, rather than 'not a valid ZIP'",
          "entitlement" in json.dumps(res).lower(), res)
    check("nothing was written to go/modules", client_files() == [], client_files())

    print("\n== 6. entitled: license, then download")
    rc, out = on_server("entitle", CUSTOMER_EMAIL, MODULE)
    check("vendor grants the product", rc == 0, out)

    name, res = jmap(token, "MarketplaceRepository/refresh", {"repositoryId": repo_id})
    check("license refresh succeeds", name != "error" and res.get("success"), res)

    state = probe_client()
    check("the runtime gate now licenses the module", state.get("licensed") is True, state)
    check("the license is bound to this instance's host",
          state.get("host") == "mpclient", state)

    name, res = jmap(token, "MarketplaceRepository/download",
                     {"repositoryId": repo_id, "module": MODULE, "version": ""})
    check("download succeeds", name != "error" and res.get("success"), res)
    check("it reports the concrete version, not ''", res.get("version") == VERSION, res)

    files = client_files()
    check("the module landed on disk", "Module.php" in files and "MARKER.txt" in files, files)
    state = probe_client()
    check("the downloaded version is recorded",
          state.get("downloaded", {}).get(MODULE) == VERSION, state)

    print("\n== 7. a revoked entitlement reaches the client on the next refresh")
    rc, out = on_server("revoke", CUSTOMER_EMAIL, MODULE)
    check("vendor revokes the grant", rc == 0, out)
    name, res = jmap(token, "MarketplaceRepository/refresh", {"repositoryId": repo_id})
    check("refresh still succeeds", name != "error" and res.get("success"), res)
    state = probe_client()
    check("the runtime gate now refuses the module", state.get("licensed") is False, state)

    print("\n== 8. the signing key is pinned: a reinstalled vendor is refused")
    subprocess.run(["docker", "exec", "-u", "www-data", "mpserver", "php", "-r",
                    "require '/var/www/html/vendor/autoload.php';"
                    "go\\core\\App::get();"
                    "go()->setAuthState((new go\\core\\auth\\TemporaryState())->setUserId(1));"
                    "$db = go()->getDbConnection()->getPDO();"
                    "$db->exec(\"DELETE FROM core_setting WHERE name IN ('privateKey','publicKey') "
                    "AND moduleId = (SELECT id FROM core_module WHERE name='marketplaceserver')\");"],
                   capture_output=True, text=True)
    subprocess.run(["docker", "restart", "mpserver"], capture_output=True)
    subprocess.run(["docker", "exec", "mpclient", "bash", "-c",
                    "for i in $(seq 1 60); do curl -s -o /dev/null %s/ && break; sleep 1; done" % SERVER_INTERNAL],
                   capture_output=True)
    # Re-issuing the key pair happens lazily on the next /info.
    name, res = jmap(token, "MarketplaceRepository/refresh", {"repositoryId": repo_id})
    check("refresh refuses a changed signing key", name == "error", res)
    check("and explains it in the user's words", "key" in json.dumps(res).lower(), res)
    state = probe_client()
    check("the repository is flagged, not silently re-pinned",
          (state.get("repo") or {}).get("keyMismatch") is True, state)

    print("\n%d checks, %d failed" % (checks, len(failures)))
    if failures:
        for f in failures:
            print("  - " + f)
        sys.exit(1)
    print("ALL GREEN")


if __name__ == "__main__":
    main()
