<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka_Test
 **/

/**
 * 
 *
 * @package Omeka
 * @copyright Center for History and New Media, 2009
 **/
class Core_OmekaCoreTest extends PHPUnit_Framework_TestCase
{
    public function testCanCallPhasedLoadingInTheOldWay()
    {
        $this->assertNull(Omeka_Context::getInstance()->getDb());
        
        $core = new Omeka_Core;
        $core->phasedLoading('initializeDb');
        
        $db = Omeka_Context::getInstance()->getDb();
        $this->assertEquals('Omeka_Db', get_class($db));
    }
    
    public function tearDown()
    {
        Omeka_Context::resetInstance();
    }
}
