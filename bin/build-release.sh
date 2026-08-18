#!/usr/bin/env sh
set -eu
project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
version=$(sed -n 's/^Version: //p' "$project_dir/style.css" | head -1)
dist_dir="$project_dir/dist"
stage_dir=$(mktemp -d)
case "$stage_dir" in /tmp/*) ;; *) printf '%s\n' 'Unsafe temporary directory' >&2; exit 1 ;; esac
trap 'rm -rf -- "${stage_dir:?}"' EXIT INT TERM
mkdir -p "$dist_dir" "$stage_dir/orion-26"
tar -C "$project_dir" --exclude=.git --exclude=dist --exclude=.github --exclude=docs --exclude=bin --exclude=CONTRIBUTING.md --exclude=SECURITY.md --exclude=TRANSLATIONS.md --exclude=.gitignore -cf - . | tar -C "$stage_dir/orion-26" -xf -
if command -v zip >/dev/null 2>&1; then
	(cd "$stage_dir" && zip -qr "$dist_dir/orion-26-$version.zip" orion-26)
elif command -v 7z >/dev/null 2>&1; then
	(cd "$stage_dir" && 7z a -tzip -bd "$dist_dir/orion-26-$version.zip" orion-26 >/dev/null)
else
	printf '%s\n' 'zip or 7z is required' >&2
	exit 1
fi
printf '%s\n' "$dist_dir/orion-26-$version.zip"
