<?php

declare(strict_types=1);

class HUEDeviceConfigurator extends IPSModule
{
    const RESOURCES =
        [
            'motion'                 => '{F8DF1FCA-CA5B-4099-935C-3E563BCC2BE0}',
            'zigbee_connectivity'    => '{88465699-1C57-429B-924A-CAA56F45762F}',
            'light_level'            => '{F8EAC53D-5442-405B-99EC-394046D07141}',
            'temperature'            => '{93B25D1B-5630-4A3E-8BA9-FD2B4D4177F0}',
            'device_power'           => '{B86C3E14-09CC-4EF0-A28C-AA198FC25C51}',
            'button'                 => '{2D52B78D-0A13-485B-8B98-C2E0A6BA2EF1}',
            'light'                  => '{87FA14D1-0ACA-4CBD-BE83-BA4DF8831876}'
        ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{6786AF05-B089-4BD0-BABA-B2B864CF92E3}');
        $this->RegisterPropertyString('Serialnumber', '');
        $this->RegisterPropertyInteger('TargetCategory', 0);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        $Devices = $this->getDevices();

        if (!array_key_exists('data', $Devices)) {
            $Devices = [];
        } else {
            $Devices = $Devices['data'];
        }

        $servicesID = 3000;

        $Values = [];
        $AddValue = [];
        foreach ($Devices as $key => $Device) {
            $Values[] = [
                'id'                    => $key + 1,
                'DeviceID'              => $Device['id'],
                'DisplayName'           => $Device['metadata']['name'],
                'name'                  => $Device['metadata']['name'],
                'Type'                  => $Device['type'],
                'ModelID'               => $Device['product_data']['model_id'],
                'Manufacturername'      => $Device['product_data']['manufacturer_name'],
                'Productname'           => $Device['product_data']['product_name'],
            ];
            foreach ($Device['services'] as $serviceKey => $Service) {
                if ($Service['rtype'] == 'entertainment') {
                    continue;
                }
                $servicesID++;
                $Values[] = [
                    'parent'                => $key + 1,
                    'id'                    => $servicesID,
                    'DeviceID'              => $Service['rid'],
                    'DisplayName'           => '',
                    'name'                  => '',
                    'Type'                  => $Service['rtype'],
                    'ModelID'               => '',
                    'Manufacturername'      => '',
                    'Productname'           => '',
                    'instanceID'            => $this->getInstanceID($Device['id'], $Service['rid']),
                    'create'                => [
                        'moduleID'      => $this->getModuleIDByType($Service['rtype']),
                        'configuration' => [
                            'DeviceID'      => strval($Device['id']),
                            'ResourceID'    => strval($Service['rid']),
                        ],
                        'name'     => $Device['metadata']['name'] . ' ' . ucfirst($Service['rtype']),
                        'location' => $this->getPathOfCategory($this->ReadPropertyInteger('TargetCategory'))
                    ]
                ];
            }
        }
        $Form['actions'][0]['values'] = $Values;
        return json_encode($Form);
    }

    private function getDevices()
    {
        $Data = [];
        $Buffer = [];

        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'getDevices';
        $Buffer['Params'] = '';
        $Data['Buffer'] = $Buffer;
        $Data = json_encode($Data);
        $result = json_decode($this->SendDataToParent($Data), true);
        if (!$result) {
            return [];
        }
        return $result;
    }

    private function getPathOfCategory(int $categoryId): array
    {
        if ($categoryId === 0) {
            return [];
        }

        $path[] = IPS_GetName($categoryId);
        $parentId = IPS_GetObject($categoryId)['ParentID'];

        while ($parentId > 0) {
            $path[] = IPS_GetName($parentId);
            $parentId = IPS_GetObject($parentId)['ParentID'];
        }

        return array_reverse($path);
    }

    private function getInstanceID($dID, $rID)
    {
        foreach (self::RESOURCES as $GUID) {
            $IDs = IPS_GetInstanceListByModuleID($GUID);
            foreach ($IDs as $id) {
                if ((strtolower(IPS_GetProperty($id, 'DeviceID')) == strtolower($dID)) && (strtolower(IPS_GetProperty($id, 'ResourceID')) == strtolower($rID))) {
                    return $id;
                }
            }
        }
        return 0;
    }

    private function getModuleIDByType($type)
    {
        return isset(self::RESOURCES[$type]) ? self::RESOURCES[$type] : self::RESOURCES['motion']; //TODO Default
    }
}
