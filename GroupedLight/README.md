# Light
   Diese Instanz stellt den Service Light bereit, welcher eine Lampe in IP-Symcon darstellen kann.
     
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   HUE Device ID | Hier wird die ID der HUE Devices eingetragen. (Automatisch über den Konfigurator)
   HUE Resource ID | Hier wird die ID der HUE Ressource eingetragen. (Automatisch über den Konfigurator)
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