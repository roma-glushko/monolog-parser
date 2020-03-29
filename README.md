Monolog Parser
==============

A package for parsing [monolog](https://github.com/Seldaek/monolog) records including multiline support.

## Installation

```bash
composer require roma-glushko/monolog-parser
```

## Usage

```php
require_once 'path/to/vendor/autoload.php';
  
use MonologParser\Reader\LogReader;
    
$logFile = '/path/to/some/monolog.log';
$reader = new LogReader($logFile);
   
foreach ($reader as $i => $log) {
    echo sprintf(
      "The #%s log entry was written at %s. \n", 
      $i, 
      $log['date']->format('Y-m-d h:i:s')
    );
}
    
$lastLine = $reader[count($reader)-1];
echo sprintf(
  "The last log entry was written at %s. \n", 
  $lastLine['date']->format('Y-m-d h:i:s')
);

```

## Credits

This project is derived from [pulse00/monolog-parser](https://github.com/pulse00/monolog-parser) which is pretty cool but seems to be not actively supported and misses record mulitline support
