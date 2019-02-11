#!/bin/bash

# copyright Philippe Logel 2016

# If /root/.my.cnf exists then it won't ask for root password
if [ -f /root/.my.cnf ]; then
  echo "Please enter the NAME of the new EcclesiaCRM database! (example: database1)"
  read dbname
  echo "Please enter the EcclesiaCRM database CHARACTER SET! (example: latin1, utf8, ...)"
  read charset
  echo "Creating new EcclesiaCRM database..."
  mysql -e "CREATE DATABASE ${dbname} /*\!40100 DEFAULT CHARACTER SET ${charset} */;"
  echo "Database successfully created!"
  echo "Showing existing databases..."
  mysql -e "show databases;"
  echo ""
  echo "Please enter the NAME of the new EcclesiaCRM database user! (example: user1)"
  read username
  echo "Please enter the PASSWORD for the new EcclesiaCRM database user!"
  read userpass
  echo "Creating new user..."
  mysql -e "CREATE USER ${username}@localhost IDENTIFIED BY '${userpass}';"
  echo "User successfully created!"
  echo ""
  echo "Granting ALL privileges on ${dbname} to ${username}!"
  #mysql -e "GRANT ALL PRIVILEGES ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -e "GRANT SELECT ON ${dbname} TO '${username}'@'localhost';"
  mysql -e "GRANT INSERT ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -e "GRANT UPDATE ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -e "GRANT SELECT ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -e "GRANT DELETE ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -e "GRANT CREATE ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -e "GRANT DROP ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -e "GRANT ALTER ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -e "FLUSH PRIVILEGES;"
  echo "You're good now :)"
  exit
  
# If /root/.my.cnf doesn't exist then it'll ask for root password  
else
  echo "Please enter root user MySQL password!"
  read rootpasswd
  echo "Please enter the NAME of the new EcclesiaCRM database! (example: database1)"
  read dbname
  echo "Please enter the EcclesiaCRM database CHARACTER SET! (example: latin1, utf8, ...)"
  read charset
  echo "Creating new EcclesiaCRM database..."
  mysql -uroot -p${rootpasswd} -e "CREATE DATABASE ${dbname} /*\!40100 DEFAULT CHARACTER SET ${charset} */;"
  echo "Database successfully created!"
  echo "Showing existing databases..."
  mysql -uroot -p${rootpasswd} -e "show databases;"
  echo ""
  echo "Please enter the NAME of the new EcclesiaCRM database user! (example: user1)"
  read username
  echo "Please enter the PASSWORD for the new EcclesiaCRM database user!"
  read userpass
  echo "Creating new user..."
  mysql -uroot -p${rootpasswd} -e "CREATE USER ${username}@localhost IDENTIFIED BY '${userpass}';"
  echo "User successfully created!"
  echo ""
  echo "Granting ALL privileges on ${dbname} to ${username}!"
  mysql -uroot -p${rootpasswd} -e "GRANT ALL PRIVILEGES ON ${dbname}.* TO '${username}'@'localhost';"
  
  echo "Ok with all privileges"
  exit
# with minor pirvileges
  #mysql -uroot -p${rootpasswd} -e "GRANT USAGE ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "GRANT SELECT ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "GRANT INSERT ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "GRANT UPDATE ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "GRANT SELECT ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "GRANT DELETE ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "GRANT CREATE ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "GRANT DROP ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "GRANT ALTER ON ${dbname}.* TO '${username}'@'localhost';"
  mysql -uroot -p${rootpasswd} -e "FLUSH PRIVILEGES;"
  echo "You're good now :)"
  exit
fi