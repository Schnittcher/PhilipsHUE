<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ModuleBehaviorTest extends TestCase
{
    private const LIGHT = '{87FA14D1-0ACA-4CBD-BE83-BA4DF8831876}';
    private const GROUPED_LIGHT = '{6324AC4A-330C-4CB2-9281-12EECB450024}';
    private const DEVICE_CONFIGURATOR = '{52399872-F02A-4BEB-ACA0-1F6AE04D9663}';
    private const RESOURCE_ID = '11111111-1111-1111-1111-111111111111';

    // Alle Ressourcen-Module: Modul-GUID => Klasse
    private const RESOURCE_MODULES = [
        'Contact'            => ['{33022FE1-4A9F-47D3-950C-E60CAF98577C}', 'HUEContact'],
        'Tamper'             => ['{72893563-78CB-4706-BEE4-5F6F8A71CCFA}', 'HUETamper'],
        'Motion'             => ['{F8DF1FCA-CA5B-4099-935C-3E563BCC2BE0}', 'HUEMotion'],
        'ZigbeeConnectivity' => ['{88465699-1C57-429B-924A-CAA56F45762F}', 'HUEZigbeeConnectivity'],
        'LightLevel'         => ['{F8EAC53D-5442-405B-99EC-394046D07141}', 'HUELightLevel'],
        'Temperature'        => ['{93B25D1B-5630-4A3E-8BA9-FD2B4D4177F0}', 'HUETemperature'],
        'DevicePower'        => ['{B86C3E14-09CC-4EF0-A28C-AA198FC25C51}', 'HUEDevicePower'],
        'Button'             => ['{2D52B78D-0A13-485B-8B98-C2E0A6BA2EF1}', 'HUEButton'],
        'Light'              => ['{87FA14D1-0ACA-4CBD-BE83-BA4DF8831876}', 'HUELight'],
        'RelativeRotary'     => ['{585B4217-36CF-4263-A898-3C2198E7C857}', 'HUERelativeRotary'],
        'GroupedLight'       => ['{6324AC4A-330C-4CB2-9281-12EECB450024}', 'HUEGroupedLight'],
        'Scene'              => ['{53C7BFDF-C0CE-4837-82E3-654FAF7126FB}', 'HUEScene']
    ];

    // Die Darstellungen von Symcon, die das Modul nutzt
    private const KNOWN_PRESENTATIONS = [
        '{60AE6B26-B3E2-BDB1-A3A1-BE232940664B}', // Schalter
        '{6B9CAEEC-5958-C223-30F7-BD36569FC57A}', // Schieberegler
        '{3319437D-7CDE-699D-750A-3C6A3841FA75}', // Wertanzeige
        '{6F477326-1683-A2FD-D2E7-477F366ECB62}', // Werteingabe
        '{52D9E126-D7D2-2CBB-5E62-4CF7BA7C5D82}', // Aufzählung
        '{05CC3CC2-A0B2-5837-A4A7-A07EA0B9DDFB}'  // Farbe
    ];

    protected function setUp(): void
    {
        // Jeder Test startet mit einem leeren Kernel und lädt die Bibliothek neu
        IPS\Kernel::reset();
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');
        parent::setUp();
    }

    // --- Farbumrechnung (Light und GroupedLight) ---

    public function testHexColorIsSplitIntoRgb(): void
    {
        $light = $this->resource(self::LIGHT);

        $this->assertSame([246, 184, 89], $this->call($light, 'HexToRGB', 0xF6B859));
        $this->assertSame([0, 0, 0], $this->call($light, 'HexToRGB', 0));
    }

    public function testPrimaryColorsConvertToHueXy(): void
    {
        $light = $this->resource(self::LIGHT);

        // Werte aus der Formel der Hue-Dokumentation (Matrix 2016): Rot x=0.7006 y=0.2993, Weiß liegt um x=0.3227 y=0.3290
        $red = $this->call($light, 'RGBToXy', [255, 0, 0]);
        $this->assertEqualsWithDelta(0.7006, $red['x'], 0.0005);
        $this->assertEqualsWithDelta(0.2993, $red['y'], 0.0005);
        $this->assertSame(72, $red['bri']);

        $white = $this->call($light, 'RGBToXy', [255, 255, 255]);
        $this->assertEqualsWithDelta(0.3227, $white['x'], 0.0005);
        $this->assertEqualsWithDelta(0.3290, $white['y'], 0.0005);
        $this->assertSame(254, $white['bri']);

        $black = $this->call($light, 'RGBToXy', [0, 0, 0]);
        $this->assertSame([0, 0, 0], [$black['x'], $black['y'], $black['bri']]);
    }

    public function testColorKeepsItsHueWhenConvertedBackFromXy(): void
    {
        $light = $this->resource(self::LIGHT);

        foreach ([[255, 0, 0], [0, 255, 0], [0, 0, 255], [246, 184, 89], [255, 255, 255], [120, 40, 200]] as $rgb) {
            $xy = $this->call($light, 'RGBToXy', $rgb);
            $hex = $this->call($light, 'xyToHEX', $xy['x'], $xy['y']);

            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex);
            $back = $this->call($light, 'HexToRGB', (int) hexdec(substr($hex, 1)));
            // Bei voller Helligkeit ist der Farbwert normalisiert: der stärkste Kanal ist voll ausgesteuert
            $this->assertSame(255, max($back), 'Stärkster Kanal von ' . json_encode($rgb) . ' -> ' . $hex);
            // Der Farbton bleibt erhalten: dieselbe Farbart (xy) wie die Ausgangsfarbe
            $xyBack = $this->call($light, 'RGBToXy', $back);
            $this->assertEqualsWithDelta($xy['x'], $xyBack['x'], 0.01, 'x von ' . json_encode($rgb));
            $this->assertEqualsWithDelta($xy['y'], $xyBack['y'], 0.01, 'y von ' . json_encode($rgb));
        }
    }

    public function testXyConversionIsStableWhenRepeated(): void
    {
        $light = $this->resource(self::LIGHT);

        // Ein gelesener Farbwert wieder geschrieben und gelesen ergibt denselben Wert (Szenen sichern und wiederherstellen)
        $xy = $this->call($light, 'RGBToXy', [246, 184, 89]);
        $first = $this->call($light, 'xyToHEX', $xy['x'], $xy['y']);
        $xyAgain = $this->call($light, 'RGBToXy', $this->call($light, 'HexToRGB', (int) hexdec(substr($first, 1))));
        $second = $this->call($light, 'xyToHEX', $xyAgain['x'], $xyAgain['y']);

        $this->assertSame($first, $second);
    }

    // Der Farbwert hat den richtigen Farbton der Lampe. Die Konsole von Symcon färbt xy anders ein (volle Helligkeit, abgeschnitten,
    // deshalb gelblicher: #ffec82 zum selben xy). Der Wert hier stammt aus der Konsole zur Lampe bei 366 mired (Spalte "Farbe").
    public function testColorValueKeepsTheHueOfTheLampInsteadOfClippingLikeTheXyCellInSymcon(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);

        $this->receive($light, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 100.0], 'color' => ['xy' => ['x' => 0.4572, 'y' => 0.4099]]]);

        $this->assertSame('{"x":0.4572,"y":0.4099}', GetValueString($this->ident($light, 'color_xy')));
        $this->assertSame(0xFFCF78, GetValueInteger($this->ident($light, 'color')));
    }

    // --- Parameter für die Bridge ---

    public function testBrightnessOfExtraParametersIsLimitedToTheRangeOfTheApi(): void
    {
        $light = $this->resource(self::LIGHT);

        // Die Bridge lehnt Werte über 100 samt der ganzen Anfrage ab (Hue API v2: 0 bis 100)
        $limited = $this->call($light, 'limitBrightness', ['on' => ['on' => true], 'dimming' => ['brightness' => 150], 'dynamics' => ['duration' => 45]]);
        $this->assertSame(100.0, $limited['dimming']['brightness']);
        $this->assertSame(['on' => true], $limited['on']);
        $this->assertSame(['duration' => 45], $limited['dynamics']);

        $this->assertSame(0.0, $this->call($light, 'limitBrightness', ['dimming' => ['brightness' => -5]])['dimming']['brightness']);
        $this->assertSame(42.5, $this->call($light, 'limitBrightness', ['dimming' => ['brightness' => 42.5]])['dimming']['brightness']);
        $this->assertSame(100.0, $this->call($light, 'limitBrightness', ['dimming' => ['brightness' => '100']])['dimming']['brightness']);
    }

    public function testParametersWithoutBrightnessStayUnchanged(): void
    {
        $light = $this->resource(self::LIGHT);

        $this->assertSame([], $this->call($light, 'limitBrightness', []));
        $this->assertSame(['on' => ['on' => true]], $this->call($light, 'limitBrightness', ['on' => ['on' => true]]));
        $this->assertSame(['dimming' => ['brightness' => 'hell']], $this->call($light, 'limitBrightness', ['dimming' => ['brightness' => 'hell']]));
    }

    // --- Daten der Bridge (SSE und Abfrage) ---

    public function testLightTakesStatusFromBridgeData(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);

        $this->receive($light, [
            'id'                => self::RESOURCE_ID,
            'on'                => ['on' => true],
            'dimming'           => ['brightness' => 42.0],
            'color_temperature' => ['mirek' => 250],
            'color'             => ['xy' => ['x' => 0.7006, 'y' => 0.2993]]
        ]);

        $this->assertTrue(GetValueBoolean($this->ident($light, 'on')));
        $this->assertSame(42, GetValueInteger($this->ident($light, 'brightness')));
        $this->assertSame(250, GetValueInteger($this->ident($light, 'color_temperature')));
        $this->assertSame(4000, GetValueInteger($this->ident($light, 'color_temperature_kelvin')));
        $color = GetValueInteger($this->ident($light, 'color'));
        // Die Helligkeit (42 Prozent) steht in der eigenen Variable, der Farbwert zeigt die Farbe: Rot voll ausgesteuert
        $this->assertSame(255, ($color >> 16) & 0xFF);
        $this->assertLessThan(20, ($color >> 8) & 0xFF);
        $this->assertLessThan(20, $color & 0xFF);
        // Die Farbe in xy kommt unverändert von der Bridge
        $this->assertSame('{"x":0.7006,"y":0.2993}', GetValueString($this->ident($light, 'color_xy')));
    }

    public function testColorXyIsTakenOverUnchangedAndIndependentOfBrightness(): void
    {
        foreach ([self::LIGHT, self::GROUPED_LIGHT] as $guid) {
            $instance = $this->resource($guid, ['ResourceID' => self::RESOURCE_ID]);

            $this->receive($instance, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 100.0], 'color' => ['xy' => ['x' => 0.5041, 'y' => 0.4144]]]);
            $this->assertSame('{"x":0.5041,"y":0.4144}', GetValueString($this->ident($instance, 'color_xy')));

            // Eine neue Helligkeit ändert die Farbe in xy nicht
            $this->receive($instance, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 20.0]]);
            $this->assertSame('{"x":0.5041,"y":0.4144}', GetValueString($this->ident($instance, 'color_xy')));
        }
    }

    public function testColorXyHasTheXyPresentationAndAnAction(): void
    {
        $light = $this->resource(self::LIGHT);

        $presentation = $this->call($light, 'presentationFor', 'PhilipsHUE.ColorXY');

        $this->assertSame('{05CC3CC2-A0B2-5837-A4A7-A07EA0B9DDFB}', $presentation['PRESENTATION']);
        $this->assertSame(4, $presentation['ENCODING']);
        $this->assertCount(4, json_decode($presentation['CUSTOM_COLOR_SPACE'], true));
        $this->assertTrue(HasAction($this->ident($light, 'color_xy')));
    }

    public function testXyValuesFromVariablesAreValidated(): void
    {
        $light = $this->resource(self::LIGHT);

        $this->assertSame(['x' => 0.5041, 'y' => 0.4144], $this->call($light, 'parseXy', '{"x":0.5041,"y":0.4144}'));
        $this->assertSame(['x' => 0.0, 'y' => 1.0], $this->call($light, 'parseXy', '{"x":0,"y":1}'));
        foreach (['', 'kein json', '{"x":0.5}', '{"x":"a","y":0.4}', '{"x":1.5,"y":0.4}', '{"x":0.5,"y":-0.1}', '[0.5,0.4]'] as $invalid) {
            $this->assertNull($this->call($light, 'parseXy', $invalid), 'Ungültiger Wert: ' . $invalid);
        }
        $this->assertNull($this->call($light, 'parseXy', 42));
    }

    // Gruppen melden ihre Farbe nicht zurück. Mit der Einstellung "Status simulieren" gehen beide Farbvariablen den Weg einer Meldung der Bridge.
    public function testGroupedLightSimulatesBothColorVariablesLikeABridgeMessage(): void
    {
        $group = $this->resource(self::GROUPED_LIGHT, ['ResourceID' => self::RESOURCE_ID, 'stausColorColorTemperature' => true]);
        $this->receive($group, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 100.0]]);

        // Farbe als xy schreiben: color_xy unverändert, color aus xy und Helligkeit
        IPS_RequestAction($group, 'color_xy', '{"x":0.5041,"y":0.4144}');
        $this->assertSame('{"x":0.5041,"y":0.4144}', GetValueString($this->ident($group, 'color_xy')));
        $fromXy = GetValueInteger($this->ident($group, 'color'));
        $this->assertSame(255, max($this->channels($fromXy)));

        // Farbe als Farbwert schreiben: color aus xy, color_xy passend dazu
        IPS_RequestAction($group, 'color', 0xFF0000);
        $xy = json_decode(GetValueString($this->ident($group, 'color_xy')), true);
        $this->assertEqualsWithDelta(0.7006, $xy['x'], 0.0005);
        $this->assertEqualsWithDelta(0.2993, $xy['y'], 0.0005);
        $this->assertSame(255, $this->channels(GetValueInteger($this->ident($group, 'color')))[0]);
    }

    public function testGroupedLightLeavesTheColorVariablesAloneWithoutSimulation(): void
    {
        $group = $this->resource(self::GROUPED_LIGHT, ['ResourceID' => self::RESOURCE_ID]);

        IPS_RequestAction($group, 'color_xy', '{"x":0.5041,"y":0.4144}');
        IPS_RequestAction($group, 'color', 0xFF0000);

        $this->assertSame('', GetValueString($this->ident($group, 'color_xy')));
        $this->assertSame(0, GetValueInteger($this->ident($group, 'color')));
    }

    public function testDataOfOtherResourcesIsIgnored(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);

        $this->receive($light, ['id' => '22222222-2222-2222-2222-222222222222', 'on' => ['on' => true]]);

        $this->assertFalse(GetValueBoolean($this->ident($light, 'on')));
    }

    // Der Fehler aus dem Forum: Die Farbvariable hing von einer gemerkten Helligkeit ab (Attribut tmpBrightness, nur beim Setzen
    // über die Variable gefüllt) und lieferte Schwarz oder falsche Werte, wenn die Lampe außerhalb von IP-Symcon geändert wurde
    public function testColorIsTakenFromXyWithoutPriorKnowledgeOfTheBrightness(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);

        $this->receive($light, ['id' => self::RESOURCE_ID, 'color' => ['xy' => ['x' => 0.5041, 'y' => 0.4144]]]);

        $color = GetValueInteger($this->ident($light, 'color'));
        $this->assertNotSame(0, $color);
        $this->assertSame(255, max($this->channels($color)));
    }

    public function testColorValueDoesNotDependOnTheBrightnessOfTheLamp(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);

        $this->receive($light, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 100.0], 'color' => ['xy' => ['x' => 0.4572, 'y' => 0.4099]]]);
        $bright = GetValueInteger($this->ident($light, 'color'));

        // Bei 20 Prozent Helligkeit zeigt die Variable dieselbe Farbe, nicht ein dunkles Braun
        $this->receive($light, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 20.0], 'color' => ['xy' => ['x' => 0.4572, 'y' => 0.4099]]]);

        $this->assertSame($bright, GetValueInteger($this->ident($light, 'color')));
        $this->assertSame(0xFFCF78, $bright);
        $this->assertSame(20, GetValueInteger($this->ident($light, 'brightness')));
    }

    public function testNewBrightnessAloneLeavesTheColorUntouched(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);
        $this->receive($light, ['id' => self::RESOURCE_ID, 'color' => ['xy' => ['x' => 0.1700, 'y' => 0.0500]]]);
        $color = GetValueInteger($this->ident($light, 'color'));

        $this->receive($light, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 33.0]]);

        $this->assertSame($color, GetValueInteger($this->ident($light, 'color')));
        $this->assertSame(33, GetValueInteger($this->ident($light, 'brightness')));
    }

    public function testBrightnessWithoutAKnownColorLeavesTheColorUntouched(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);

        $this->receive($light, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 60.0]]);

        $this->assertSame(0, GetValueInteger($this->ident($light, 'color')));
        $this->assertSame(60, GetValueInteger($this->ident($light, 'brightness')));
    }

    public function testColorCanBeSavedAndRestoredWithTheBrightness(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);
        $this->receive($light, ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 60.0], 'color' => ['xy' => ['x' => 0.5041, 'y' => 0.4144]]]);
        $saved = GetValueInteger($this->ident($light, 'color'));

        // Gesicherten Farbwert in xy zurückrechnen, wie es RequestAction beim Wiederherstellen tut. Die API kennt xy nur mit vier
        // Nachkommastellen, kleine Kanäle weichen deshalb um wenige Stufen ab.
        $xy = $this->call($light, 'RGBToXy', $this->call($light, 'HexToRGB', $saved));
        $this->receive($light, ['id' => self::RESOURCE_ID, 'color' => ['xy' => ['x' => $xy['x'], 'y' => $xy['y']]]]);

        foreach ($this->channels($saved) as $channel => $value) {
            $this->assertEqualsWithDelta($value, $this->channels(GetValueInteger($this->ident($light, 'color')))[$channel], 4.0, 'Kanal ' . $channel);
        }
    }

    public function testGroupedLightGivesTheSameColorAsALightForTheSameXy(): void
    {
        $light = $this->resource(self::LIGHT, ['ResourceID' => self::RESOURCE_ID]);
        $group = $this->resource(self::GROUPED_LIGHT, ['ResourceID' => self::RESOURCE_ID]);
        $data = ['id' => self::RESOURCE_ID, 'dimming' => ['brightness' => 20.0], 'color' => ['xy' => ['x' => 0.3, 'y' => 0.6]]];

        $this->receive($light, $data);
        $this->receive($group, $data);

        $this->assertNotSame(0, GetValueInteger($this->ident($group, 'color')));
        $this->assertSame(GetValueInteger($this->ident($light, 'color')), GetValueInteger($this->ident($group, 'color')));
    }

    // --- Variablen und Darstellungen ---

    #[DataProvider('resourceModules')]
    public function testEveryProfileIsMappedToAPresentation(string $module): void
    {
        [$guid, $class] = self::RESOURCE_MODULES[$module];
        $instance = $this->resource($guid);

        foreach ($class::$Variables as $row) {
            $profile = $row[3];
            $presentation = $this->call($instance, 'presentationFor', $profile);

            if ($profile === '') {
                // Variablen ohne Profil bleiben ohne eigene Darstellung
                $this->assertSame('', $presentation);
                continue;
            }

            $this->assertIsArray($presentation, $module . ': Profil ' . $profile . ' hat keine Darstellung');
            $this->assertContains($presentation['PRESENTATION'], self::KNOWN_PRESENTATIONS);
            if (isset($presentation['OPTIONS'])) {
                $options = json_decode($presentation['OPTIONS'], true);
                $this->assertNotEmpty($options);
                $values = array_column($options, 'Value');
                $this->assertSame($values, array_values(array_unique($values, SORT_REGULAR)), 'Doppelte Werte in den Optionen von ' . $profile);
                foreach ($options as $option) {
                    $this->assertNotSame('', $option['Caption']);
                }
            }
        }
    }

    public function testUnknownProfilesAreLeftUntouched(): void
    {
        $instance = $this->resource(self::LIGHT);

        $this->assertSame('~UnknownProfile', $this->call($instance, 'presentationFor', '~UnknownProfile'));
        $this->assertSame('', $this->call($instance, 'presentationFor', ''));
    }

    #[DataProvider('resourceModules')]
    public function testVariablesAreCreatedAndApplyChangesIsRepeatable(string $module): void
    {
        [$guid, $class] = self::RESOURCE_MODULES[$module];
        $instance = $this->resource($guid);

        $expected = array_map(fn (array $row) => str_replace(' ', '', $row[0]), array_values(array_filter($class::$Variables, fn (array $row) => $row[5])));
        sort($expected);

        $idents = fn () => $this->identsOf($instance);
        $this->assertSame($expected, $idents(), $module . ': Variablen nach dem Anlegen');

        IPS_ApplyChanges($instance);
        IPS_ApplyChanges($instance);
        $this->assertSame($expected, $idents(), $module . ': Variablen nach wiederholtem ApplyChanges');
    }

    public function testDisabledVariableIsRemovedAndActionsAreKept(): void
    {
        $light = $this->resource(self::LIGHT);
        $rows = json_decode(IPS_GetProperty($light, 'Variables'), true);
        foreach ($rows as &$row) {
            if ($row['Ident'] === 'transition') {
                $row['Keep'] = false;
            }
        }
        unset($row);
        IPS_SetProperty($light, 'Variables', json_encode($rows));
        IPS_ApplyChanges($light);

        $this->assertNotContains('transition', $this->identsOf($light));
        $this->assertContains('brightness', $this->identsOf($light));
        $this->assertTrue(HasAction($this->ident($light, 'brightness')));
    }

    public function testFormListsAllVariablesWithoutCreatingThem(): void
    {
        $light = $this->resource(self::LIGHT);
        IPS_DeleteVariable($this->ident($light, 'transition'));
        $before = $this->identsOf($light);

        $form = json_decode(IPS_GetConfigurationForm($light), true);
        $rows = array_column($form['elements'][2]['values'], 'Ident');

        // Das Formular zeigt die Variable weiter als Zeile, legt sie aber erst mit ApplyChanges wieder an
        $this->assertContains('transition', $rows);
        $this->assertSame($before, $this->identsOf($light));
        IPS_ApplyChanges($light);
        $this->assertContains('transition', $this->identsOf($light));
    }

    // --- Formulare ---

    public function testConfiguratorShowsAHintWithoutBridge(): void
    {
        $configurator = IPS_CreateInstance(self::DEVICE_CONFIGURATOR);

        $form = json_decode(IPS_GetConfigurationForm($configurator), true);

        $this->assertIsArray($form);
        $this->assertSame('Label', $form['elements'][0]['type']);
        $this->assertSame('The bridge is not connected. Open the bridge instance and pair it with IP-Symcon first.', $form['elements'][0]['caption']);
    }

    #[DataProvider('formFiles')]
    public function testEveryFormEndsWithTheDonationBlock(string $file): void
    {
        $form = json_decode((string) file_get_contents($file), true);
        $actions = $form['actions'];

        // Der Spendenblock sind die letzten drei Elemente: Überschrift, Hinweistext und die Zeile mit den Bildern
        $block = array_slice($actions, -3);
        $this->assertSame('Donations', $block[0]['caption']);
        $this->assertStringStartsWith('This module is free for non-commercial use.', $block[1]['caption']);
        $this->assertSame('RowLayout', $block[2]['type']);
    }

    public static function resourceModules(): array
    {
        $modules = [];
        foreach (array_keys(self::RESOURCE_MODULES) as $name) {
            $modules[$name] = [$name];
        }
        return $modules;
    }

    public static function formFiles(): array
    {
        $files = [];
        foreach (glob(__DIR__ . '/../*/form.json') as $file) {
            $files[basename(dirname($file))] = [$file];
        }
        return $files;
    }

    // --- Hilfen ---

    // Legt eine Ressourceninstanz an und übernimmt die Properties
    private function resource(string $guid, array $properties = []): int
    {
        $id = IPS_CreateInstance($guid);
        foreach ($properties as $name => $value) {
            IPS_SetProperty($id, $name, $value);
        }
        IPS_ApplyChanges($id);
        return $id;
    }

    // Ruft eine geschützte Methode des Moduls auf
    private function call(int $instance, string $method, ...$arguments)
    {
        $module = IPS\InstanceManager::getInstanceInterface($instance);
        return (new ReflectionMethod($module, $method))->invoke($module, ...$arguments);
    }

    // Spielt Daten der Bridge ein, wie sie der Rückkanal liefert
    private function receive(int $instance, array $resource): void
    {
        IPS\InstanceManager::getInstanceInterface($instance)->ReceiveData(json_encode(['DataID' => '{6C33FAE0-8FF8-4CAE-B5E9-89A2D24D067D}', 'Data' => [$resource]]));
    }

    // Zerlegt einen Farbwert in [R, G, B]
    private function channels(int $color): array
    {
        return [($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF];
    }

    private function ident(int $instance, string $ident): int
    {
        return IPS_GetObjectIDByIdent($ident, $instance);
    }

    private function identsOf(int $instance): array
    {
        $idents = array_map(fn (int $id) => IPS_GetObject($id)['ObjectIdent'], IPS_GetChildrenIDs($instance));
        sort($idents);
        return $idents;
    }
}
