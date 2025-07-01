#!/bin/bash

# Twilio SDK Upgrade Script
# Usage: ./upgrade-twilio.sh [phase]
# Phases: 7 (upgrade to 7.x), 8 (upgrade to 8.x), test (run tests only)

set -e  # Exit on error

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="/tmp/twilio-backup-$(date +%Y%m%d-%H%M%S)"

echo "🚀 Twilio SDK Upgrade Script"
echo "============================"
echo ""

# Function to create backup
create_backup() {
    echo "📦 Creating backup..."
    cp -r "$PLUGIN_DIR" "$BACKUP_DIR"
    echo "✅ Backup created at: $BACKUP_DIR"
    echo ""
}

# Function to run tests
run_tests() {
    echo "🧪 Running tests..."
    cd "$PLUGIN_DIR"
    if [ -f "test-upgrade.php" ]; then
        php test-upgrade.php
    else
        echo "❌ Test file not found"
        exit 1
    fi
    echo ""
}

# Function to upgrade to version 7.x
upgrade_to_7x() {
    echo "📈 Upgrading to Twilio SDK 7.x..."
    cd "$PLUGIN_DIR"
    
    # Update composer.json
    sed -i.bak 's/"twilio\/sdk": "\^[0-9]*\.[0-9]*"/"twilio\/sdk": "^7.16"/' composer.json
    
    # Run composer update
    if command -v composer &> /dev/null; then
        composer update twilio/sdk
        echo "✅ Upgrade to 7.x completed"
    else
        echo "❌ Composer not found. Please run: composer update twilio/sdk"
        exit 1
    fi
    echo ""
}

# Function to upgrade to version 8.x
upgrade_to_8x() {
    echo "📈 Upgrading to Twilio SDK 8.x..."
    cd "$PLUGIN_DIR"
    
    # Update composer.json
    sed -i.bak 's/"twilio\/sdk": "\^[0-9]*\.[0-9]*"/"twilio\/sdk": "^8.6"/' composer.json
    
    # Run composer update
    if command -v composer &> /dev/null; then
        composer update twilio/sdk
        echo "✅ Upgrade to 8.x completed"
    else
        echo "❌ Composer not found. Please run: composer update twilio/sdk"
        exit 1
    fi
    echo ""
}

# Function to rollback
rollback() {
    echo "🔄 Rolling back..."
    if [ -d "$1" ]; then
        cp -r "$1"/* "$PLUGIN_DIR/"
        echo "✅ Rollback completed from: $1"
    else
        echo "❌ Backup directory not found: $1"
        exit 1
    fi
}

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ composer.json not found. Please run this script from the plugin directory."
    exit 1
fi

# Parse command line arguments
case "$1" in
    "7")
        create_backup
        upgrade_to_7x
        run_tests
        echo "🎉 Phase 2 (7.x upgrade) completed successfully!"
        echo "📝 Review the test results above."
        echo "📁 Backup location: $BACKUP_DIR"
        ;;
    "8")
        create_backup
        upgrade_to_8x
        run_tests
        echo "🎉 Phase 3 (8.x upgrade) completed successfully!"
        echo "📝 Review the test results above."
        echo "📁 Backup location: $BACKUP_DIR"
        ;;
    "test")
        run_tests
        ;;
    "rollback")
        if [ -n "$2" ]; then
            rollback "$2"
        else
            echo "❌ Please specify backup directory: ./upgrade-twilio.sh rollback /path/to/backup"
            exit 1
        fi
        ;;
    "status")
        echo "📊 Current Status:"
        cd "$PLUGIN_DIR"
        if [ -f "composer.lock" ]; then
            CURRENT_VERSION=$(grep -A1 '"name": "twilio/sdk"' composer.lock | grep '"version"' | sed 's/.*"version": "\([^"]*\)".*/\1/')
            echo "   Twilio SDK: $CURRENT_VERSION"
        fi
        echo "   PHP: $(php -r 'echo PHP_VERSION;')"
        echo ""
        ;;
    *)
        echo "Usage: $0 [command]"
        echo ""
        echo "Commands:"
        echo "  7        - Upgrade to Twilio SDK 7.x"
        echo "  8        - Upgrade to Twilio SDK 8.x"
        echo "  test     - Run compatibility tests"
        echo "  status   - Show current versions"
        echo "  rollback - Rollback from backup"
        echo ""
        echo "Examples:"
        echo "  $0 7              # Upgrade to 7.x"
        echo "  $0 8              # Upgrade to 8.x"
        echo "  $0 test           # Run tests only"
        echo "  $0 rollback /tmp/backup-dir"
        echo ""
        exit 1
        ;;
esac 