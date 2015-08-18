<?php

class DoctrineTools
{
    public function getDbConfig()
    {
        //An example configuration
        return array(
         'driver'   => 'pdo_mysql',
         'user'     => 'root',
         'password' => 'potatoes',
         'dbname'   => 'garden',
         'host'     => 'localhost',
         'charset' => 'utf8',
               'driverOptions' => array(
                  1002=>'SET NAMES utf8'
               )
        );
    }

    public function bootstrapDoctrine()
    {
        require_once($this->_libDir . DS . 'Doctrine/ORM/Tools/Setup.php');
        Doctrine\ORM\Tools\Setup::registerAutoloadDirectory('/full/path/to/lib');//So that Doctrine is in /full/path/to/lib/Doctrine
    }

    public function getEntityFolders()
    {
        //An example configuration of two entity folders
        return array(
         '/full/path/to/App/Module1/Entities/yml' => '\\App\\Module1\\Entities',
         '/full/path/to/App/Module2/Entities/yml' => '\\App\\Module2\\Entities'
        );
    }

    public function setupDoctrine()
    {
        $config = Doctrine\ORM\Tools\Setup::createConfiguration();
        $driver = new \Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver(getEntityFolders());
        $driver->setGlobalBasename('schema');
        $config->setMetadataDriverImpl($driver);

        $entityManager = \Doctrine\ORM\EntityManager::create($dbConfig, $config);
        return $entityManager;
    }

    public function getEntitiesMetaData($em)
    {
        $cmf = new Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);  // we must set the EntityManager

        $driver = $em->getConfiguration()->getMetadataDriverImpl();

        $classes = $driver->getAllClassNames();
        $metadata = array();
        foreach ($classes as $class) {
            //any unsupported table/schema could be handled here to exclude some classes
            if (true) {
                $metadata[] = $cmf->getMetadataFor($class);
            }
        }
        return $metadata;
    }

    public function generateEntities($rootDir, $metadata)
    {
        $generator = new Doctrine\ORM\Tools\EntityGenerator();
        $generator->setUpdateEntityIfExists(true);    // only update if class already exists
      //$generator->setRegenerateEntityIfExists(true);  // this will overwrite the existing classes
        $generator->setGenerateStubMethods(true);
        $generator->setGenerateAnnotations(true);
        $generator->generate($metadata, $rootDir);
    }

    public function generateDatabase()
    {
        $schema = new Doctrine\ORM\Tools\SchemaTool($em);
        $schema->createSchema($metadata);
    }
}
   //Sets up the Doctrine classes autoloader
   bootstrapDoctrine();
   //Sets up database connection, schema files (yml) and returns the EntityManager
   $em = setupDoctrine();
   //Returns the metadata specified by the two schema.orm.yml files
   $metadata = getEntitiesMetaData($em);
   /* Generates the classes based on the yml schema. Using the yml files in the example
    * the result will be the following files:
    *    /full/path/to/App/Module1/Entities/User.php
    *    /full/path/to/App/Module2/Entities/Comment.php
    *    /full/path/to/App/Module2/Entities/Page.php
    */
   generateEntities('/full/path/to', $metadata);
   //Now generate database tables:
   generateDatabase($metadata);
