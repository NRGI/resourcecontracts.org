#!/usr/bin/env bash

# Triggers automatic deploy for certain branches.
#
# Uses Shippable env variables:
# - BRANCH
# - COMMIT

set -e

elementIn () {
  local e
  for e in "${@:2}"; do [[ "$e" == "$1" ]] && return 0; done
  return 1
}

# Automatic deploy allowed for these branches only.
DEPLOY_BRANCHES=("staging" "master")

if ! elementIn "$BRANCH" "${DEPLOY_BRANCHES[@]}" ;
then
  echo "Skiping deploy as branch is not allowed for automatic deploy"
fi

# Id of the Shippable project containing the deploy script.
DEPLOY_PROJECT_ID=5804f143e8fe021000f9aed1

# Trigger Shippable to run the deploy project and pass the current project name, branch and latest commit
STATUS=$(curl -s\
  -H "Authorization: apiToken $API_TOKEN"\
  -H "Content-Type: application/json"\
  -d "{\"branchName\":\"master\",\"globalEnv\": {\"PROJECT\":\"rc-admin\", \"PROJECT_BRANCH\":\"$BRANCH\", \"PROJECT_COMMIT\":\"$COMMIT\", \"PROJECT_REPO_FULL_NAME\":\"$REPO_FULL_NAME\", \"PROJECT_COMMITTER\":\"$COMMITTER\", \"PROJECT_COMPARE_URL\":\"$COMPARE_URL\" }}"\
  "https://api.shippable.com/projects/$DEPLOY_PROJECT_ID/newBuild")
echo "$STATUS"

if [[ "$STATUS" == *"runId"* ]]
then
  echo "Deploy triggered successfully";
  exit 0
else
  echo "Failed to trigger deploy.";
  exit 1
fi
