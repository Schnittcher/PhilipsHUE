<?php

declare(strict_types=1);

class HUEZoneConfigurator extends IPSModuleStrict
{
    public const RESOURCES =
        [
            'grouped_light'                 => '{6324AC4A-330C-4CB2-9281-12EECB450024}'
        ];

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('Serialnumber', '');
        $this->RegisterPropertyInteger('TargetCategory', 0);
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetCompatibleParents(): string
    {
        return json_encode(['type' => 'connect', 'moduleIDs' => ['{6786AF05-B089-4BD0-BABA-B2B864CF92E3}']]);
    }

    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        if (!$this->HasActiveParent()) {
            array_unshift($Form['elements'], ['type' => 'Label', 'caption' => 'The bridge is not connected. Open the bridge instance and pair it with IP-Symcon first.']);
            return json_encode($Form);
        }

        $Zones = $this->getZones();

        if (!array_key_exists('data', $Zones)) {
            $Zones = [];
        } else {
            $Zones = $Zones['data'];
        }

        $servicesID = 3000;

        $Values = [];
        $AddValue = [];
        foreach ($Zones as $key => $Zone) {
            $Values[] = [
                'id'                    => $key + 1,
                'ZoneID'                => $Zone['id'],
                'DisplayName'           => $Zone['metadata']['name'],
                'name'                  => $Zone['metadata']['name'],
                'Type'                  => $Zone['type'],
            ];
            foreach ($Zone['services'] as $serviceKey => $Service) {
                $servicesID++;
                $Values[] = [
                    'parent'                => $key + 1,
                    'id'                    => $servicesID,
                    'ZoneID'                => $Service['rid'],
                    'DisplayName'           => '',
                    'name'                  => '',
                    'Type'                  => $Service['rtype'],
                    'instanceID'            => $this->getInstanceID($Zone['id'], $Service['rid']),
                    'create'                => [
                        'moduleID'      => $this->getModuleIDByType($Service['rtype']),
                        'configuration' => [
                            'RoomZoneID'        => strval($Zone['id']),
                            'ResourceID'        => strval($Service['rid']),
                        ],
                        'name'     => $Zone['metadata']['name'] . ' ' . ucfirst($Service['rtype']),
                        'location' => $this->getPathOfCategory($this->ReadPropertyInteger('TargetCategory'))
                    ]
                ];
            }
        }
        $Form['actions'][0]['values'] = $Values;
        return json_encode($Form);
    }

    private function getZones()
    {
        $Data = [];
        $Buffer = [];

        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'getZones';
        $Buffer['Params'] = '';
        $Data['Buffer'] = $Buffer;
        $Data = json_encode($Data);
        $result = json_decode((string) $this->SendDataToParent($Data), true);
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
                if ((strtolower(IPS_GetProperty($id, 'RoomZoneID')) == strtolower($dID)) && (strtolower(IPS_GetProperty($id, 'ResourceID')) == strtolower($rID))) {
                    return $id;
                }
            }
        }
        return 0;
    }

    private function getModuleIDByType($type)
    {
        return isset(self::RESOURCES[$type]) ? self::RESOURCES[$type] : self::RESOURCES['grouped_light']; //TODO Default
    }
}
