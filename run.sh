FOLDER=/share/irrigationscheduler

mkdir -p $FOLDER

chmod -R 777 $FOLDER
chmod 777 /data/options.json

php /var/www/html/irrigation.php >> $FOLDER/irrigation.log &

chmod 777 /data/options.json

apache2-foreground
