#!/bin/bash

/usr/bin/sudo /usr/bin/apt-get -q -y update
/usr/bin/sudo /usr/bin/apt-get -q -y install php5-cli
/usr/bin/sudo /usr/bin/apt-get -q -y htop

/usr/bin/curl -sS https://getcomposer.org/installer | /usr/bin/sudo /usr/bin/php -- --install-dir=/usr/bin/ --filename=composer
