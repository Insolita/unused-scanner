<?php
$projectPath = getcwd();
return [
    'composerJsonPath' => $projectPath . '/stub_composer.json',
    'vendorPath' => $projectPath . '/../vendor/',
    'scanDirectories' => [$projectPath . '/stubs/'],
    'requireDev' => false
];
