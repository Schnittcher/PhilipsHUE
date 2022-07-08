<?php

declare(strict_types=1);

class RessourceModule extends IPSModule
{
    public function Create()
    {
        parent::Create();
        //$this->ConnectParent('{6EFF1F3C-DF5F-43F7-DF44-F87EFF149566}');
        $this->ConnectParent('{6786AF05-B089-4BD0-BABA-B2B864CF92E3}');

        $this->RegisterPropertyString('DeviceID', '');
        $this->RegisterPropertyString('RoomID', '');
        $this->RegisterPropertyString('ResourceID', '');

        $Variables = [];
        foreach (static::$Variables as $Pos => $Variable) {
            $Variables[] = [
                'Ident'        => str_replace(' ', '', $Variable[0]),
                'Name'         => $this->Translate($Variable[1]),
                'VarType'      => $Variable[2],
                'Profile'      => $Variable[3],
                'Action'       => $Variable[4],
                'Pos'          => $Pos + 1,
                'Keep'         => $Variable[5]
            ];
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->ConnectParent('{6EFF1F3C-DF5F-43F7-DF44-F87EFF149566}');

        //Setze Filter für ReceiveData
        $ResourceID = $this->ReadPropertyString('ResourceID');
        $this->SetReceiveDataFilter('.*' . $ResourceID . '.*');

        $NewRows = static::$Variables;
        $NewPos = 0;
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            @$this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $Variable['Keep']);
            if ($Variable['Action'] && $Variable['Keep']) {
                $this->EnableAction($Variable['Ident']);
            }
            foreach ($NewRows as $Index => $Row) {
                if ($Variable['Ident'] == str_replace(' ', '', $Row[0])) {
                    unset($NewRows[$Index]);
                }
            }
            if ($NewPos < $Variable['Pos']) {
                $NewPos = $Variable['Pos'];
            }
        }

        if (count($NewRows) != 0) {
            foreach ($NewRows as $NewVariable) {
                $Variables[] = [
                    'Ident'        => str_replace(' ', '', $NewVariable[0]),
                    'Name'         => $this->Translate($NewVariable[1]),
                    'VarType'      => $NewVariable[2],
                    'Profile'      => $NewVariable[3],
                    'Action'       => $NewVariable[4],
                    'Pos'          => ++$NewPos,
                    'Keep'         => $NewVariable[5]
                ];
            }
            IPS_SetProperty($this->InstanceID, 'Variables', json_encode($Variables));
            IPS_ApplyChanges($this->InstanceID);
            return;
        }
        $this->updateValues();
    }

    public function updateValues()
    {
        if ($this->ReadPropertyString('ResourceID') != '') {
            $result = json_decode($this->getData($this->ReadPropertyString('ResourceID'), static::SERVICE), true);
            $Data = $result['data'][0];
            $this->mapResultsToValues($Data);
        }
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $Data = json_decode($JSONString, true)['Data'][0];
        $this->mapResultsToValues($Data);
    }

    protected function SetValue($Ident, $Value)
    {
        if (@$this->GetIDForIdent($Ident)) {
            $this->SendDebug('SetValue :: ' . $Ident, $Value, 0);
            parent::SetValue($Ident, $Value);
        }
    }
    protected function GetValue($Ident)
    {
        if (@$this->GetIDForIdent($Ident)) {
            return parent::GetValue($Ident);
         }
         return false;

    }

    protected function sendData(string $rid, string $endpoint, string $value)
    {
        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'setResourceData';
        $Buffer['rid'] = $rid;
        $Buffer['endpoint'] = $endpoint;
        $Buffer['value'] = $value;
        $Data['Buffer'] = $Buffer;
        $Data = json_encode($Data);

        if (!$this->HasActiveParent()) {
            return [];
        }

        $this->SendDebug(__FUNCTION__, $Data, 0);
        $result = $this->SendDataToParent($Data);
        $this->SendDebug(__FUNCTION__, $result, 0);
    }

    protected function getData(string $rid, string $endpoint)
    {
        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'getResourceData';
        $Buffer['rid'] = $rid;
        $Buffer['endpoint'] = $endpoint;
        $Data['Buffer'] = $Buffer;
        $Data = json_encode($Data);

        if (!$this->HasActiveParent()) {
            return '{"data":[{}]}';
        }

        $this->SendDebug(__FUNCTION__ . ' :: $Data', $Data, 0);
        $result = $this->SendDataToParent($Data);
        $this->SendDebug(__FUNCTION__ . ' :: $result', $result, 0);
        return $result;
    }
}