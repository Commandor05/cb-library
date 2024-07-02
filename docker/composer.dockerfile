FROM composer:2

ENV COMPOSERUSER=symfony
ENV COMPOSERGROUP=symfony

RUN adduser -g ${COMPOSERGROUP} -s /bin/sh -D ${COMPOSERUSER}

