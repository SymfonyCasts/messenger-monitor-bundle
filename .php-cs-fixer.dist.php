<?php

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->exclude('tmp')
;

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'protected_to_private' => false,
        'semicolon_after_instruction' => false,
        'phpdoc_to_comment' => false,
    ))
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
