## ResourceContracts.org Dockerfile

This repository contains the Dockerfile of [ResouceContracts.org](http://github.com/NRGI/resourcecontracts.org/) for Docker.

### Base Docker Image

Ubuntu 24.04 LTS with PHP 8.4 (ondrej/php PPA)

### Installation

1. Install [Docker](https://www.docker.com/).
2. Clone this repo `git clone https://github.com/NRGI/resourcecontracts.org.git`
3. Build an image from the Dockerfile: `docker build -t rc-admin .`
4. Provide the variables listed in `conf/env.template` as container environment variables (ECS task definition or `--env-file`)

### Usage

* Run `docker run -p 80:80 --env-file rc-admin.env -d rc-admin`
* Access the system from the browser at http://localhost/

### Notes

* Queue workers and the scheduler run as `www-data` (supervisor / cron); Apache runs as usual.
* `/shared_path` holds `storage/` and `public/data`; mount it as a volume to keep uploads and logs across deployments.