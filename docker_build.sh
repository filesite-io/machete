#!/bin/sh
docker stop machete
docker rm machete
docker rmi filesite/machete

## build filesite/machete:latest
docker build --no-cache -t filesite/machete -f ./Dockerfile .

## build filesite/machete:samba
##docker build --no-cache -t filesite/machete:samba -f ./Dockerfile_samba .
