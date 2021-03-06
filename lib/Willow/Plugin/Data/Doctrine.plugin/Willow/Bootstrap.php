<?php
/* $Id$ */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */


/**
 * Append include_path to include Doctrine models
 */
Willow_Loader::appendIncludePath(
    'Vendor:Doctrine:lib', 'App:Models', 'App:Models:generated', 'App:Repositories', 'App:Criteria', 'App:Services'
);

/**
 * Register Doctrine autoloader
 */
Willow_Autoloader::register(new Willow_Doctrine_Autoloader());

/**
 * Setup Doctrine attributes
 */
Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true);
Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);

/**
 * Get the config registered to the blackboard
 */
$config = Willow_Blackboard::get('config');

/**
 * Setup Doctrine connection(s)
 */
if (isset($config->db->connections) && ($config->db->connections !== null))
{
    foreach ($config->db->connections as $name => $connection)
    {
        Doctrine_Manager::connection($connection, $name);
    }

    if (!isset($config->db->connections->default) && isset($config->db->connections->master))
    {
        Doctrine_Manager::connection($config->db->connections->master, 'default');
    }
}
elseif (isset($config->db->connection) && ($config->db->connection !== null))
{
    Doctrine_Manager::connection($config->db->connection, 'default');
}

/**
 * Setup Doctrine collation & character set
 */
if (count(Doctrine_Manager::getInstance()->getConnections()) > 0)
{
    Doctrine_Manager::getInstance()->setCollate($config->db->collation);
    Doctrine_Manager::getInstance()->setCharset($config->db->charset);

    try
    {
        Doctrine_Manager::getInstance()->getConnection('default')->setCharset($config->db->charset);
    }
    catch (Doctrine_Manager_Exception $e)
    {
    }
}

/**
 * Configure Doctrine
 */
Willow_Whiteboard::register(
    'doctrine_config',
    array(
        'data_fixtures_path' => Willow_Loader::getRealPath('App:Doctrine:Data:Fixtures', false, false),
        'models_path'        => Willow_Loader::getRealPath('App:Models', false, false),
        'migrations_path'    => Willow_Loader::getRealPath('App:Doctrine:Migrations', false, false),
        'sql_path'           => Willow_Loader::getRealPath('App:Doctrine:Data:Sql', false, false),
        'yaml_schema_path'   => Willow_Loader::getRealPath('App:Doctrine:Schema', false, false),
    )
);

/**
 * Setup session handler
 */
Willow_Session::registerHandler(new Willow_Session_Handler_Doctrine());
