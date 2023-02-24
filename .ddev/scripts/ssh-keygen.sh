#!/bin/bash

echo "[FE] Init SSH-Server (self-deployment)"
sudo /etc/init.d/ssh start

echo "[FE] Checking SSH keypairs used for: Deployment to remote machine(s), GIT access"

privateKey=/var/www/html/deployment/ssh/id_rsa
publicKey=/var/www/html/deployment/ssh/id_rsa.pub

echo "     {$privateKey, $publicKey}"

if [ -f $publicKey ] && [ -f $privateKey ] ;
then
  echo "[FE] [-] SSH keypair exists."

  if [ $(stat -c %a $privateKey) != 600 ] ;
  then
    echo "[FE] [!] Permissions are too wide, adjusting to 0600."
    chmod 600 $privateKey
  else
    echo "[FE] [-] Permissions properly set to 0600."
  fi

  validation=`diff <(cut -d' ' -f 2 $publicKey) <(ssh-keygen -y -f $privateKey | cut -d' ' -f 2)`

  if [ -z "$validation" ] ;
  then
    echo "[FE] [-] SSH keypair is valid."

    echo "[FE] [i] PubKey for copy and paste: "
    cat $publicKey
    # TODO: Would be cool to implement this into `ddev describe`, but could not find a way
  else
    echo "[FE] [!!!] SSH keypair is invalid or mismatching. Please fix manually."
  fi

else
  echo "[FE] [!] SSH keypair does not exist. Will create dummy key."
  echo "         Either replace it, or use this one in github deployment keys"
  echo "         and authorized_keys on remote machines."
  echo "         ssh-keygen output follows:"

  ssh-keygen -b 4096 -N "" -t rsa -C "ddev-`date +%Y%m%d`-`whoami`@`hostname`" -f $privateKey

  echo "[FE] [i] PubKey for copy and paste: "
  cat $publicKey
fi

echo "[FE] Allow self-authorize"
cat $publicKey >> ~/.ssh/authorized_keys
