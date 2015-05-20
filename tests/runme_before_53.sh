sudo apt-get update
sudo apt-get install apache2 libapache2-mod-fastcgi
#enable php-fpm
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm
#configure apache virtual hosts
sudo cp -f $TRAVIS_BUILD_DIR/build/travis-ci-apache /etc/apache2/sites-available/default
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/default
echo 'date.timezone = "Europe/Rome"' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
echo 'Listen 8000' | sudo tee --append /etc/apache2/ports.conf
sudo service apache2 restart