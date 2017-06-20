# Contao Stylesheet Manager

This module offers functionality for handling different types of stylesheets in Contao.

## Features

- expandable architecture (new preprocessors like LESS can be added easily)
- SCSS
    - uses locally installed compass for compiling SCSS files (usually ```/usr/bin/compass```)
    - full support for compass's config.rb
- aggregating all files to a single CSS file
- support for development and production environment
- caching of the generated CSS file so that a full regeneration is only necessary if at least one of the linked files change
- a random string is added to the name of the generated css file to bypass browser caches

## Possible types of stylesheets

Note: the order of types is mandatory.

| Stylesheet type     | Description                                           | Supported formats                       | Overridable stylesheets |
|---------------------|-------------------------------------------------------|-----------------------------------------|-------------------------|
| core stylesheets    | e.g. bootstrap                                        | CSS, SCSS                               | –                       |
| module stylesheets  | stylesheets living in contao modules, i.e. assets/css | CSS (else contao would throw Exception) | core                    |
| project stylesheets | project specific stylesheets                          | CSS, SCSS                               | core, modules           |

Note: Even though project stylesheets are loaded *after* everything else, there's still access to all stylesheets (including variables, mixins, ... living in the core stylesheets).

## Currently supported formats

Note: This module is written in an expandable way, so new compilers can be added easily (e.g. LESS).

- CSS
- SCSS (compiled by compass which is a requirement then -> tested successfully with version 1.0.3)

## Technical instructions

### Installation

1. Add the following code to a config.php of some of your modules (according to your project):
    ```
    $GLOBALS['TL_STYLESHEET_MANAGER_CSS'] = [
        'core'    => [
            'files/themes/my_project/scss/_variables.scss', // add the project's variables in order to override variables of libs like bootstrap
            'files/themes/my_project/scss/_core.scss' // could contain libs like bootstrap or font awesome
        ],
        'project' => [
            'files/themes/my_project/scss/mixins/_columns.scss',
            'files/themes/my_project/scss/components/_accordion.scss',
            'files/themes/my_project/scss/pages/_home.scss'
        ]
    ];
    ```
    
    __Important note__: Every file can import other files, but these imported files are currently *not* inspected for changes. At this stage it's the best way to add all your partial scss files to ```$GLOBALS['TL_STYLESHEET_MANAGER_CSS']``` as shown above.

2. Copy the contao template fe_page.html5 to your contao instance's templates directory and replace ```<?= $this->stylesheets ?>``` by ```<!-- stylesheetManagerCss -->``` (CAUTION: including the comment characters!).

### Configuration

Note: Take a look into ```config.php``` in order to see what properties can be adjusted.

### Commands

- clear the stylesheet manager cache: ```<contao dir>/vendor/bin/contao-console stylesheetmanager:cache:clear```

### Add a new preprocessor

1. Extend from Compiler (copy Scss.php if you like):

    ```
    <?php
    
    namespace Acme\MyBundle\Compiler;
    
    class Less extends Compiler
    {
        //...
    }
    ```

2. Register the new preprocessor in the config:

    ```
    <?php
    
    // Acme\MyBundle\Resources/contao/config.php
    
    $GLOBALS['STYLESHEET_MANAGER']['preprocessors']['less'] = [
        'class'   => '\Acme\MyBundle\Compiler\Less',
        'bin'     => '/usr/bin/less',
        'cmdDev'  => '##lib## ...',
        'cmdProd' => '##lib## ...',
    ];
    ```

3. Activate the new preprocessor:
    
    ```
    $GLOBALS['STYLESHEET_MANAGER']['activePreprocessor'] = 'less';
    ```

### Hooks

Name | Arguments | Description
---- | --------- | -----------
modifyFrontendPage | $strBuffer, $strTemplate | Triggers the compiling.

## Notes on css generation on live systems

On the live server we usually don't have preprocessors like compass installed. Because of that reason
the library uses an already existing generated CSS file even if changes have been made on SCSS files on
this live server (of course this generated CSS file might be not up to date but in most cases this is
better than no CSS or some error message ;-)). If the necessary preprocessor like compass lib is in
place even on the live server stylesheet manager regenerates the final CSS file.

## TODO

- support for contao's tags "static", "media", ... in asset paths added to the according arrays in ```$GLOBALS```
- accomplish auto triggering of scss compiling if an *imported* file is changed (currently only files listed in TL_CSS, TL_USER_CSS, TL_FRAMEWORK, and TL_STYLESHEET_MANAGER_CSS are inspected for changes) -> maybe using compass watch