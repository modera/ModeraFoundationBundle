# ModeraFoundationBundle [![Build Status](https://travis-ci.org/modera/ModeraFoundationBundle.svg?branch=master)](https://travis-ci.org/modera/ModeraFoundationBundle)

Bundle ships some very basic utility classes

## Installation

Add this dependency to your composer.json:

    "modera/foundation-bundle": "dev-master"

Update your AppKernel class and add ModeraFoundationBundle declaration there:

    new Modera\FoundationBundle\ModeraFoundationBundle(),

## Documentation

### Functional testing

Modera\FoundationBundle\FunctionalTestCase allow you to increase speed of test writing by:
- kernel boot (this actual done by overriding and running static::createClient())
- entity  manager creation, 
- database create/delete/clear

#### Predefined variables

- **$keepDatabase**. If true - test will reuse existing tables, if they are exists. Increase test rerun speed during development. 
- **$useTransaction**. If true - all database activity will be wrapped inside transaction. Db clearing speed increasing. Good for the CI and single runs.
- **$client**. Predefined client.
- **$container**. Container of currently booted kernel.
- **$em**. Entity manager of current kernel.
- **$st**. Schema tool of current kernel.

Please, do not reboot(create new) kernel. This can lead to unexpected entity manager behavior.  

#### Test case init

This test case introduce new test case init. All preparation(client creation, entity manager init, db preparing, transaction start) are done in *init* method. If you need to do something before that use *preInit* method. If you need to access some services or other symfony internals, init some components after symfony init override *postInit* method. 

#### Test flow routines

##### Isolation

There is two isolation levels present. Method and Class.
Override **getIsolationLevel()** to alter.
 
- **Class isolation level**. This means that database will be created/cleared and kernel will be booted only once. Before all tests. And every next test will works based on result of previous test. Usable if you want to reuse user auth, for example.  

- **Method isolation level**. DB will be cleared and kernel will be booted before every test.

##### Tables creation

Most functional test sooner or later will start using databases.
FunctionalTestCase allow you to simplify this procedure.

override **getDatabaseTableList** method. This method should return array
of used entities ClassMetadata.

##### Tables reuse

To keep and reuse database tables override 

    static $keepDatabase = true;
    
##### Use transaction instead tables clearing

    static $useTransaction = true;
    
##### Many to many entities clearing

In case of many to many connection simple one by one tables delete can lead to foreign key errors.
To avoid this situation use **preClear** method to resolve(delete) this entities.

##### Simple test case example


    class SimpleTestCase extends FunctionalTestCase
    {
        /**
         * @var SimpleService
         */
        static private $simpleService
    
        static public function postInit()
        {
           static::$simpleService = static::$container->get('simple.service');  
        }
        
        static protected function getDatabaseTableList()
        {
           return [
               static::$em->getClassMetadata('\Acme\AppBundle\Entity\SimpleEntity');
           ]
        }
        
        function testSimpleTestCase()
        {
             // do some test stuff here
        }
    }


#### Basic phpunit method override

List of method overrides.

- setUpBeforeClass. Override **doSetUpBeforeClass** template method to use setUpBeforeClass.
- tearDownAfterClass. Override **doTearDownAfterClass**
- setUp. Override **doSetUp**
- tearDown. Override **doTearDown**

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE

