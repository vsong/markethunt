# Markethunt
Markethunt is a web service that collects data from Mousehunt's in-game marketplace. It comes with a web app and API to access the data.

## Links
**Markethunt web app**: https://markethunt.win/

**Markethunt backend API**: https://api.markethunt.win/

**Mousehunt userscript**: https://greasyfork.org/en/scripts/441382-markethunt-plugin-for-mousehunt

**Database dumps**: https://cdn.markethunt.win/db_backups/

## Installation
To set up your own development instance of Markethunt, run `init.sh` on a linux machine with `docker` installed and at least 1GB RAM. The web app will be hosted on http://localhost:9002 and the API will be hosted on http://localhost:9001 . The MariaDB container will populate data from the latest DB dump.

The web app uses `webpack` to compile javascript, so a node container has been setup to avoid you needing to install node. The helper script `frontend/www-src/npm.sh` lets your run `npm` commands inside the node container conveniently, e.g. `./npm.sh run build`.