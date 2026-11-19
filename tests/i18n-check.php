<?php

/**
 * Every string in languages/<domain>.pot must be translated in every shipped .mo.
 *
 * The .mo files are what WordPress loads, so a .po that was updated but not
 * compiled fails here too. The bundled catalogues are what a git-archive build
 * ships; the wp.org zip uses the language pack instead (see .distignore). Each
 * locale also needs the JSON for build/admin.js, named after md5 of that path,
 * or the settings screen stays English.
 *
 * existing-checked: tests/*.php and tests/*.mjs - none reads the catalogues; CI run-i18n is off and only diffs the .pot.
 *
 * Run: php tests/i18n-check.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$fail = static function (string $msg): void {
    fwrite(STDERR, "FAIL: {$msg}\n");
    exit(1);
};

$unquote = static fn (string $s): string => stripcslashes(substr($s, 1, -1));

// msgctxt\4msgid keys from the .pot (header entry skipped).
$potFiles = glob($root . '/languages/*.pot') ?: [];
if (count($potFiles) !== 1) {
    $fail('expected exactly one .pot in languages/');
}
$keys  = [];
$entry = [];
$field = '';
foreach (array_merge(file($potFiles[0], FILE_IGNORE_NEW_LINES) ?: [], ['']) as $line) {
    if (preg_match('/^(msgctxt|msgid|msgid_plural|msgstr(?:\[\d+\])?) (".*")$/', $line, $m)) {
        $field         = $m[1];
        $entry[$field] = $unquote($m[2]);
    } elseif (str_starts_with($line, '"') && '' !== $field) {
        $entry[$field] .= $unquote($line);
    } elseif ('' === trim($line)) {
        if (($entry['msgid'] ?? '') !== '') {
            $keys[] = (isset($entry['msgctxt']) ? $entry['msgctxt'] . "\4" : '') . $entry['msgid'];
        }
        $entry = [];
        $field = '';
    }
}

$moFiles = glob($root . '/languages/*.mo') ?: [];
if ([] === $keys || [] === $moFiles) {
    $fail('no strings in the .pot or no .mo files in languages/');
}

$errors = 0;
foreach ($moFiles as $mo) {
    $data = (string) file_get_contents($mo);
    $le   = unpack('V', $data)[1] === 0x950412de;
    $int  = static fn (int $off): int => unpack($le ? 'V' : 'N', substr($data, $off, 4))[1];
    $n    = $int(8);
    $orig = $int(12);
    $tran = $int(16);
    $map  = [];
    for ($i = 0; $i < $n; $i++) {
        $id  = explode("\0", substr($data, $int($orig + $i * 8 + 4), $int($orig + $i * 8)))[0];
        $str = substr($data, $int($tran + $i * 8 + 4), $int($tran + $i * 8));
        $map[$id] = $str;
    }
    $missing = array_filter($keys, static fn (string $k): bool => trim(str_replace("\0", '', $map[$k] ?? '')) === '');
    foreach ($missing as $k) {
        fwrite(STDERR, basename($mo) . ': untranslated "' . str_replace("\4", '|', $k) . "\"\n");
    }
    $errors += count($missing);

    $json = substr($mo, 0, -3) . '-' . md5('build/admin.js') . '.json';
    if (! is_file($json)) {
        fwrite(STDERR, basename($json) . ": missing (JS translations for build/admin.js)\n");
        $errors++;
    }
}

if ($errors > 0) {
    $fail("{$errors} problem(s); update the .po, then run wp i18n make-mo languages and make-json");
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI script, not a page.
echo 'OK: ' . count($keys) . ' strings translated in ' . count($moFiles) . " .mo files\n";
