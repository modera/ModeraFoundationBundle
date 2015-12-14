<?php

namespace Modera\FoundationBundle\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Alex Plaksin <alex.plaksin@modera.net>
 * @copyright 2015 Modera Foundation
 */
class FunctionalTestCase extends WebTestCase
{

    const IM_METHOD = 'method';
    const IM_CLASS = 'class';

    /**
     * Database will be not deleted after tests execution.
     *
     * Also this will mean that if database tables exists, they will not be recreated.
     *
     * @var bool
     */
    static public $keepDatabase = false;

    /**
     * Tests will use transactions to aviod database clearance.
     *
     * @var bool
     */
    static public $useTransaction = true;

    /**
     * @var Client
     */
    static protected $client;

    /**
     * @var ContainerInterface
     */
    static protected $container;

    /**
     * @var EntityManager
     */
    static protected $em;

    /**
     * @var SchemaTool
     */
    static protected $st;

    static public function setUpBeforeClass()
    {
        if (static::getIsolationLevel() == self::IM_CLASS) {
            static::init();
        }
        static::doSetUpBeforeClass();
    }

    public function setUp()
    {
        if (static::getIsolationLevel() == self::IM_METHOD) {
            static::init();
        }

        $this->doSetUp();
    }

    public function tearDown()
    {
        if (static::getIsolationLevel() == self::IM_METHOD) {
            static::clear();
        }

        $this->doTearDown();
    }

    static public function tearDownAfterClass()
    {
        if (static::getIsolationLevel() == self::IM_CLASS) {
            static::clear();
        }

        static::doTearDownAfterClass();
    }

    static function clear()
    {
        static::preClear();
        if (static::$keepDatabase) {
            if (static::$useTransaction) {
                static::rollbackTransaction();
            } else {
                static::clearTables(static::getDatabaseTableList());
            }
        } else {
            if (static::$useTransaction) {
                static::rollbackTransaction();
            } else {
                static::dropTables(static::getDatabaseTableList());
            }
        }

        static::$kernel = null;
        static::$em = null;
        static::$container = null;
        static::$client = null;

        static::postClear();
    }

    /**
     * Create tables.
     *
     * @param ClassMetadata[] $entitiesMetadata
     */
    static protected function createTables(array $entitiesMetadata)
    {
        static::$st->createSchema($entitiesMetadata);
    }

    /**
     * Drop tables.
     *
     * @param ClassMetadata[] $entitiesMetadata
     */
    static protected function dropTables(array $entitiesMetadata)
    {
        static::$st->dropSchema($entitiesMetadata);
    }

    /**
     * Methods return all tables metadata that used in this test and its children
     *
     * Template method.
     * Update this list if you need to add/remove db tables for this tests.
     *
     * @return ClassMetadata[]
     */
    static protected function getDatabaseTableList()
    {
        return [];
    }

    /**
     * Get this test tables names
     *
     * @return string[]
     */
    static protected function getDatabaseTableListNames()
    {
        $dbNames = [];

        foreach(static::getDatabaseTableList() as $classMetaData) {
            $dbNames[] = $classMetaData->getTableName();
        }

        return $dbNames;
    }

    static protected function emExists()
    {
        return static::$container->has('doctrine.orm.entity_manager');
    }

    /**
     * Override this method to change isolation level of the test.
     *
     * @return string
     */
    static protected function getIsolationLevel()
    {
        return self::IM_METHOD;
    }

    static protected function rollbackTransaction()
    {
        if (static::$container && static::emExists()) {
            $c = static::$em->getConnection();
            // having this check if there's an active transaction will let us
            // to use this TC as parent-class for integration test cases
            // even if they don't use EM
            if ($c->isTransactionActive()) {
                $c->rollback();
            }}
    }

    /**
     * Check if database exists.
     *
     * @return boolean
     */
    static protected function doDatabaseTablesExists()
    {
        $schemaManager = self::$em->getConnection()->getSchemaManager();
        return $schemaManager->tablesExist(self::getDatabaseTableListNames());
    }

    static protected function makeDatabaseCreateDecision()
    {
        if ( !(static::doDatabaseTablesExists() == true && static::$keepDatabase) ) {
            static::dropTables(static::getDatabaseTableList());
            static::createTables(static::getDatabaseTableList());
        }
    }

    static protected function makeTransactionStartDecision()
    {
        if (static::$useTransaction) {
            static::$em->getConnection()->beginTransaction();
        }
    }

    /**
     *
     */
    static protected function init()
    {

        static::preInit();

        static::$client = static::createClient();

        static::$container = static::$kernel->getContainer();

        if (self::emExists()) {
            static::$em = static::$container->get('doctrine.orm.entity_manager');
        }

        static::$st = new SchemaTool(self::$em);

        static::makeDatabaseCreateDecision();
        static::clearTables(static::getDatabaseTableList());
        static::makeTransactionStartDecision();

        static::postInit();
    }

    /**
     * Template method.
     *
     * If you need to execute any actions before static::init()
     */
    static protected function preInit() {}

    /**
     * Template method.
     *
     * If you need to execute any actions after static::init()
     */
    static protected function postInit() {}

    /**
     * Template method.
     *
     * If you need to execute something before clearing/deleting tables
     */
    static protected function preClear() {}

    /**
     * Template method.
     *
     * If you need to execute something after clearing/deleting tables
     */
    static protected function postClear() {}

    /**
     * Template method to implement preClearTables
     */
    static protected function preClearTables() {}

    /**
     * Template method that executed after static::setUpBeforeClass
     */
    static public function doSetupBeforeClass() {}

    /**
     * Template method that executed after static::tearDownAfterClass
     */
    static public function doTearDownAfterClass() {}

    /**
     * Template method that executed after $this->setUp
     */
    public function doSetUp() {}

    /**
     * Template method that executed after $this->tearDown
     */
    public function doTearDown() {}

    /**
     * Delete all data from tables.
     *
     * @param ClassMetadata[] $entitiesMetadata
     */
    static protected function clearTables(array $entitiesMetadata)
    {
        static::preClearTables();
        foreach ($entitiesMetadata as $entityMetadata) {
            /** var ClassMetadata $classMetaData */
            $query = static::$em->createQuery("DELETE FROM ". $entityMetadata->rootEntityName);
            $query->execute();
        }
    }

    /**
     * Overide of create client from WebTestCase
     *
     * Main thing is that static::bootKernel will create new kernel in static::$kernel
     * So all that depeneds on static::$kernel will start depends on new version of kernel
     *
     * @param array $options
     * @param array $server
     *
     * @return Client
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        if (!static::$client) {
            static::$client = parent::createClient($options, $server);
        }

        static::$client->setServerParameters($server);

        return static::$client;
    }
}