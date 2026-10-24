# GroupedLight
Diese Instanz stellt den Service GroupedLight bereit, der eine Gruppe von Lampen (Raum oder Zone) abbildet und gemeinsam steuert. Sie wird normalerweise über den Room Configurator oder Zone Configurator angelegt.

## Inhaltsverzeichnis
- [GroupedLight](#groupedlight)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Funktionen](#3-funktionen)
  - [4. Spenden](#4-spenden)
  - [5. Lizenz](#5-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
HUE Raum-/Zonen-ID | ID des Raums oder der Zone. Wird vom Konfigurator gesetzt.
HUE Ressourcen-ID | ID der HUE-Ressource. Wird vom Konfigurator gesetzt.
Variablen | Liste der Variablen der Instanz. Mit „Aktiv“ lassen sich einzelne Variablen abwählen.
Aktualisiere Szenenprofil | Liest die Szenen der Gruppe von der Bridge und füllt die Auswahl der Variable „Szene“.
Status für Farbe und Farbtemperatur simulieren | Setzt die Variablen für Farbe und Farbtemperatur nach einer Aktion, weil die Bridge für Gruppen diese Werte nicht zurückmeldet.
Geräteinformationen abrufen | Fragt die Informationen der Ressource bei der Bridge ab (Ausgabe im Debug).

## 2. Variablen
Die Variablen entstehen mit der Instanz. In der Konfiguration lassen sich einzelne Variablen über „Aktiv“ abwählen. Die Variablen nutzen die nativen Darstellungen von IP-Symcon.

Variable | Ident | Typ | Beschreibung
------------ | ------------ | ------------ | ----------------
Status | `on` | Ja/Nein | Schaltet ein oder aus. Steuerbar.
Helligkeit | `brightness` | Ganzzahl | Helligkeit in Prozent (0 bis 100). Ein Wert über 0 schaltet ein, 0 schaltet aus. Steuerbar.
Farbe | `color` | Ganzzahl | Farbwert (RGB). Steuerbar.
Farbtemperatur | `color_temperature` | Ganzzahl | Farbtemperatur in Mired (153 bis 500). Steuerbar.
Farbtemperatur | `color_temperature_kelvin` | Ganzzahl | Farbtemperatur in Kelvin (2000 bis 6535). Steuerbar.
Übergang | `transition` | Ganzzahl | Übergangszeit in Millisekunden, die bei der nächsten Aktion verwendet wird. Steuerbar.
Szene | `scene` | Text | Auswahl der Szenen der Gruppe. Die Auswahl wird über die Schaltfläche „Aktualisiere Szenenprofil“ und beim Übernehmen der Instanz gefüllt. Steuerbar.

## 3. Funktionen

`void PHUE_setAlert(integer $InstanzID);`
Lässt die Lampe kurz „atmen“, um sie zu erkennen.

`void PHUE_setColor(integer $InstanzID, integer $Color, array $Opt);`
Setzt die Farbe mit weiteren Parametern der HUE API. Der Farbwert wird als Ganzzahl übergeben, zum Beispiel als Hexadezimalzahl `0xFF00FF` (gleichbedeutend mit `16711935`). Texte wie `"FF00FF"` oder `"#FF00FF"` werden nicht angenommen.

**Beispiel:** Die Lampe soll auf die Farbe F6B859 mit der Helligkeit 100 und einem Übergang von 45 ms geschaltet werden:

```php
$InstanzID = 12345;
$color = 0xF6B859; // Farbwert als Integer
$opt = ['on' => ['on' => true], 'dimming' => ['brightness' => 100], 'dynamics' => ['duration' => 45]];
PHUE_setColor($InstanzID, $color, $opt);
```

`void PHUE_updateSceneProfileNeu(integer $InstanzID);`
Liest die Szenen der Gruppe von der Bridge und füllt die Auswahl der Variable „Szene“. Wird beim Übernehmen der Instanz automatisch aufgerufen, wenn die Bridge verbunden ist.

`array PHUE_getOwnerInfo(integer $InstanzID);`
Liefert die Daten des Raums oder der Zone, zu der die Gruppe gehört. Ohne Treffer ist der Rückgabewert `null`.

`void PHUE_updateValues(integer $InstanzID);`
Liest den aktuellen Zustand der Ressource von der Bridge und aktualisiert die Variablen. Wird beim Übernehmen der Instanz automatisch aufgerufen.

`void PHUE_getInfos(integer $InstanzID);`
Fragt die Ressource bei der Bridge ab (Ausgabe im Debug).

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
