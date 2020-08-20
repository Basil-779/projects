FROM python:3.8.2-buster
ENV PYTHONUNBUFFERED 1
RUN apt-get update && apt-get -y install ffmpeg && apt-get -y install python-libtorrent && mkdir /code
#apache2 libapache2-mod-wsgi-py3
WORKDIR /code
COPY req.txt /code/
#COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN pip3 install -r req.txt
COPY . /code/
RUN python manage.py collectstatic
RUN chmod -R 777 /code
RUN python manage.py runmodwsgi --setup-only --port=8000 --user www-data --group www-data --server-root=/etc/mod_wsgi-express-80
RUN cat /code/httpd.conf >> /etc/mod_wsgi-express-80/httpd.conf
RUN sed -i '/LC_ALL/d' /etc/mod_wsgi-express-80/apachectl; /etc/mod_wsgi-express-80/apachectl start
CMD ["/etc/mod_wsgi-express-80/apachectl","start","-D", "FOREGROUND"]
#RUN service apache2 restart