#!/usr/bin/env python3
"""Upload FitCoch to UDRU hosting via SFTP."""

from __future__ import annotations

import argparse
import os
import sys
from pathlib import Path

try:
    import paramiko
except ImportError:
    print("Install paramiko first: pip install paramiko", file=sys.stderr)
    raise SystemExit(1)

ROOT = Path(__file__).resolve().parents[1]
EXCLUDE_DIRS = {".git", "tests", "node_modules", ".cursor", ".agents", "__pycache__"}
EXCLUDE_FILES = {".env", ".env.udru", "phpunit.xml", ".phpunit.result.cache", "deploy-test.txt"}


def should_skip(rel: Path) -> bool:
    parts = rel.parts
    if parts and parts[0] in EXCLUDE_DIRS:
        return True
    if rel.name in EXCLUDE_FILES:
        return True
    return False


def ensure_remote_dir(sftp: paramiko.SFTPClient, remote_dir: str) -> None:
    remote_dir = remote_dir.replace("\\", "/")
    if remote_dir in ("", "/"):
        return
    parts = [p for p in remote_dir.strip("/").split("/") if p]
    built = ""
    for part in parts:
        built += "/" + part
        try:
            sftp.stat(built)
        except OSError:
            sftp.mkdir(built)


def upload_tree(
    sftp: paramiko.SFTPClient,
    local_root: Path,
    remote_root: str,
) -> int:
    count = 0
    for path in local_root.rglob("*"):
        if not path.is_file():
            continue
        rel = path.relative_to(local_root)
        if should_skip(rel):
            continue
        remote = (remote_root.rstrip("/") + "/" + rel.as_posix()).replace("//", "/")
        ensure_remote_dir(sftp, os.path.dirname(remote))
        sftp.put(str(path), remote)
        count += 1
        if count % 25 == 0:
            print(f"  uploaded {count} files...")
    return count


def main() -> int:
    parser = argparse.ArgumentParser(description="Deploy FitCoch via SFTP")
    parser.add_argument("--host", default="hosting.udru.ac.th")
    parser.add_argument("--user", default=os.environ.get("FITCOCH_SFTP_USER", "fitcoch"))
    parser.add_argument("--password", default=os.environ.get("FITCOCH_SFTP_PASS", ""))
    parser.add_argument("--remote-root", default="/")
    parser.add_argument("--env-file", default=".env.udru")
    args = parser.parse_args()

    password = args.password
    if not password:
        import getpass

        password = getpass.getpass("SFTP password: ")

    env_path = ROOT / args.env_file
    if not env_path.is_file():
        print(f"Missing {env_path} — copy from .env.production.udru.example", file=sys.stderr)
        return 1

    print(f"Connecting to {args.user}@{args.host} ...")
    transport = paramiko.Transport((args.host, 22))
    transport.connect(username=args.user, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)

    try:
        print(f"Uploading project to {args.remote_root} ...")
        total = upload_tree(sftp, ROOT, args.remote_root)
        remote_env = args.remote_root.rstrip("/") + "/.env"
        ensure_remote_dir(sftp, os.path.dirname(remote_env))
        sftp.put(str(env_path), remote_env)
        print(f"Done — {total} files + .env uploaded.")
        print("Open https://hosting.udru.ac.th/~fitcoch/public/")
        print("Import database/migrations/all.sql in phpMyAdmin if not done yet.")
    finally:
        sftp.close()
        transport.close()

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
