<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LocalizationTest extends TestCase
{
    #[DataProvider('moduleFolders')]
    public function testEveryTextHasAGermanTranslation(string $folder): void
    {
        $german = $this->germanTranslations($folder);

        $missing = array_values(array_filter(
            $this->usedTexts($folder),
            fn (string $text) => !isset($german[$text])
        ));

        $this->assertSame([], $missing, 'Texte ohne deutsche Übersetzung in ' . $folder . '/locale.json');
    }

    #[DataProvider('moduleFolders')]
    public function testTranslationsAreNotEmptyAndHaveNoUnusedKeys(string $folder): void
    {
        $used = array_flip($this->usedTexts($folder));

        foreach ($this->germanTranslations($folder) as $key => $translation) {
            $this->assertNotSame('', trim($translation), 'Leere Übersetzung für "' . $key . '"');
            $this->assertArrayHasKey($key, $used, 'Übersetzung für "' . $key . '" wird in ' . $folder . ' nirgends verwendet');
        }
    }

    #[DataProvider('moduleFolders')]
    public function testSourceTextsAreEnglish(string $folder): void
    {
        // Die Originaltexte sind englisch: kein Umlaut und kein "ß" in Beschriftungen und Translate-Schlüsseln
        foreach ($this->usedTexts($folder) as $text) {
            $this->assertDoesNotMatchRegularExpression('/[äöüÄÖÜß]/u', $text, 'Deutsches Original: ' . $text);
        }
    }

    public static function moduleFolders(): array
    {
        $folders = [];
        foreach (glob(__DIR__ . '/../*/locale.json') as $file) {
            $name = basename(dirname($file));
            $folders[$name] = [$name];
        }
        return $folders;
    }

    private function germanTranslations(string $folder): array
    {
        $locale = json_decode((string) file_get_contents(__DIR__ . '/../' . $folder . '/locale.json'), true);
        return $locale['translations']['de'];
    }

    // Alle Texte, die ein Modul übersetzt: Beschriftungen der form.json, Translate()-Schlüssel, Variablennamen und Darstellungstexte
    private function usedTexts(string $folder): array
    {
        $texts = array_merge($this->formTexts($folder), $this->translateKeys($folder));

        $code = (string) file_get_contents(__DIR__ . '/../' . $folder . '/module.php');
        // Variablennamen: ['ident', 'Name', VARIABLETYPE_...
        preg_match_all("/\\[\\s*'[^']+',\\s*'([^']+)',\\s*VARIABLETYPE_/", $code, $names);
        $texts = array_merge($texts, $names[1]);

        if ($this->isResourceModule($code)) {
            $resource = (string) file_get_contents(__DIR__ . '/../libs/ResourceModule.php');
            // Darstellungen: [Wert, 'Beschriftung', Farbe] – nur die Optionen, die dieses Modul über seine Profile nutzt
            foreach ($this->presentationTexts($resource, $code) as $text) {
                $texts[] = $text;
            }
        }

        // Beschriftungen, die das Modul zur Laufzeit selbst ins Formular einfügt: 'caption' => '...'
        preg_match_all("/'caption'\\s*=>\\s*'((?:[^'\\\\]|\\\\.)+)'/", $code, $captions);
        $texts = array_merge($texts, $captions[1]);

        if ($folder === 'Discovery') {
            // Namen der Konfiguratoren: 'Device Configurator' => '{GUID}'
            preg_match_all("/^\\s+'([A-Z][^']*)'\\s*=>\\s*'\\{[0-9A-F-]+\\}'/m", $code, $configurators);
            $texts = array_merge($texts, $configurators[1]);
        }

        return array_values(array_unique($texts));
    }

    private function isResourceModule(string $code): bool
    {
        return strpos($code, 'extends RessourceModule') !== false;
    }

    // Beschriftungen aus presentationFor(), die zu den Profilen des Moduls gehören
    private function presentationTexts(string $resource, string $moduleCode): array
    {
        preg_match_all("/VARIABLETYPE_\\w+,\\s*'([^']*)'/", $moduleCode, $profiles);
        $texts = [];
        foreach (array_unique($profiles[1]) as $profile) {
            if ($profile === '') {
                continue;
            }
            $start = strpos($resource, "case '" . $profile . "':");
            if ($start === false) {
                continue;
            }
            $end = strpos($resource, "case '", $start + 5);
            $block = substr($resource, $start, ($end === false ? strlen($resource) : $end) - $start);
            preg_match_all("/\\[(?:'[^']+'|true|false), '([^']+)', 0x[0-9A-Fa-f]+\\]/", $block, $captions);
            $texts = array_merge($texts, $captions[1]);
        }
        return $texts;
    }

    private function translateKeys(string $folder): array
    {
        $code = (string) file_get_contents(__DIR__ . '/../' . $folder . '/module.php');
        preg_match_all("/Translate\\('((?:[^'\\\\]|\\\\.)*)'\\)/", $code, $matches);
        return array_values(array_unique($matches[1]));
    }

    // Alle Beschriftungen der form.json (caption), auch die des Spendenblocks
    private function formTexts(string $folder): array
    {
        $path = __DIR__ . '/../' . $folder . '/form.json';
        if (!is_file($path)) {
            return [];
        }
        $form = json_decode((string) file_get_contents($path), true);
        $texts = [];
        array_walk_recursive($form, function ($value, $key) use (&$texts)
        {
            if (in_array($key, ['caption', 'label'], true) && is_string($value) && $value !== '') {
                $texts[] = $value;
            }
        });
        return array_values(array_unique($texts));
    }
}
