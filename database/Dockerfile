FROM mariadb:10.5

RUN echo "[mariadb]" >> /etc/mysql/conf.d/config.cnf
RUN echo "innodb_buffer_pool_size = 33554432" >> /etc/mysql/conf.d/config.cnf

RUN apt update
RUN apt install wget -y
RUN wget https://cdn.markethunt.win/db_backups/markethunt_latest.sql.zst -O /docker-entrypoint-initdb.d/init.sql.zst

ENV MARIADB_DATABASE=markethunt
ENV MARIADB_USER=appdbuser
ENV MARIADB_PASSWORD=app
ENV MARIADB_ROOT_PASSWORD=admin
