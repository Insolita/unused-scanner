# Project scanner for detect unused composer dependencies

[![Build Status](https://travis-ci.org/Insolita/unused-scanner.svg?branch=master)](https://travis-ci.org/Insolita/unused-scanner)

### ChangeLog

1.0.8 Return different exitCodes [@see](https://github.com/Insolita/unused-scanner/blob/master/Lib/Runner.php#L18)

1.0.9 Add ability for store usage report [@see](https://github.com/Insolita/unused-scanner/blob/master/scanner_config.example.php#L51)

1.1
   - Fix #10 - php extensions should be skipped without warnings

   - Fix #12 - check presence of scanner_config.php in current working directory and allow run without arguments

   - New config option - skipPackages for excluding packages from checking

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

since 1.1 you can run it without  argument, if scanner_config.php existed in current working directory, it will be used
by default

**For auto-testing**:

Add --silent option for skip progress output and return exit code = 16, when unused packages detected

run `unused_scanner /path/to/configuration/file/scanner_config.php --silent`

**Docker**:

 run ```docker run -v `pwd`:/app tico/unused-scanner /app/path/to/configuration/file/scanner_config.php```

wait for result..

![Demo screenshot](unused.png)
