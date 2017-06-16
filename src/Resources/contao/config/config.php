<?php

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['modifyFrontendPage']['compileStylesheets'] = ['HeimrichHannot\StylesheetManagerBundle\Manager', 'compile'];