This deployment is for Amazon Elasticbeanstalk only and assumes that you have setup application in EB dashboard. 

# Deployment Setup

## Existing system deployment

If the system is already setup and you are deploying to the existing AWS EB environment 

* Clone the project and checkout `deploy-example` branch
* Create new branch for deployment, say `deploy-stage`
* Update the credentials and Apis in the following files
    * [.env](../.env)
    * [00.environmentVariables.config](../.ebextensions/00.environmentVariables.config)
* Commit to the branch and next time you deploy it, just merge to-be-deployed branch to this branch and `eb deploy`.

## First time deployment

If you are deploying the system for the first time,

* Follow the above steps
* Update one more file
    * Update Abbyy crendentials in [.ebextensions/scripts/setup-pdfprocessor.sh](../.ebextensions/scripts/setup-pdfprocessor.sh)


# Deployment 

## Existing system deployment

* Use `eb deploy [environment-name]`

## First time deployment

* `eb deploy [environment-name]`
* scp scripts folder to the server and run the followings - see TODO below
    * `bash scripts/setup-beanstalk.sh`
    * `bash scripts/setup-supervisord.sh`
    * `bash scripts/setup-pdfprocessor.sh`

# TODO

* .env and environmentvariables need to integrate
* The first time deployment should also take care of the manual existing bash script running. However currently it compiles beanstalk and supervisord after their sources and it gives compile error, when tried from using `eb deploy`. 



