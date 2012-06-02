# JavaProperties.php

Parse Java properties files in PHP.

This class allows you to parse Java properties files or strings in PHP.
It should be completely compliant with
[the parsing rules of java.util.Properties](http://docs.oracle.com/javase/7/docs/api/java/util/Properties.html#load(java.io.Reader).

## INTERFACE
```php
class \Classmarkets\JavaProperties implements \ArrayAccess {
    void loadResource($url, $streamContext = null);
    void loadString($string);

    array getAll();
}
```

## SYNOPSIS
```php
$properties = new Classmarkets\JavaProperties;
    
$properties->loadString("foo: bar");
// OR: $properties->loadResource("http://mysite/legacy/app.properties");
    
var_export($properties->getAll());
var_export($properties['foo']);
```

yields:

    array (
      'foo' => 'bar',
    )

`loadResource` accepts any URL for which there is a [supported protocol wrapper](http://php.net/manual/en/wrappers.php), including of course you're own registered [streamWrappers](http://php.net/manual/en/class.streamwrapper.php). It takes a [stream context](http://php.net/manual/en/stream.contexts.php) as an optional second argument.

## REQUIREMENTS

* PHP has to be compiled `--with-pcre-regex`
* `allow_url_fopen = on` for network streams. This is implied by fopen. Refer to [the docs](http://php.net/manual/en/function.fopen.php) for details.

## KNOWN LIMITATIONS

* Escaped key-value-delimiters are not supported, e. g. `foo\:bar = baz` will _not_ result in `[ 'foo:bar' => 'baz' ]`, but `[ 'foo\' => 'bar = baz' ]`. 
* Lines ending with multiple backslashes are not handled properly. They are treated as if they ended with exactly one backslash.

Patches welcome :)