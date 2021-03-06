#!/usr/bin/python3

import time
import socket
import json

MASTER_PREFIX = "MD3"
SLAVE_PREFIX = "SD3"

def send_command(ip, port):
    try:
        s = socket.create_connection((ip, port)) # faire un systeme avec fichier de conf
        obj = {
            "packet_type": "monitor_ip"
        };
        obj_str = json.JSONEncoder().encode(obj);
        print("[ CRON REFRESH NETWORK ]: Sending " + obj_str + " to " + ip + ":" + str(port))
        s.send(obj_str.encode())
        s.close()
    except Exception as e:
        print("[ CRON REFRESH NETWORK ]: No connection available. Trying again in 15 min")

if __name__ == "__main__":
    hostname = socket.gethostname()
    if MASTER_PREFIX in hostname:
        send_command('127.0.0.1', 4224)
    elif SLAVE_PREFIX in hostname:
        send_command('127.0.0.1', 4243)
