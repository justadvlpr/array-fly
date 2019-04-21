# ArrayFly

ArrayFly allows you to:

- Change an array value from a file.

- Get the array value from a file.

_This project is still a WIP, more features will be added soon._

# Installation

```
composer require vitorarantes/array-fly
```

# Usage

#### ArrayFly::File()

``` 
$file = new ArrayFly\File(
    'my-array-file.php'
);

echo $file->getValue('key1');
(output: I'm an old value)

$file
    ->setValue('key1', 'Change me')
    ->save();

echo $file->getValue('key1');
(output: Change me)
```