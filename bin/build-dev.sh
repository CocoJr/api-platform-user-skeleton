#!/bin/bash

CIDR="$( pwd )"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

cd $DIR/../

composer install
mkdir -p config/jwt
if [[ ! -f config/jwt/private.pem ]]
then
    openssl genrsa -passout env:JWT_PASSPHRASE -out config/jwt/private.pem -aes256 4096
    openssl rsa -passin env:JWT_PASSPHRASE -pubout -in config/jwt/private.pem -out config/jwt/public.pem
    chmod 777 config/jwt/*.pem
fi
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migration:migrate -n
php bin/console hautelook:fixtures:load -n
chmod 777 var/cache/ -R

cd $CDIR
