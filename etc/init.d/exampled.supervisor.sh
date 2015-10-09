#! /bin/sh

# Installation
# - Move this to /etc/init.d/myservice
# - chmod +x this
#
# Starting and stopping
# - Start: `service myservice start` or `/etc/init.d/myservice start`
# - Stop: `service myservice stop` or `/etc/init.d/myservice stop`

#ref http://till.klampaeckel.de/blog/archives/94-start-stop-daemon,-Gearman-and-a-little-PHP.html
#ref http://unix.stackexchange.com/questions/85033/use-start-stop-daemon-for-a-php-server/85570#85570
#ref http://serverfault.com/questions/229759/launching-a-php-daemon-from-an-lsb-init-script-w-start-stop-daemon

NAME=exampled
DESC="Daemon for my magnificent PHP CLI script"
PIDFILE="/var/run/${NAME}.pid"
LOGFILE="/var/log/${NAME}.log"

DAEMON="/php-project/exampled.php"
DAEMON_OPTS=""

SUPERVISOR="/usr/bin/supervisorctl"

START_OPTS="start ${NAME}"
STOP_OPTS="stop ${NAME}"
STATUS_OPTS="status ${NAME}"

test -x $DAEMON || exit 0

set -e

case "$1" in
    status)
        echo "Status ${DESC}: "
        $SUPERVISOR $STATUS_OPTS
        echo $?
        ;;
    start)
        echo "Starting ${DESC}: "
        $SUPERVISOR $START_OPTS >> $LOGFILE
        echo "$NAME."
        ;;
    stop)
        echo "Stopping $DESC: "
        $SUPERVISOR $STOP_OPTS
        echo "$NAME."
        rm -f $PIDFILE
        ;;
    restart|force-reload)
        echo "Restarting $DESC: "
        $SUPERVISOR $STOP_OPTS
        sleep 1
        $SUPERVISOR $START_OPTS >> $LOGFILE
        echo "$NAME."
        ;;
    *)
        N=/etc/init.d/$NAME
        echo "Usage: $N {start|stop|restart|force-reload|status}" >&2
        exit 1
        ;;
esac

exit 0
