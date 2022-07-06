# HUEDevice
   Dieses Modul bildet die verschiedenen HUE Geräte in IP-Symcon ab.
   Darunter zählen zum Beispiel Sensoren, Lichter, Schalter und Gruppen.
     
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   HUE Device ID | Hier wird die ID der HUE Devices eingetragen.
   Geräte Typ | Auswhal zwischen Licht, Sensor/Schalter und Gruppe
   Sensor Typ | Nur sichtbar, wenn als Geräte Typ Sensor ausgewählt wurde,hier kann der Typ des Sensors ausgewählt werden.

  ## 2. Funktionen

  **PHUE_AlertSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich einen Alarm für eine Lampe / Gruppe zu setzen.
   none: Kein Alarm
   select: Das Licht führt einen Atemzyklus (breathe cycle) durch.
   lselect: Die Leuchte führt 15 Sekunden lang oder bis zum Empfang eines Befehls "alert": "none" Atemzyklen (breathe cycle) durch. d.h. nachdem der Atemzyklus (breathe cycle) beendet ist, setzt die Brücke die Warnung nicht auf "none" zurück
   ```php
   PHUE_AlertSet($InstanceID, $Value); //string 'none', 'select', 'lselect'
   ```

   **PHUE_CTSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich die Farbtemperatur der Lampe bzw. der Gruppe zu ändern. Der Wert wird in Integer angegeben werden.
   ```php
   PHUE_CTSet(25537, 366); //Farbtemperatur 366
   ```

   **PHUE_ColorSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich die Farbe der Lampe bzw. der Gruppe zu ändern. Der Wert wird in Hex angegeben werden.
   ```php
   PHUE_ColorSet(25537, '#FF0000'); //Farbe Rot
   ```

   **PHUE_DimSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät bzw. die Gruppe zu dimmen.
   ```php
   PHUE_DimSet(25537, 50); //0-254
   ```

   **PHUE_EffectSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich einen Effekt für die Lampe bzw. Gruppe zu aktiveren.
   ```php
   PHUE_EffectSet(25537, 'colorloop'); //Effekt colorloop
   ```

   **PHUE_GetState($InstanceID)**\
   Mit dieser Funktion ist es möglich den aktuellen Status der lampe / Gruppe abzufragen.
   ```php
   PHUE_GetState(25537); //Gibt ture oder false zurück
   ```

   **PHUE_SatSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich die Sättigung der Lampe bzw. der Gruppe zu ändern. Der Wert wird in Integer angegeben werden.
   ```php
   PHUE_SatSet(25537, 50); //Sättigung 50  - 254
   ```

   **PHUE_SceneSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich eine Szene für die Gruppe zu aktiveren.
   ```php
   PHUE_SceneSet(25537, 'Name der Szene');
   ```

      **PHUE_SceneSetEx($InstanceID, $Value, $Parameter)**\
   Mit dieser Funktion ist es möglich eine Szene mit erweiterten Parametern für die Gruppe zu aktiveren.
   ```php
   PHUE_SceneSetEx(25537, 'Name der Szene', ['transitiontime' => 200]);
   ```
 
   **PHUE_SwitchMode($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   PHUE_SwitchMode(25537, true); //Einschalten
   PHUE_SwitchMode(25537, false); //Ausschalten
   ```

   **PHUE_SensorStateSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich einen Sensor ein- bzw. auszuschalten.
   ```php
   PHUE_SensorStateSet(25537, true); //Einschalten
   PHUE_SensorStateSet(25537, false); //Ausschalten
   ```

   **PHUE_CLIPSensorStateSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich einen CLIPGenericStatus Sensor ein- bzw. auszuschalten.
   Zum Beispiel für die HUE Labs Szenen.
   ```php
   PHUE_CLIPSensorStateSet(25537, true); //Einschalten
   PHUE_CLIPSensorStateSet(25537, false); //Ausschalten
   ```