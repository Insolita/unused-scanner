# Project scanner for detect unused composer dependencies

[![Build Status](https://travis-ci.org/Insolita/unused-scanner.svg?branch=master)](https://travis-ci.org/Insolita/unused-scanner)

### Installation

`composer global require insolita/unused-scanner`

Ensure that your ~/.composer/vendor/bin directory declared in $PATH

`echo $PATH`

if not - you should add it in ~/.bashrc or ~/.profile

### Update

`composer global update`

### Usage

prepare configuration file, see [scanner_config.example.php](scanner_config.example.php)

put it in project root (or other place)

run `composer dumpautoload` in your project directory

run `unused_scanner /path/to/configuration/file/scanner_config.php`

**For auto-testing**:

Add --silent option for skip progress output and return exit code = 1, when unused packages detected
run `unused_scanner /path/to/configuration/file/scanner_config.php --silent`

**Docker**:

 run ```docker run -v `pwd`:/app tico/unused-scanner /app/path/to/configuration/file/scanner_config.php```

wait for result..

![Demo screenshot](unused.png)
