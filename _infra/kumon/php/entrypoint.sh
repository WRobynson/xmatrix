#!/bin/bash
set -e

# Inicia o cron em segundo plano
#cat /etc/web_user_pwd | sudo -S cron -f &

#   muda as permissões da pasta do usuário (web) do sistema
#cat /etc/web_user_pwd | sudo -S chown -R 1000:1000 /var/www/html/user
#cat /etc/web_user_pwd | sudo -S chmod -R 775 /var/www/html/user

#   muda as permissões da pasta de LOGs do sistema
#cat /etc/web_user_pwd | sudo -S chown -R 1000:1000 /var/www/log
#cat /etc/web_user_pwd | sudo -S chmod -R 775 /var/www/log

# Executar o comando original do Apache ou outra aplicação
exec "$@"
