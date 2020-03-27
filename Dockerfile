FROM php:7.3-apache

# Copy data for add-on
COPY 000-default.conf /etc/apache2/sites-enabled
COPY /web /var/www/html

COPY run.sh /
RUN chmod a+x /run.sh

EXPOSE 7777

CMD [ "/run.sh" ]
