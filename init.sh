#!/bin/bash

echo "init.sh: Using DB $DB_NAME with user $DB_USER on $POSTGRES_HOST:$POSTGRES_PORT"


cat << EOF > secrets.php
<?php
\$dbport = $POSTGRES_PORT;
\$dbhost = "$POSTGRES_HOST";
\$dbname = "$DB_NAME";
\$dbuser = "$DB_USER";
\$dbpass = "$DB_PASS";
EOF
