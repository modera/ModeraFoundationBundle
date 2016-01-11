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
    public static $keepDatabase = false;

    /**
     * Tests will use transactions to aviod database clearance.
     *
     * @var bool
     */
    public static $useTransaction = true;

    /**
     * @var Client
     */
    protected static $client;

    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @var EntityManager
     */
    protected static $em;

    /**
     * @var SchemaTool
     */
    protected static $st;

    public static function setUpBeforeClass()
    {
        // Backward compatibility adding
        static::initSymfony();

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

    public static function tearDownAfterClass()
    {
        static::doTearDownAfterClass();

        if (static::getIsolationLevel() == self::IM_CLASS) {
            static::clear();
        }
    }

    public static function clear()
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
    protected static function createTables(array $entitiesMetadata)
    {
        static::$st->createSchema($entitiesMetadata);
    }

    /**
     * Drop tables.
     *
     * @param ClassMetadata[] $entitiesMetadata
     */
    protected static function dropTables(array $entitiesMetadata)
    {
        static::$st->dropSchema($entitiesMetadata);
    }

    /**
     * Methods return all tables metadata that used in this test and its children.
     *
     * Template method.
     * Update this list if you need to add/remove db tables for this tests.
     *
     * @return ClassMetadata[]
     */
    protected static function getDatabaseTableList()
    {
        return [];
    }

    /**
     * Get this test tables names.
     *
     * @return string[]
     */
    protected static function getDatabaseTableListNames()
    {
        $dbNames = [];

        foreach (static::getDatabaseTableList() as $classMetaData) {
            $dbNames[] = $classMetaData->getTableName();
        }

        return $dbNames;
    }

    protected static function emExists()
    {
        return static::$container->has('doctrine.orm.entity_manager');
    }

    /**
     * Override this method to change isolation level of the test.
     *
     * @return string
     */
    protected static function getIsolationLevel()
    {
        return self::IM_METHOD;
    }

    protected static function rollbackTransaction()
    {
        if (static::$container && static::emExists()) {
            $c = static::$em->getConnection();
            // having this check if there's an active transaction will let us
            // to use this TC as parent-class for integration test cases
            // even if they don't use EM
            if ($c->isTransactionActive()) {
                $c->rollback();
            }
        }
    }

    /**
     * Check if database exists.
     *
     * @return bool
     */
    protected static function doDatabaseTablesExists()
    {
        $schemaManager = self::$em->getConnection()->getSchemaManager();

        return $schemaManager->tablesExist(self::getDatabaseTableListNames());
    }

    protected static function makeDatabaseCreateDecision()
    {
        if (!(static::doDatabaseTablesExists() == true && static::$keepDatabase)) {
            static::dropTables(static::getDatabaseTableList());
            static::createTables(static::getDatabaseTableList());
        }
    }

    protected static function makeTransactionStartDecision()
    {
        if (static::$useTransaction) {
            static::$em->getConnection()->beginTransaction();
        }
    }

    protected static function initSymfony()
    {
        static::$client = static::createClient();

        static::$container = static::$kernel->getContainer();

        if (self::emExists()) {
            static::$em = static::$container->get('doctrine.orm.entity_manager');
        }
    }

    /**
     * Main before test/Class init method.
     */
    protected static function init()
    {
        static::preInit();

        static::initSymfony();

        static::$st = new SchemaTool(static::$em);

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
    protected static function preInit()
    {
    }

    /**
     * Template method.
     *
     * If you need to execute any actions after static::init()
     */
    protected static function postInit()
    {
    }

    /**
     * Template method.
     *
     * If you need to execute something before clearing/deleting tables
     */
    protected static function preClear()
    {
    }

    /**
     * Template method.
     *
     * If you need to execute something after clearing/deleting tables
     */
    protected static function postClear()
    {
    }

    /**
     * Template method to implement preClearTables.
     */
    protected static function preClearTables()
    {
    }

    /**
     * Template method that executed after static::setUpBeforeClass.
     */
    public static function doSetupBeforeClass()
    {
    }

    /**
     * Template method that executed after static::tearDownAfterClass.
     */
    public static function doTearDownAfterClass()
    {
    }

    /**
     * Template method that executed after $this->setUp.
     */
    public function doSetUp()
    {
    }

    /**
     * Template method that executed after $this->tearDown.
     */
    public function doTearDown()
    {
    }

    /**
     * Delete all data from tables.
     *
     * @param ClassMetadata[] $entitiesMetadata
     */
    protected static function clearTables(array $entitiesMetadata)
    {
        static::preClearTables();
        foreach ($entitiesMetadata as $entityMetadata) {
            /* var ClassMetadata $classMetaData */
            $query = static::$em->createQuery('DELETE FROM '.$entityMetadata->rootEntityName);
            $query->execute();
        }
    }

    /**
     * Overide of create client from WebTestCase.
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
