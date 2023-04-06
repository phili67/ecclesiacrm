FROM php:8.0.19-apache-buster
#FROM php:7.4.2-apache-buster

# Surpresses debconf complaints of trying to install apt packages interactively
# https://github.com/moby/moby/issues/4032#issuecomment-192327844

ARG DEBIAN_FRONTEND=noninteractive

# Update
RUN apt-get -y update --fix-missing

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# mise en place de nodejs + npm
RUN apt-get update && apt-get upgrade --no-install-recommends -qq -y
RUN apt-get install curl gnupg -y

RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - && \
    apt-get install -y nodejs

RUN node --version
RUN npm --version
RUN npm install -g npm

#RUN npm install

RUN rm -rf /var/lib/apt/lists/*


RUN apt-get update && apt-get install -y vim


# Set the locale
#RUN apt-get clean && apt-get update && apt-get install -y locales
RUN apt-get install -y locales locales-all
ENV LC_ALL fr_FR.UTF-8
ENV LANG fr_FR.UTF-8
ENV LANGUAGE fr_FR.UTF-8
