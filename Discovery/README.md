# Discovery
Diese Instanz durchsucht das Netzwerk nach HUE Bridges und legt über die gefundene Bridge den passenden Konfigurator an.

## Inhaltsverzeichnis
- [Discovery](#discovery)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
  - [3. Spenden](#3-spenden)
  - [4. Lizenz](#4-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
HUE-Bridge-Suche | Liste der gefundenen Bridges (Name, IP-Adresse, Modell, Seriennummer) mit den dazugehörigen Konfiguratoren. Über „Erstellen“ werden Konfigurator, Bridge und SSE-Client angelegt.

## 2. Funktionen

`array PHUE_mDNSDiscoverBridges(integer $InstanzID);`
Sucht per mDNS nach HUE Bridges und liefert die gefundenen Bridges als Liste.

## 3. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 4. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
