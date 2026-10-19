[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-9.0%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Schnittcher/PhilipsHUE/actions/workflows/style.yml/badge.svg)](https://github.com/Schnittcher/PhilipsHUE/actions/workflows/style.yml)
[![Tests](https://github.com/Schnittcher/PhilipsHUE/actions/workflows/tests.yml/badge.svg)](https://github.com/Schnittcher/PhilipsHUE/actions/workflows/tests.yml)

# PhilipsHUE
Diese Bibliothek bindet die Philips HUE Bridge über die HUE API v2 in IP-Symcon ein. Als Rückkanal nutzt sie SSE (Server-Sent Events), Änderungen an den Geräten kommen dadurch sofort in IP-Symcon an.

## Inhaltsverzeichnis
- [PhilipsHUE](#philipshue)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Voraussetzungen](#1-voraussetzungen)
  - [2. Funktionsumfang](#2-funktionsumfang)
  - [3. Enthaltene Module](#3-enthaltene-module)
  - [4. Installation](#4-installation)
  - [5. Konfiguration in IP-Symcon](#5-konfiguration-in-ip-symcon)
  - [6. Spenden](#6-spenden)
  - [7. Lizenz](#7-lizenz)

## 1. Voraussetzungen

* mindestens IPS Version 9.0
* Philips HUE Bridge (HUE API v2) im selben Netzwerk
* Zugriff auf die Taste der Bridge, um IP-Symcon einmalig zu koppeln

## 2. Funktionsumfang
* Bridges im Netzwerk finden und mit IP-Symcon koppeln
* Lampen, Gruppen (Räume und Zonen) und Szenen steuern
* Sensoren, Taster und Drehregler als Variablen abbilden
* Geräte, Räume, Zonen und Szenen über Konfiguratoren anlegen
* Statusänderungen der Bridge in Echtzeit übernehmen

Die einzelnen Funktionen stehen bei den Instanzen unter "Enthaltene Module" und in der README des jeweiligen Moduls.

## 3. Enthaltene Module

* [Discovery](Discovery/README.md)
  * Sucht das Netzwerk nach HUE Bridges und legt den passenden Konfigurator an.
* [Bridge](Bridge/README.md)
  * Gateway zur HUE Bridge. Hier wird IP-Symcon mit der Bridge gekoppelt.
* [DeviceConfigurator](DeviceConfigurator/README.md)
  * Legt alle Geräte und Ressourcen der Bridge als Instanzen an.
* [RoomConfigurator](RoomConfigurator/README.md)
  * Legt die Räume der Bridge als Gruppen an.
* [ZoneConfigurator](ZoneConfigurator/README.md)
  * Legt die Zonen der Bridge als Gruppen an.
* [SzenenConfigurator](SzenenConfigurator/README.md)
  * Legt die Szenen der Bridge als Instanzen an.
* [Light](Light/README.md)
  * Stellt eine Lampe dar: Ein/Aus, Helligkeit, Farbe, Farbtemperatur und Übergangszeit.
* [GroupedLight](GroupedLight/README.md)
  * Stellt eine Gruppe von Lampen (Raum oder Zone) dar und steuert sie gemeinsam, einschließlich Szenenauswahl.
* [Scene](Scene/README.md)
  * Aktiviert eine Szene der Bridge.
* [Button](Button/README.md)
  * Zeigt das letzte Ereignis eines Tasters.
* [RelativeRotary](RelativeRotary/README.md)
  * Zeigt Aktion, Richtung, Schritte und Dauer eines Drehreglers (zum Beispiel Hue Tap Dial).
* [Motion](Motion/README.md)
  * Zeigt die Bewegung eines Bewegungsmelders und lässt ihn aktivieren oder deaktivieren.
* [LightLevel](LightLevel/README.md)
  * Zeigt die Helligkeit eines Sensors.
* [Temperature](Temperature/README.md)
  * Zeigt die Temperatur eines Sensors.
* [Contact](Contact/README.md)
  * Zeigt den Zustand eines Kontaktsensors.
* [Tamper](Tamper/README.md)
  * Zeigt den Manipulationsstatus eines Geräts.
* [DevicePower](DevicePower/README.md)
  * Zeigt den Batteriestand und Batteriezustand eines Geräts.
* [ZigbeeConnectivity](ZigbeeConnectivity/README.md)
  * Zeigt den Zigbee-Verbindungsstatus eines Geräts.

## 4. Installation
Die Installation erfolgt über den IP-Symcon Module Store.

## 5. Konfiguration in IP-Symcon
Zuerst wird eine Discovery-Instanz angelegt. Sie sucht die Bridges im Netzwerk, über sie lässt sich der Konfigurator anlegen. Der Konfigurator erzeugt die Bridge-Instanz, in der die Philips HUE Bridge einmalig mit IP-Symcon gekoppelt wird: Taste an der Bridge drücken und in der Bridge-Instanz "Registriere IP-Symcon" ausführen. Danach zeigt der Konfigurator die Geräte und Ressourcen der Bridge, aus denen sich die Instanzen anlegen lassen.

## 6. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 7. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
