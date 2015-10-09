# Simple daemon

## What is this project about

You have background tasks for your project?
You want to code them in PHP?
You need a simple framework to attach your tasks to?

Please try out my wrapper for your daemon tasks.

## How to use it

That is quite simple, have a look at the example daemon to get an idea what you need to implement.

Integrate it via composer into project

    composer require firegate666/daemon

Create your own daemon handler and daemon handler factory be implementing `HandlerInterface` and `HandlerInterfaceFactory`.
Setup your own startup php script, see `exampled.php`

### setup with start-stop-daemon

Copy your own init script to `/etc/init.d/`, see `etc/init.d/exampled.daemon.sh`.

### setup with supervisor

Download and install [Supervisord](http://supervisord.org/). On Ubuntu or similar it is quite easy to use `apt-get install`.
Copy your supervisor configuration to `/etc/supervisor/conf.d/`, see `etc/supervisor/conf.d/exampled.conf`.
Copy your own init script to `/etc/init.d/`, see `etc/init.d/exampled.supervisor.sh`.

### control it

Now you can control your daemon

    /etc/init.d/exampled start
    /etc/init.d/exampled stop
    /etc/init.d/exampled restart
    /etc/init.d/exampled status    

## How does this thing work

Via a command line sript you can start up the daemon, see `exampled.php`. Write your own handler and factory and inject them into the daemon.
When the daemon starts up, he forks 4 child processes with your pre-defined handler. If one child goes down, a new one is started.
Each child process has its own handler instance and starts the run loop.

If the daemon itself receives a shutdown signal (SIGTERM, SIGINT), this signal is dispatched to his children and the daemon waits for their termination before he goes down himself.

## ToDos

- too many children died in short amount of time detection
- proper log messages and levels
- define guidelines for levels
