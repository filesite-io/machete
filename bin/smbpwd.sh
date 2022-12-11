#!/bin/sh

password=$1
username='filesite'

if [ -z $password ]; then
    user='88888888'
fi

#sleep 3
smbpasswd -a $username<<EOF
$password
$password
EOF
