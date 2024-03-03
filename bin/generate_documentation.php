<?php
/*
 * This file is part of philiagus/parser
 *
 * (c) Andreas Eicher <philiagus@philiagus.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Philiagus\Parser\Contract\Parser;

require_once __DIR__ . '/../vendor/autoload.php';

(new class() {

    private const string SOURCE_PATH = __DIR__ . '/../src',
        TARGET_FILE = __DIR__ . '/../doc/list-of-parsers.md';

    private array $allParsers = [];

    public function run(): void
    {
        $basePath = realpath(self::SOURCE_PATH) . DIRECTORY_SEPARATOR;
        $iterator = new RecursiveDirectoryIterator($basePath);
        $recursiveIterator = new RecursiveIteratorIterator($iterator);
        $regexIterator = new RegexIterator($recursiveIterator, '/^.*\.php$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($regexIterator as $element) {
            require_once $element[0];
        }

        $allClasses = get_declared_classes();
        foreach ($allClasses as $class) {
            if (!is_a($class, Parser::class, true)) continue;
            $reflection = new \ReflectionClass($class);
            if (
                $reflection->isAbstract() ||
                $reflection->isInterface() ||
                $reflection->isEnum() ||
                !str_starts_with($reflection->getFileName(), $basePath)
            ) continue;
            $this->addToDocumentation($reflection);
        }
        ob_start();
        $allParsers = $this->allParsers;
        (static function() use ($allParsers): void {
            require __DIR__ . '/template.md.php';
        })();
        $content = ob_get_clean();
        file_put_contents(self::TARGET_FILE, $content);
    }

    public function addToDocumentation(ReflectionClass $parserClass): void
    {
        $className = $parserClass->getName();
        $doc = $parserClass->getDocComment() ?: '';
        $flags = [];
        if(preg_match_all('~@(?<flag>\S+)\h*(?<value>.*?)\h*$~m', $doc, $matches, PREG_SET_ORDER)) {
            foreach($matches as ['flag' => $flag, 'value' => $value]) {
                $flags[$flag] = $value;
            }
        }
        $package = 'Generic';
        if(preg_match('~@package\s+(.*\\\\)?(?<package>\S+)~', $doc, $matches)) {
            $package = $matches['package'];
        }
        $cleanDoc = preg_replace(
            '~(
                ^\h*\*+\h*@.*?$|
                ^\h*/\*+\h*$|
                ^\h*\*+/\h*$|
                ^\h*\*+\h*+
            )~mx'
            , '', $doc);
        $cleanDoc = preg_replace('~^\h+$~m', '', $cleanDoc);
        $cleanDoc = preg_replace('~\n\n+~', "\n\n", $cleanDoc);
        $cleanDoc = trim($cleanDoc);
        $this->allParsers[$package][$className] = [
            'doc' => $cleanDoc,
            'flags' => $flags
        ];
    }
})->run();


