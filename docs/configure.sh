#!/bin/sh
APP_NAME="php-oauth-example-rs"

INSTALL_DIR=`pwd`

# generate config files
(
cd config/
for DEFAULTS_FILE in `ls *.defaults`
do
    INI_FILE=`basename ${DEFAULTS_FILE} .defaults`
    if [ ! -f ${INI_FILE} ]
    then
        cat ${DEFAULTS_FILE} | sed "s|/PATH/TO/APP|${INSTALL_DIR}|g" > ${INI_FILE}
    fi
done
)
