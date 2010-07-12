<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 */

/**
 * All database migration classes inherit from this one.
 *
 * While not required, a down() method must be declared within a concrete
 * subclass in order to allow for reversal of database migrations.
 * 
 * @internal This is a pseudo-port of Ruby on Rails' migrations.
 * @package Omeka
 * @copyright Center for History and New Media, 2007-2010
 */
abstract class Omeka_Db_Migration
{
    /**
     * @param Omeka_Db $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * @return Omeka_Db
     */
    public function getDb()
    {
        return $this->db;
    } 
    
    /**
     * Proxy calls to Omeka_Db.
     *
     * Allows migration writers to call db methods directly on $this.
     *
     * @param string $m Method name.
     * @param array $a Method arguments.
     */
    public function __call($m, $a)
    {
        return call_user_func_array(array($this->getDb(), $m), $a);
    }
    
    /**
     * Migrate up (the normal migration).
     */
    abstract public function up();
    
    /**
     * If the migration requires a form submission, here's where to handle display of it
     * 
     * @return void
     */
    public function form() {}
}
