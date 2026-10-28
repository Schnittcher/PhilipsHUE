# Changelog

## 25.10.2026 - Version 2.0.1

### Neu
- Neue Variable "Farbe (xy)" (`color_xy`) bei Lampen und Gruppen: Die Farbe in CIE xy, wie sie die Bridge meldet, ohne Umrechnung. Sie ist steuerbar und eignet sich zum genauen Sichern und Wiederherstellen von Farben. Bestehende Instanzen zeigen die Variable nach dem Öffnen und Speichern der Instanz.

### Fixes
- `PHUE_setColor` begrenzt eine übergebene Helligkeit auf 0 bis 100, weil die Bridge die Anfrage sonst ablehnt und die Farbe nicht setzt.
- Lehnt die Bridge einen Befehl ab (Fehler 400), steht das jetzt als Warnung mit dem Grund im Log.
- Die Farbvariable von Lampen und Gruppen zeigt jetzt zuverlässig die Farbe der Bridge, auch wenn die Lampe außerhalb von IP-Symcon geändert wurde (vorher Schwarz oder falsche Werte). Der Farbwert hängt nicht mehr von der Helligkeit ab (sie steht in der eigenen Variable), der Farbton wird nicht mehr durch Abschneiden einzelner Kanäle verfälscht. Hinweis: Die Konsole von Symcon zeigt die Zelle von „Farbe (xy)“ gelblicher als den Farbwert, weil sie die Farbe abschneidet. Zum genauen Sichern und Wiederherstellen von Farben dient „Farbe (xy)“.
- Bei Gruppen mit der Einstellung "Status für Farbe und Farbtemperatur simulieren" bekommen der Farbwert und die Farbe (xy) nach einer Aktion denselben Wert wie bei einer Meldung der Bridge.

## 19.10.2026 - Version 2.0

### Neu
- Das Modul nutzt die strikte Modul-Basis von IP-Symcon. Achtung: Erforderlich ist jetzt IP-Symcon 9.0 oder neuer.
- Die Variablen nutzen die nativen Darstellungen von IP-Symcon statt Variablenprofilen. Achtung: Beim Übernehmen einer bestehenden Instanz ersetzt die Darstellung das bisherige Profil, eigene Anpassungen an der Darstellung bleiben erhalten. Die Temperatur wird mit zwei Nachkommastellen angezeigt.
- Die Szenenauswahl einer Gruppe wird beim Übernehmen der Instanz automatisch gefüllt.
- Die Konfiguratoren zeigen einen Hinweis, wenn die Bridge nicht verbunden ist.
- Alle Konfigurationsformulare enthalten den Spendenblock und sind vollständig übersetzt.

### Fixes
- Die Module werden unter aktuellen IP-Symcon-Versionen wieder geladen.
- Die Debug-Ausgabe der Bridge zeigt den API-Schlüssel nicht mehr.
- Die Variable "Farbtemperatur Kelvin" hat jetzt einen deutschen Namen.
- Falsche deutsche Bezeichnungen sind korrigiert (Getrennt, Unidirektional eingehend, Im Uhrzeigersinn, Gegen den Uhrzeigersinn).
- Die Beispiele zu `PHUE_setColor` in der README sind korrigiert (Farbwert als Integer, Helligkeit bis 100).
- Die Variablenliste hat in allen Formularen eine Überschrift, und der Name der Variable "Status" beim Temperatursensor ist übersetzt.
- Die Einstellung der Bridge für Fehler 400 heißt jetzt „Fehler 400 im Debug protokollieren“, weil sie genau das tut (die Property bleibt unverändert).
