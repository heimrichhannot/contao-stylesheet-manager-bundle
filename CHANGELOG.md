# Changelog
All notable changes to this project will be documented in this file.

## [3.2.4] - 2019-06-25

### Removed
- `--default-encoding=` argument for compatibility to Dart Sass

## [3.2.3] - 2018-02-09

### Added
- `--default-encoding=` argument to sass command using the current contao config `characterSet` 

## [3.2.2] - 2018-01-11

### Fixed
- output path containing spaces now supported

## [3.2.1] - 2017-11-14

### Fixed
- import file full path replacement

## [3.2.0] - 2017-11-14

### Added
- support for recursive watching for changes in @import'ed files (SCSS)

### Fixed
- issue concerning assets served by .public file

## [3.1.3] - 2017-11-13

### Fixed
- fixed regexp for replacing in fe_page

## [3.1.2] - 2017-11-13

### Fixed
- Disable source map files when in `prod` mode

## [3.1.1] - 2017-11-08

### Fixed
- ensure that `assets/css` directory exists, before `scss` compiler tries to compile css file to `assets/css` 

## [3.1.0] - 2017-11-06

### Added
- Google Webfonts support (includes Webfonts set in Contao page layout settings)

## [3.0.1] - 2017-10-27

### Removed
- echo 1

## [3.0.0] - 2017-10-27

### Removed
- compass requirement for sass compiling -> now uses plain sass
- config.rb

## [2.0.8] - 2017-10-16

### Added
- support for bundle css files symlinked in web folder

## [2.0.7] - 2017-10-09

### Changed
- drop restriction to fe_page (or derivates)

## [2.0.6] - 2017-09-27

### Fixed
- `compass` executable path under osx

## [2.0.5] - 2017-07-26

### Fixed
- fixed usage of already compiled css file if no compiler lib is present (e.g. on live systems)

## [2.0.4] - 2017-07-26

### Fixed
- fixed lib detection

## [2.0.3] - 2017-07-26

### Fixed
- fixed lib detection

## [2.0.2] - 2017-07-18

### Fixed
- compass location (which doesn't work else)

## [2.0.1] - 2017-06-26

### Fixed
- docs and composer.json

## [2.0.0] - 2017-06-26

### Added
- multiple group support (see README.md for further details on new config array structure)

## [1.0.2] - 2017-06-21

### Fixed
- check for existance of preprocessor

## [1.0.1] - 2017-06-21

### Added
- more documentation

### Fixed
- compass default path
