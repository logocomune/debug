# Debug tool
This software is a simple debugging tool which prints human-readable information about a variable in HTML or pure text if the execution is from CLI mode.  

## Install

Install the latest version with `composer require logocomune/debug`

Alternatively, you can specify Debug as a dependency in your project’s existing composer.json file:
```json
{
 "require-dev": {
    "logocomune/debug": "~1.1"
 }
}
```

## Usage
Basic example:
```php
// debug() or d() just display a variable
debug(['test'=>1,'d'=>'ok']);

// debug and exit
de(['test'=>1,'d'=>'ok']);
```

Available features:
```php
// Disable debug
\Logocomune\Debug\Debug::disable();

// Enable backtrace
\Logocomune\Debug::backtrace();

// Disable backtrace
\Logocomune\Debug::backtraceOff();

// Dump a variable with

// print_r (default mode)
\Logocomune\Debug::renderAsPrintR();

// var_dump
\Logocomune\Debug::renderAsVarDump();


// var_export
\Logocomune\Debug::renderAsVarExport();


// Symfony mechanism for exploring and dumping PHP variables
\Logocomune\Debug::renderAsSymVarDump();


```
