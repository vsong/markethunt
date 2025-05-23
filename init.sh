#!/bin/bash

docker compose up -d

# set up api backend
docker exec -it markethunt-api-dev composer install -d /app 

# set up frontend
docker exec -it markethunt-frontend-dev composer install -d /app 
docker exec -it --workdir /app/www-src markethunt-node-dev bash -c "npm install && npm run build"

echo -e "\n\nSetup complete. Please wait a few minutes for the database to finish importing the prod dump file.\n"

