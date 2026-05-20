<?php
return [
  'database' => [
    'host' => 'localhost',
    'port' => '',
    'charset' => NULL,
    'dbname' => 'espocrm',
    'user' => 'espocrm',
    'password' => 'ROOT',
    'platform' => 'Mysql'
  ],
  'smtpPassword' => NULL,
  'logger' => [
    'path' => 'data/logs/espo.log',
    'level' => 'WARNING',
    'rotation' => true,
    'maxFileNumber' => 30,
    'printTrace' => false,
    'databaseHandler' => false,
    'sql' => false,
    'sqlFailed' => false
  ],
  'restrictedMode' => false,
  'cleanupAppLog' => true,
  'cleanupAppLogPeriod' => '30 days',
  'webSocketMessager' => 'ZeroMQ',
  'clientSecurityHeadersDisabled' => false,
  'clientCspDisabled' => false,
  'clientCspScriptSourceList' => [
    0 => 'https://maps.googleapis.com'
  ],
  'adminUpgradeDisabled' => false,
  'isInstalled' => true,
  'microtimeInternal' => 1778746394.994444,
  'cryptKey' => 'ed385b38f6f6a548ea405e2843eb934b',
  'hashSecretKey' => '30399af1e8989048c4bae3c461a2c083',
  'actualDatabaseType' => 'mysql',
  'actualDatabaseVersion' => '8.0.45',
  'instanceId' => 'd0a3b010-6662-45a7-a230-3d0fade5b1fe',
  'apiSecretKeys' => (object) []
];
