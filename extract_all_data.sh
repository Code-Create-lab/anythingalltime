#!/bin/bash

# Extract all INSERT data from database.sql
echo "=== EXTRACTING ALL INSERT DATA FROM database.sql ==="
echo

SQL_FILE="/Users/darkness51/Developer/PHP/gogrocerbackend/source/database/dbdump/database.sql"

# List of all tables with INSERT data
tables=("aboutuspage" "admin" "app_link" "app_notice" "cancel_for" "country_code" "currency" "fcm" "firebase" "firebase_iso" "freedeliverycart" "id_types" "image_space" "map_api" "map_settings" "mapbox" "membership_plan" "minimum_maximum_order_value" "msg91" "payout_req_valid" "razorpay_key" "reedem_values" "referral_points" "reward_points" "roles" "settings" "smsby" "tax_types" "tbl_web_setting" "termspage" "twilio")

for table in "${tables[@]}"; do
    echo "=== TABLE: $table ==="
    grep -A 10 "INSERT INTO \`$table\`" "$SQL_FILE" | head -15
    echo
    echo "----------------------------------------"
    echo
done