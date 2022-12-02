# GroupedLight
   Diese Instanz stellt den Service GroupedLight bereit, welcher eine Gruppe von Lampen in IP-Symcon darstellen kann.
     
   ## Inhaltverzeichnis
- [GroupedLight](#groupedlight)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
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

   ```php
   PHUE_setColor($InstanzID,$Color, $opt)
   ```
   Mit dieser Funktion kann eine Gruppe mit weiteren Parametern geschaltet werden.

   **Beispiel:**
   
   Instanz ID: 27705
   Die Lampen der Gruppe sollen auf die Farbe F6B859 mit der Helligkeit 150 geschaltet werden und dies mit einem Übergang von 45ms.

   ```php
   $InstanzID= 27705;
   $color = 'F6B859';
   $opt = ['on' => ['on' => true], 'dimming' => ['brightness' => 150], 'dynamics' => ['duration' => 45]];
   PHUE_setColor($InstanzID, $color, $opt);
   ```

   