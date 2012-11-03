#!/bin/sh

rm -rf extlib
mkdir -p extlib

# php-lib-remote-rs
(
cd extlib
git clone https://github.com/fkooman/php-lib-remote-rs.git
)
