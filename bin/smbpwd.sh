#!/bin/sh

username=$1
password=$2

if [ -z $username ]; then
    user='filesite'
fi

if [ -z $password ]; then
    user='88888888'
fi

#sleep 3
smbpasswd -a $username<<EOF
$password
$password
EOF
