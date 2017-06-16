# Contao Stylesheet Manager

This module offers functionality for handling different types of stylesheets in Contao.

The possible types are (the order is mandatory):

| Stylesheet type     | Description                                           | Supported formats                       | Overridable stylesheets |
|---------------------|-------------------------------------------------------|-----------------------------------------|-------------------------|
| core stylesheets    | e.g. bootstrap                                        | CSS, SCSS                               | –                       |
| module stylesheets  | stylesheets living in contao modules, i.e. assets/css | CSS (else contao would throw Exception) | core                    |
| project stylesheets | project specific stylesheets                          | CSS, SCSS                               | core, modules           |

Note: Even though project stylesheets are loaded *after* everything else, there's still access to all stylesheets (including variables, mixins, ... living in the core stylesheets).

## Currently supported formats

Note: This module is written in an expandable way, so new compilers can be added easily (e.g. LESS).

- CSS
- SCSS (compiled by compass)

## Features

- uses locally installed compass for compiling SCSS files (usually ```/usr/bin/compass```)
- full support for compass's config.rb
- aggregating all files to a single CSS file
- support for development and production environment

## Technical instructions

### Installation

Add the following code to a config.php of some of your modules (according to your project):
```
$GLOBALS['TL_STYLESHEET_MANAGER_CSS'] = [
    'core' => 'files/themes/my_project/scss/_core.scss',
    'project' => 'files/themes/my_project/scss/_project.scss',
];
```

_core.scss and _project.scss themselves can also contain @import statements, of course. If you like it better you could also define 'core' and 'project' to be arrays of css/scss files.

Copy the contao template fe_page.html5 to your contao instance's templates directory and replace ```<?= $this->stylesheets ?>``` by ```<!-- stylesheetManagerCss -->``` (CAUTION: including the comment characters!).

### Hooks

Name | Arguments | Description
---- | --------- | -----------
modifyFrontendPage | $strBuffer, $strTemplate | Triggers the compiling.

### TODO

- support for contao's tags "static", "media", ... in asset paths added to the according arrays in ```$GLOBALS```