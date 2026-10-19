# ZigbeeConnectivity
Diese Instanz stellt den Service Zigbee Connectivity bereit, der den Zigbee-Verbindungsstatus eines Geräts anzeigt. Sie wird normalerweise über den Device Configurator angelegt.

## Inhaltsverzeichnis
- [ZigbeeConnectivity](#zigbeeconnectivity)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Funktionen](#3-funktionen)
  - [4. Spenden](#4-spenden)
  - [5. Lizenz](#5-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
HUE Geräte-ID | ID des HUE-Geräts. Wird vom Konfigurator gesetzt.
HUE Ressourcen-ID | ID der HUE-Ressource. Wird vom Konfigurator gesetzt.
Variablen | Liste der Variablen der Instanz. Mit „Aktiv“ lassen sich einzelne Variablen abwählen.
Geräteinformationen abrufen | Fragt die Informationen der Ressource bei der Bridge ab (Ausgabe im Debug).

## 2. Variablen
Die Variablen entstehen mit der Instanz. In der Konfiguration lassen sich einzelne Variablen über „Aktiv“ abwählen. Die Variablen nutzen die nativen Darstellungen von IP-Symcon.

Variable | Ident | Typ | Beschreibung
------------ | ------------ | ------------ | ----------------
Status | `State` | Text | Zigbee-Verbindungsstatus (Verbunden, Getrennt, Konnektivitätsproblem, Unidirektional eingehend).

## 3. Funktionen

`void PHUE_updateValues(integer $InstanzID);`
Liest den aktuellen Zustand der Ressource von der Bridge und aktualisiert die Variablen. Wird beim Übernehmen der Instanz automatisch aufgerufen.

`void PHUE_getInfos(integer $InstanzID);`
Fragt die Ressource bei der Bridge ab (Ausgabe im Debug).

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
