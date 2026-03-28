#!/bin/bash

# Move to the project root
cd /home/site/wwwroot || {
  echo "[$(date)] Failed to change directory to /home/site/wwwroot"
  exit 1
}

# Redirect all output to persistent log (append)
exec >> /home/site/wwwroot/storage/logs/webjob-queue.log 2>&1

echo "[$(date)] Starting Laravel queue worker..."

# ----------------------
# Sanity Checks
# ----------------------
if [ -z "$APP_KEY" ]; then
  echo "[$(date)] APP_KEY missing. Exiting."
  exit 1
fi

# Redis check (Laravel context)
php artisan tinker --execute="Redis::ping();" >/dev/null 2>&1 || {
  echo "[$(date)] Redis not reachable. Exiting."
  exit 1
}

# ----------------------
# Start queue worker
# ----------------------
# Use all required queues in priority order: default, content-extraction, page-extraction
QUEUES="default,content-extraction,page-extraction"

# Infinite loop to ensure WebJob survives occasional failures
while true; do
  echo "[$(date)] Running queue worker for queues: $QUEUES"

  php artisan queue:work redis \
    --queue="$QUEUES" \
    --sleep=3 \
    --tries=3 \
    --timeout=90 \
    --max-jobs=100 \
    --max-time=3600

  EXIT_CODE=$?
  echo "[$(date)] Queue worker exited with code $EXIT_CODE. Restarting in 5 seconds..."
  sleep 5
done
