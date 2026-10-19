# Changelog

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
