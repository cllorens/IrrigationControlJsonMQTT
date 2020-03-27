FROM php:7.3-apache

# Copy data for add-on
COPY 000-default.conf /etc/apache2/sites-enabled
COPY /web /var/www/html

COPY run.sh /home
RUN chmod a+x /home/run.sh

CMD /home/run.sh
