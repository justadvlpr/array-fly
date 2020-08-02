# ArrayFly

ArrayFly allows you to:

- Change an array value from a file.

- Get the array value from a file.

# Installation

```
composer require vitorarantes/array-fly
```

# Usage

``` 
<?php

use ArrayFly\ArrayFly;

$arrayFly = new ArrayFly('my-array-file.php');

echo $arrayFly->getValue('key1');
(output: value1)

$arrayFly->setValue('key1', 'value2')->save();

echo $arrayFly->getValue('key1');
(output: value1)
```