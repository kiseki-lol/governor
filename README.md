# governor
Implements the Aya governor protocol, allowing for independent server discovery while retaining responsible user safety measures

Also known as the "Master Server".

## Server Installation

1. Install PHP. After the install, it must be in your Path/you must be able to run it from the command line.
2. Turn on the pdo_sqlite extension in php.ini in your PHP installation
3. Install [Composer](https://getcomposer.org/)
4. Copy migrations/migrations.sql to aya.db
5. Put your own BaseUrl in config/base.php (or set it to localhost:[whatever port you'll use])
6. Open a new console
7. Run `composer install`. This should install the dependencies required for running this server.
8. Start the php server. Here's an example of a command that you can use to start a server: `php -S 127.0.0.1:1337 index.php`. Feel free to change the port, it isn't hardcoded into Aya.
