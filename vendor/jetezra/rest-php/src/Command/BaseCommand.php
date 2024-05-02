<?php

namespace JetPhp\Command;

use JetPhp\Core\Base;
use JetPhp\Database\Connector;
use JetPhp\Exeptions\DatabaseException;
use PDO;
use Symfony\Component\Console\Command\Command;

/**
 * This is the base command class, it extends the Symfony console command class and provides some helper methods
 * that can be used in all commands. All commands should extend this class.
 * */
class BaseCommand extends Command
{

    /**
     * Return the base app, via this, you can access all the app settings, and current app environment
     *
     * @return Base
     */
    protected static function base(): Base
    {
        return new Base();
    }

    /**
     * Returns the current database connection
     *
     * @param string|null $db
     * @return PDO
     * @throws DatabaseException
     */
    protected static function connection(string | null $db = null): PDO
    {
        return Connector::connect($db);
    }
}
