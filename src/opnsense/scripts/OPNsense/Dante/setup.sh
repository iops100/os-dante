#!/bin/sh

AGENT_DIRS="/usr/local/etc/Dante"

for directory in ${AGENT_DIRS}; do
    mkdir -p ${directory}
    chown -R proxy:proxy ${directory}
    chmod -R 770 ${directory}
done

#We add our startup script
cp /usr/local/opnsense/scripts/OPNsense/Dante/sockd /usr/local/etc/rc.d/

exit 0
