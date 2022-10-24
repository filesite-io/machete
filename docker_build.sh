#!/bin/sh
docker stop machete
docker rm machete
docker rmi filesite/machete
docker build --no-cache -t filesite/machete .
