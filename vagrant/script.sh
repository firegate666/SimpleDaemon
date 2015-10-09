#!/bin/bash

/usr/bin/sudo /usr/bin/apt-get -q -y update
/usr/bin/sudo /usr/bin/apt-get -q -y install php5-cli
/usr/bin/sudo /usr/bin/apt-get -q -y install htop
/usr/bin/sudo /usr/bin/apt-get -q -y install supervisor
/usr/bin/sudo /usr/bin/apt-get -q -y install vim

file="/usr/bin/composer"
if [ -f "$file" ]
then
	echo "$file found."
else
    /usr/bin/curl -sS https://getcomposer.org/installer | /usr/bin/sudo /usr/bin/php -- --install-dir=/usr/bin/ --filename=composer
fi
