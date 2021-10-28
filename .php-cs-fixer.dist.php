<?php

$finder = (new PhpCsFixer\Finder())
    ->in(['src'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@DoctrineAnnotation' => true,
        'ordered_class_elements' => ['order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public_static', // if this is missing, public static properties will be handled like property_public
            'property_protected_static',
            'property_private_static',
            'method_public_static',
            'method_protected_static',
            'method_private_static',
            'property_public',
            'property_protected',
            'property_private',
            'construct',
            'destruct',
            'magic',
            'phpunit',
            'method_public',
            'method_protected',
            'method_private',
        ]],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],

        'mb_str_functions' => true,
        'ordered_interfaces' => true,
        'regular_callable_call' => true,
        'date_time_immutable' => true, // incompatible with jms/job-queue-bundle
        'phpdoc_to_comment' => false, // phpdoc comment is required for type hinting in Eclipse
        'phpdoc_var_without_name' => false, // phpdoc var name is required for type hinting in Eclipse
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const']],
        'strict_comparison' => false,
        'php_unit_strict' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
