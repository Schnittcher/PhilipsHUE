<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

include_once __DIR__ . '/stubs/Validator.php';

class LibraryValidationTest extends TestCaseSymconValidation
{
    // Prüft library.json, module.json, form.json und locale.json der Bibliothek mit dem Validator der Symcon-Stubs
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    #[DataProvider('moduleFolders')]
    public function testValidateModule(string $folder): void
    {
        $this->validateModule(__DIR__ . '/../' . $folder);
    }

    public static function moduleFolders(): array
    {
        $folders = [];
        foreach (glob(__DIR__ . '/../*/module.json') as $file) {
            $name = basename(dirname($file));
            $folders[$name] = [$name];
        }
        return $folders;
    }
}
