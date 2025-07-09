#!/bin/bash

# Usage ./htb-recon.sh [IP_ADDRESS] [output_filename]

IP="$1"
OUTPUT="$2"

# Check if IP was provided
if [ -z "$IP" ]; then
  echo "Usage: ./htb-recon.sh <IP> [output_filename]"
  exit 1
fi

echo "[*] Running full port scan on $IP"
# This should be obvious but don't do this in prod, you can't go that fast :)
PORTS=$(sudo nmap -p- --min-rate 10000 $IP | grep ^[0-9] | cut -d'/' -f1 | paste -sd, -)

if [ -z "$PORTS" ]; then
  echo "No ports found"
  exit 1
fi

echo "[*] Open ports: $PORTS"

echo "[*] Running nmap scan on open ports..."
if [ -n "$OUTPUT" ]; then
  sudo nmap -p "$PORTS" -sSCV -vv -oN "$OUTPUT" "$IP"
else
  sudo nmap -p "$PORTS" -sSCV -vv "$IP"
fi
