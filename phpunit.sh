#!/usr/bin/env bash
echo "criando schema"
php bin/console --env=test doctrine:migration:migrate
echo "rodando testes"
./vendor/bin/simple-phpunit