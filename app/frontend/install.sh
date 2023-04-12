apt install -y php-mbstring
apt install -y php-ssh2
apt install -y php-curl
apt install -y php-mysqli

if [ -d $PWD/logs ];
then
	echo "La carpeta logs ya existe"
else
	mkdir logs
	chmod -R 777 logs
fi

if [ -d $PWD/biblioteca ];
then
	echo "La carpeta biblioteca ya existe"
else
	mkdir biblioteca
	chmod -R 777 biblioteca
fi

if [ -f $PWD/system/config/access.config.inc.php ];
then
	echo "Ya existe access.config.inc.php (No es necesario copiarlo)"
else
	cp -r system/config/access.config.inc.php.example system/config/access.config.inc.php
	chmod 777 system/config/access.config.inc.php
fi

if [ -f $PWD/system/config/config.inc.php ];
then
	echo "Ya existe config.inc.php (No es necesario copiarlo)"
else
	cp -r system/config/config.inc.php.example system/config/config.inc.php
	chmod 777 system/config/config.inc.php
fi

if [ -f $PWD/system/config/wsconfig.inc.php ];
then
	echo "Ya existe wsconfig.inc.php (No es necesario copiarlo)"
else
	cp -r system/config/wsconfig.inc.php.example system/config/wsconfig.inc.php
	chmod 777 system/config/wsconfig.inc.php
fi

echo "Enter user for MySql"
read usuarioMysql

mysql -u $usuarioMysql -p $passMysql < base/sql/completa.sql
