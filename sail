#!/usr/bin/env bash

# Laravel Sail wrapper script for easier Docker usage

SAIL_CMD="./vendor/bin/sail"

# Check if vendor/bin/sail exists
if [ ! -f "$SAIL_CMD" ]; then
    echo "Error: Laravel Sail not found. Run 'composer install' first."
    exit 1
fi

# Pass all arguments to sail
$SAIL_CMD "$@"