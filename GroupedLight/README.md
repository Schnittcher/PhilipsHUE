# GroupedLight
   Diese Instanz stellt den Service GroupedLight bereit, welcher eine Gruppe von Lampen in IP-Symcon darstellen kann.
     
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   HUE Room ID | Hier wird die ID der HUE Räume eingetragen. (Automatisch über den Konfigurator)
   HUE Resource ID | Hier wird die ID der HUE Ressource eingetragen. (Automatisch über den Konfigurator)
   Status für Farbe und Farbtemperatur simulieren | Wenn die Farbe oder die Farbtemperatur über die Instanz gesetzt wird, kann mit dieser Option ein Simulieren der Rückantwort aktiviert werden. Da die HUE Gruppe keine Rückantwort für die Farbe oder die Farbtemperatur erhalten.
   Variablen | Hier können einzelne Variablen deaktiviert werden.

  ## 2. Funktionen

     ```php
   RequestAction($VariablenID, $Value);
   ```
   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

   **Beispiel:**
   
   Variable ID Status: 12345
   ```php
   RequestAction(12345, true); //Licht einschalten
   RequestAction(12345, false); //Licht ausschalten
   ```