#!/usr/bin/env bash

IMAGE_BUILD_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ")
HOSTNAME=$(hostname)

cat > public/version.txt << EOF
{
  "image_build_date":"$IMAGE_BUILD_DATE",
  "branch":"$BRANCH",
  "revision": "$COMMIT",
  "deployed_by":"$COMMITTER",
  "built_on_host":"$HOSTNAME"  
}
EOF
