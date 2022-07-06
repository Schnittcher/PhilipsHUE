[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Check Style](https://github.com/Schnittcher/PhilipsHUE/workflows/Check%20Style/badge.svg)](https://github.com/Schnittcher/PhilipsHUE/actions)
![Version](https://img.shields.io/badge/Symcon%20Version-6.3%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

# PhilipsHUE
   Dieses Modul stellt eine Verbindung zur der Philips HUE Bridge her, um HUE Geräte in IP-Symcon zu integrieren.
   Dieses Modul basiert auf der neuen API von Philips HUE, welches als Rückkanal SSE (Server-Sent Events) benutzt.
 
   ## Inhaltverzeichnis
   1. [Voraussetzungen](#1-voraussetzungen)
   2. [Enthaltene Module](#2-enthaltene-module)
   3. [Installation](#3-installation)
   4. [Konfiguration in IP-Symcon](#4-konfiguration-in-ip-symcon)
   5. [Spenden](#5-spenden)
   6. [Lizenz](#6-lizenz)
   
## 1. Voraussetzungen

* mindestens IPS Version 6.3


## 2. Enthaltene Module

* Bridge
* Configurator
* DevicePower
* Discovery
* Light
* LightLevel
* Motion
* Temperature
* ZigbeeConnectivity


## 3. Installation
Über den IP-Symcon Module Store. (Beta Version Philpis HUE V2)

## 4. Konfiguration in IP-Symcon

Nachdem das Modul installiert wurde muss eine Discovery Instanz angelegt werden.
Die Discovery Instanz durchsucht das Netzwerk nach vorhanden Philip HUE Bridges.
Nachdem die Suche abgeschlossen worden ist, kann über die Discovery Instanz der Konfigurator für das Modul angelegt werden.
Der Konfigurator legt ein Gateway an, dieses muss nun geöffnet werden, dort muss die Philips HUE Bridge mit IP-Symcon gepairt werden, dazu muss auf der HUE Bridge der Button gedrückt werden. Nachdem der Button betätigt wurde, muss in der Bridge Instanz der Button "Registriere IP-Symcon" ausgeführt werden. Nun sollte IP-Symcon mit der Philips HUE Bridge gepairt sein und im Konfigurator sollten die Geräte & Resourcen zu finden sein.

## 5. Spenden

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:    

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a> <a href="https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share" target="_blank">Amazon Wunschzettel</a>

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)