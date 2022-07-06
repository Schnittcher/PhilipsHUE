<?php

declare(strict_types=1);
/**
 * @addtogroup generic
 * @{
 *
 * @file          DebugHelper.php
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       5.0
 */
/**
 * DebugHelper ergänzt SendDebug um die Möglichkeit Array und Objekte auszugeben.
 */
trait DebugHelper
{
    /**
     * Ergänzt SendDebug um Möglichkeit Objekte und Array auszugeben.
     *
     * @param string             $Message Nachricht für Data.
     * @param TXB_API_Data|mixed $Data    Daten für die Ausgabe.
     *
     * @return int $Format Ausgabeformat für Strings.
     */
    protected function SendDebug($Message, $Data, $Format)
    {
        if (is_a($Data, 'Kodi_RPC_Data')) {
            parent::SendDebug($Message . ' Method', $Data->Namespace . '.' . $Data->Method, 0);
            switch ($Data->Typ) {
                case Kodi_RPC_Data::$EventTyp:
                    $this->SendDebug($Message . ' Event', $Data->GetEvent(), 0);
                    break;
                case Kodi_RPC_Data::$ResultTyp:
                    $this->SendDebug($Message . ' Result', $Data->GetResult(), 0);
                    break;
                default:
                    $this->SendDebug($Message . ' Params', $Data->Params, 0);
                    break;
            }
        } elseif (is_a($Data, 'KodiRPCException')) {
            $this->SendDebug($Message, $Data->getMessage(), 0);
        } elseif (is_array($Data)) {
            if (count($Data) > 25) {
                $this->SendDebug($Message, array_slice($Data, 0, 20), 0);
                $this->SendDebug($Message . ':CUT', '-------------CUT-----------------', 0);
                $this->SendDebug($Message, array_slice($Data, -5, null, true), 0);
            } else {
                foreach ($Data as $Key => $DebugData) {
                    $this->SendDebug($Message . ':' . $Key, $DebugData, 0);
                }
            }
        } elseif (is_object($Data)) {
            foreach ($Data as $Key => $DebugData) {
                $this->SendDebug($Message . '.' . $Key, $DebugData, 0);
            }
        } elseif (is_bool($Data)) {
            parent::SendDebug($Message, ($Data ? 'TRUE' : 'FALSE'), 0);
        } else {
            if (IPS_GetKernelRunlevel() == KR_READY) {
                parent::SendDebug($Message, $Data, $Format);
            } else {
                IPS_LogMessage($this->InstanceID . ': ' . $Message, $Data);
            }
        }
    }
}
