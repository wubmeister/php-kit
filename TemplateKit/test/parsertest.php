<?php

$resolver = new CoreKit\Resolver();
$resolver->addPath(__DIR__);
TemplateKit\SmartTemplate\Compiler::setParserOptions([
    'resolver' => $resolver
]);

$filename = __DIR__ . '/uncompiled.tpl';
$cachename = md5($filename).'_uncompiled.php';

// if (!file_exists($cachename) || filemtime($cachename) < filemtime($filemtime)) {
    $template = file_get_contents('uncompiled.tpl');
    $parser = TemplateKit\SmartTemplate\Compiler::getParser();
    $document = $parser->parse($template);
    // var_dump($document);
    // echo $document->getPhpCode() . PHP_EOL;
    $phpCode = $document->getPhpCode();
    $inc = '';
    foreach ($document->getIncludes() as $include) {
        $inc .= 'require_once "' . $include . '";' . PHP_EOL;
    }
    if ($inc) {
        $phpCode = '<?php' . PHP_EOL . $inc . PHP_EOL . '?>' . PHP_EOL . $phpCode;
    }
    file_put_contents($cachename, $phpCode);
// } else {
//     echo 'CACHED' . PHP_EOL;
// }

$content = 'Hello';
$variable = 'variable value';
include $cachename;
echo PHP_EOL;
