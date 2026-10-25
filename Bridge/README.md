# Bridge
Diese Instanz ist die Verbindung zur HUE Bridge. Sie koppelt IP-Symcon mit der Bridge, nutzt einen SSE-Client als Rückkanal und verteilt die Daten an die Geräteinstanzen. Sie wird normalerweise über die Discovery-Instanz angelegt.

## Inhaltsverzeichnis
- [Bridge](#bridge)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
  - [3. Spenden](#3-spenden)
  - [4. Lizenz](#4-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
IP-Adresse der HUE Bridge | Adresse der Bridge im Netzwerk.
Registriere IP-Symcon | Koppelt IP-Symcon mit der Bridge. Vorher muss die Taste an der Bridge gedrückt werden. Das Ergebnis steht als Meldung im Formular.
Fehlermeldungen | Mit „Fehler 207“ werden Teilfehler der Bridge im Log gemeldet. Mit „Fehler 400 im Debug protokollieren“ werden Antworten mit Fehler 400 im Debug protokolliert, sonst werden sie ohne Meldung verworfen. Lehnt die Bridge einen Befehl ab (Fehler 400 bei einem Schreibzugriff), steht das immer als Warnung mit dem Grund der Bridge im Log.

## 2. Funktionen

`void PHUE_registerUser(integer $InstanzID);`
Koppelt IP-Symcon mit der Bridge (die Taste an der Bridge muss vorher gedrückt werden).

`void PHUE_RegisterServerEvents(integer $InstanzID);`
Richtet den SSE-Rückkanal im SSE-Client ein.

`mixed PHUE_getDevices(integer $InstanzID);`
Liefert die Geräte der Bridge als Array.

`mixed PHUE_getRooms(integer $InstanzID);`
Liefert die Räume der Bridge als Array.

`mixed PHUE_getZones(integer $InstanzID);`
Liefert die Zonen der Bridge als Array.

`mixed PHUE_getScenes(integer $InstanzID);`
Liefert die Szenen der Bridge als Array.

`array PHUE_getScenesbyGroup(integer $InstanzID);`
Liefert die Szenen der Bridge, gruppiert nach Raum oder Zone.

## 3. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 4. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
