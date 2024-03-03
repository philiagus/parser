<?php
/**
 * @var array<string, array<string, string>> $allParsers
 */
ksort($allParsers);
array_walk($allParsers, fn(&$a) => ksort($a));
?>
# List of Parsers

Here you can find a short overview of all the parsers provided with this package.

<?php foreach($allParsers as $group => $parsers): ?>
## <?= $group ?>

<?php foreach($parsers as $name => $doc): ?>
- `<?= $name ?>` for `<?= $doc['flags']['target-type'] ?? 'mixed' ?>`

<?= preg_replace('~^~m', '    ', $doc['doc']) ?>

<?php endforeach; ?>

<?php endforeach; ?>
