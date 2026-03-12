#!/bin/bash
# WOL script - sends Wake-on-LAN packet
python3 -c "
import socket
mac = '2c:f0:5d:7f:e3:2b'.replace(':', '').replace('-', '')
mac_bytes = bytes.fromhex(mac)
magic = b'\\xff' * 6 + mac_bytes * 16
s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
s.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST, 1)
s.sendto(magic, ('192.168.1.255', 9))
print('WOL sent')
"
