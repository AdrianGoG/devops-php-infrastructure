import argparse
import json
import os
import socket
import sys
from datetime import datetime

import requests

# Paths are taken from the script's own folder, not from the current directory,
# so it works the same when Jenkins runs it as python-monitor/infra_check.py
# from the root of the workspace.
HERE = os.path.dirname(os.path.abspath(__file__))
LOG_FILE = os.path.join(HERE, "logs", "monitor.log")
TIMEOUT = 5


def log(message):
    """Write one line in the log file."""
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)

    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(now + "  " + message + "\n")


def check_server(server):
    """Try to open a TCP connection to the server (port 22, the SSH port)."""
    try:
        connection = socket.create_connection((server["host"], server["port"]), TIMEOUT)
        connection.close()
        return True
    except OSError:
        return False


def check_app(app):
    """Ask the application for its health endpoint and see what it answers."""
    result = {
        "name": app["name"],
        "server": app["server"],
        "code": None,
        "state": "down",
        "php": None,
        "time_ms": None,
    }

    try:
        response = requests.get(app["url"], timeout=TIMEOUT)
    except requests.RequestException:
        # No answer at all: the container or the server is down.
        return result

    result["code"] = response.status_code
    result["time_ms"] = int(response.elapsed.total_seconds() * 1000)

    # Read the PHP version from the JSON answer, if there is one. A broken
    # application answers 500 with an empty body, so this can fail.
    try:
        data = response.json()
        result["php"] = data.get("php")
    except ValueError:
        data = {}

    if response.status_code == 200:
        if not data:
            # A 200 without a JSON body is not a working application. When PHP
            # fails on a require(), it prints a warning first - the headers are
            # already gone with a 200 - and only then dies. Trusting the status
            # code alone reports a dead application as healthy.
            result["state"] = "error"
        elif data.get("status") == "ok":
            result["state"] = "ok"
        else:
            result["state"] = "degraded"
    elif response.status_code == 404:
        result["state"] = "not found"
    elif response.status_code == 503:
        result["state"] = "unavailable"
    elif response.status_code >= 500:
        result["state"] = "error"
    else:
        result["state"] = "error"

    return result


def print_report(servers, results):
    """Print the report on the screen."""
    print()
    print("  Infrastructure report -", datetime.now().strftime("%d.%m.%Y %H:%M:%S"))
    print("  " + "-" * 70)

    for server in servers:
        if server["ok"]:
            print("  {:<6} {:<16} reachable".format(server["key"], server["host"]))
        else:
            print("  {:<6} {:<16} NOT REACHABLE".format(server["key"], server["host"]))

    if servers:
        print("  " + "-" * 70)

    print("  {:<22}{:<6}{:<9}{:<7}{:<9}{}".format(
        "application", "srv", "php", "code", "time", "state"))

    for app in results:
        print("  {:<22}{:<6}{:<9}{:<7}{:<9}{}".format(
            app["name"],
            app["server"],
            app["php"] or "-",
            app["code"] or "-",
            str(app["time_ms"]) + " ms" if app["time_ms"] else "-",
            app["state"],
        ))

    ok_count = 0
    for app in results:
        if app["state"] == "ok":
            ok_count += 1

    print("  " + "-" * 70)
    print("  {} out of {} applications are working".format(ok_count, len(results)))
    print()

    return ok_count


def compare(old_file, results):
    """Compare with an older report and show what changed."""
    try:
        with open(old_file, encoding="utf-8") as f:
            old = json.load(f)
    except (OSError, ValueError):
        print("  Could not read", old_file)
        return 0

    old_states = {}
    for app in old["applications"]:
        old_states[app["name"]] = app["state"]

    broken = 0

    print("  Comparison with", old_file, "(from " + old["date"] + ")")
    print("  " + "-" * 70)

    for app in results:
        before = old_states.get(app["name"])

        if before is None or before == app["state"]:
            continue

        if before == "ok":
            print("  {:<22} {} -> {}   BROKE".format(app["name"], before, app["state"]))
            broken += 1
        elif app["state"] == "ok":
            print("  {:<22} {} -> {}   fixed".format(app["name"], before, app["state"]))
        else:
            print("  {:<22} {} -> {}".format(app["name"], before, app["state"]))

    if broken == 0:
        print("  Nothing broke.")

    print()

    return broken


def main():
    parser = argparse.ArgumentParser(description="Check the infrastructure.")
    parser.add_argument("--targets", default=os.path.join(HERE, "targets.json"),
                        help="file with the servers and applications")
    parser.add_argument("--only", help="check one application only, by name")
    parser.add_argument("--report", help="save the result in a JSON file")
    parser.add_argument("--compare", help="compare with a saved JSON file")
    args = parser.parse_args()

    with open(args.targets, encoding="utf-8") as f:
        targets = json.load(f)

    # Check the servers.
    servers = []
    for server in targets["servers"]:
        ok = check_server(server)
        servers.append({"key": server["key"], "host": server["host"], "ok": ok})

        if ok:
            log("server {} ({}) is reachable".format(server["key"], server["host"]))
        else:
            log("server {} ({}) is NOT reachable".format(server["key"], server["host"]))

    # Check the applications.
    applications = targets["applications"]

    if args.only:
        applications = [a for a in applications if a["name"] == args.only]

        if not applications:
            print("No application named", args.only, "in", args.targets)
            sys.exit(1)

    results = []
    for app in applications:
        result = check_app(app)
        results.append(result)

        log("{} code={} state={} php={}".format(
            result["name"], result["code"], result["state"], result["php"]))

    ok_count = print_report(servers, results)
    log("report: {}/{} applications working".format(ok_count, len(results)))

    broken = 0
    if args.compare:
        broken = compare(args.compare, results)

    if args.report:
        report = {
            "date": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            "servers": servers,
            "applications": results,
        }

        with open(args.report, "w", encoding="utf-8") as f:
            json.dump(report, f, indent=2)

        print("  Saved in", args.report)
        print()

    # Exit code for Jenkins: 1 if something is not working.
    if ok_count < len(results) or broken > 0:
        return 1

    return 0


if __name__ == "__main__":
    sys.exit(main())