#!/bin/bash

# Configure Composer to use HTTPS instead of SSH for GitHub
echo "Configuring Composer to use HTTPS for GitHub repositories..."

# Set GitHub protocol to HTTPS
composer config --global github-protocols https

# Disable SSH for GitHub
composer config --global secure-http true

# Configure GitHub OAuth if token is available
if [ ! -z "$GITHUB_TOKEN" ]; then
    echo "Setting up GitHub OAuth token..."
    composer config --global github-oauth.github.com $GITHUB_TOKEN
fi

# Force HTTPS for specific problematic repositories
composer config --global repositories.twilio-php vcs https://github.com/twilio/twilio-php.git
composer config --global repositories.stripe-php vcs https://github.com/stripe/stripe-php.git
composer config --global repositories.laravel-paystack vcs https://github.com/unicodeveloper/laravel-paystack.git

# Set timeout to handle slow connections
composer config --global process-timeout 600

echo "Composer configuration updated successfully!"
echo "Current GitHub protocol settings:"
composer config --global github-protocols 