#!/bin/bash

docker exec -it --workdir /app/www-src markethunt-node-dev npm "$@"