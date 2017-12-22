php bin/console doctrine:database:drop --force
php bin/console doctrine:generate:entities AppBundle
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force --complete --dump-sql
