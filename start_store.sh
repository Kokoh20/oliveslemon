#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
PORT="${PORT:-3000}"
HOST="${HOST:-127.0.0.1}"

if ! command -v php &>/dev/null; then
  echo "[Error] PHP is not installed or not in PATH."
  echo "Install PHP (e.g., sudo apt install php-cli) then re-run this script."
  exit 1
fi

echo "Starting Olives & Lemon on http://${HOST}:${PORT}"
echo "Press Ctrl+C to stop."
php -S "${HOST}:${PORT}" -t "$ROOT"
