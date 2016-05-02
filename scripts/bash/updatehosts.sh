#!/bin/bash

DATE=$(date +%Y%m%d)

# Backup the old hosts file
sudo cp -p /etc/hosts /etc/hosts-$DATE

# Create a new one from users hosts file
sudo cp -p ~/.hosts /etc/hosts

# Add adblocking from http://winhelp2002.mvps.org
cd /tmp
wget http://winhelp2002.mvps.org/hosts.txt
sudo cat hosts.txt >> /etc/hosts


echo "[done]"
