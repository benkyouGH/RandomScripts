#!/bin/bash

# Lazy random password generator

length=${1:-16}
LC_ALL=C tr -dc 'a-zA-Z0-9!"#$%&'\''()*+,-./:;<=>?@[\]^_`{|}~' < /dev/urandom | head -c "$length"
