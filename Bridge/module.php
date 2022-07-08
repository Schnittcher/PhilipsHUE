<?php

declare(strict_types=1);

class HUEBridge extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('Host', '');
        $this->RegisterAttributeString('User', '');
        $this->RequireParent('{2FADB4B7-FDAB-3C64-3E2C-068A4809849A}');

        $this->RegisterMessage(IPS_GetInstance($this->InstanceID)['ConnectionID'], IM_CHANGESTATUS);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        if (!$this->BridgePaired()) {
            $this->SetStatus(200);

            $this->LogMessage('Error: Registration incomplete, please pair IP-Symcon with the Philips HUE Bridge.', KL_ERROR);
            return;
        }
    }

    public function RegisterServerEvents()
    {
        $url = 'https://' . $this->ReadPropertyString('Host') . '/eventstream/clip/v2';
        $this->SendDebug('RegisterServerEvents :: url', $url, 0);
        $parent = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        IPS_SetProperty($parent, 'URL', $url);
        IPS_SetProperty($parent, 'VerifyPeer', false);
        IPS_SetProperty($parent, 'VerifyHost', false);
        IPS_SetProperty($parent, 'Active', true);
        IPS_SetProperty($parent, 'Headers', json_encode([['Name' => 'Accept', 'Value' => 'text/event-stream'], ['Name' => 'hue-application-key', 'Value' => $this->ReadAttributeString('User')]]));
        IPS_ApplyChanges($parent);
    }

    public function ForwardData($JSONString)
    {
        $this->SendDebug(__FUNCTION__, $JSONString, 0);
        $data = json_decode($JSONString, true);

        switch ($data['Buffer']['Command']) {
            case 'getDevices':
                $result = $this->getDevices();
                break;
            case 'getRooms':
                $result = $this->getRooms();
                break;
            case 'setResourceData':
                $result = $this->sendRequest($this->ReadAttributeString('User'), 'resource/' . $data['Buffer']['endpoint'] . '/' . $data['Buffer']['rid'], $data['Buffer']['value'], 'PUT');
                break;
            case 'getResourceData':
                $result = $this->sendRequest($this->ReadAttributeString('User'), 'resource/' . $data['Buffer']['endpoint'] . '/' . $data['Buffer']['rid'], '', 'GET');
                break;
            default:
            $this->SendDebug(__FUNCTION__, 'Invalid Command: ' . $data->Buffer->Command, 0);
            break;
        }
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
        return json_encode($result);
    }

    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString, true);
        $HUEData = json_decode($data['Data'], true);

        foreach ($HUEData as $key => $Data) {
            $Data['DataID'] = '{6C33FAE0-8FF8-4CAE-B5E9-89A2D24D067D}';
            $Data['Data'] = $Data['data'];
            $this->SendDataToChildren(json_encode($Data));
        }
    }

    public function registerUser()
    {
        $params['devicetype'] = 'Symcon';
        $result = $this->sendRequest('', '', json_encode($params), 'POST');
        if (@isset($result[0]->success->username)) {
            $this->SendDebug('Register User', 'OK: ' . $result[0]->success->username, 0);
            $this->WriteAttributeString('User', $result[0]->success->username);
            $this->RegisterServerEvents();
            $this->SetStatus(102);
        } else {
            $this->SendDebug(__FUNCTION__ . 'Pairing failed', json_encode($result), 0);
            $this->SetStatus(200);
            $this->LogMessage('Error: ' . $result[0]->error->type . ': ' . $result[0]->error->description, KL_ERROR);
        }
    }

    public function getDevices()
    {
        return $this->sendRequest($this->ReadAttributeString('User'), 'resource/device', '', 'GET');
    }

    public function getRooms()
    {
        return $this->sendRequest($this->ReadAttributeString('User'), 'resource/room', '', 'GET');
    }

    private function sendRequest(string $User, string $endpoint, string $params, string $method = 'GET')
    {
        if ($this->ReadPropertyString('Host') == '') {
            return false;
        }

        $this->SendDebug('User', $User, 0);
        $ch = curl_init();
        if ($User != '' && $endpoint != '') {
            $this->SendDebug(__FUNCTION__ . ' URL', 'https://' . $this->ReadPropertyString('Host') . '/clip/v2/' . $endpoint, 0);
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->ReadPropertyString('Host') . '/clip/v2/' . $endpoint);
        } elseif ($endpoint != '') {
            return [];
        } else {
            $this->SendDebug(__FUNCTION__ . ' URL', $this->ReadPropertyString('Host') . '/api/' . $endpoint, 0);
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->ReadPropertyString('Host') . '/api/' . $endpoint);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, 'Symcon');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'hue-application-key: ' . $User
        ]);

        if ($method == 'POST' || $method == 'PUT' || $method == 'DELETE') {
            if ($method == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            }
            if (in_array($method, ['PUT', 'DELETE'])) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $apiResult = curl_exec($ch);
        $this->SendDebug(__FUNCTION__ . ' Result', $apiResult, 0);
        $headerInfo = curl_getinfo($ch);
        if ($headerInfo['http_code'] == 200) {
            if ($apiResult != false) {
                $this->SetStatus(102);
                return json_decode($apiResult, false);
            } else {
                $this->LogMessage('Philips HUE sendRequest Error' . curl_error($ch), 10205);
                //$this->SetStatus(201);
                return new stdClass();
            }
        } else {
            $this->LogMessage('Philips HUE sendRequest Error - Curl Error:' . curl_error($ch) . 'HTTP Code: ' . $headerInfo['http_code'], 10205);
            //$this->SetStatus(202);
            return new stdClass();
        }
        curl_close($ch);
    }

    private function BridgePaired()
    {
        if ($this->ReadAttributeString('User') != '') {
            return true;
        }
        return false;
    }
}
