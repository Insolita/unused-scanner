2.4.0
 - Add Symfony 6 support

2.3.0

- Fix [#34](https://github.com/Insolita/unused-scanner/issues/34)
- Fix [#36](https://github.com/Insolita/unused-scanner/issues/36)
  [#37](https://github.com/Insolita/unused-scanner/issues/37)
- Add support --version option
- Fix [#33](https://github.com/Insolita/unused-scanner/issues/33) - now .phar builds available

2.2.0

- Fix dev dependencies for composer2.0 compatibility
- code typehint fixes

2.1.1

- Improve json output format
- code style fixes

2.1

- Support namespaces with group use declarations
- Ensure php 7.4 support

2.0.4

- Symfony 5.0 support

2.0.3

- Added ext_mbstring dependency in composer.json
- Cosmetic changes

2.0.2

- Add License file
- Fix travis tests config

2.0

- PHP >=7.1 branch without legacy support

1.3

- Added support for old php 5.6, php 7.0 versions

1.2

- Window suppor improvement

1.1.1

- Fix #13, use DIRECTORY_SEPARATOR constants for windows support
- add tests for php 7.3 in travis.ci
- move Changelog in separated file

1.1

- Fix #10 - php extensions should be skipped without warnings

- Fix #12 - check presence of scanner_config.php in current working directory and allow run without arguments

- New config option - skipPackages for excluding packages from checking

1.0.9

- Add ability for store usage report [@see](https://github.com/Insolita/unused-scanner/blob/master/scanner_config.example.php#L51)

1.0.8

- Return different exitCodes [@see](https://github.com/Insolita/unused-scanner/blob/master/Lib/Runner.php#L18)
