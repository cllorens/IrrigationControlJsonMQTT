FOLDER=/share/irrigationscheduler

mkdir -p $FOLDER

chmod -R 777 $FOLDER

php /var/www/html/irrigation.php >> $FOLDER/irrigation.log &

apache2-foreground
