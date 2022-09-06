<?php

declare(strict_types=1);

class HUESceneConfigurator extends IPSModule
{
    const RESOURCES =
        [
            'scene'                 => '{53C7BFDF-C0CE-4837-82E3-654FAF7126FB}'
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

        $Scenes = $this->getScenes();

        if (!array_key_exists('data', $Scenes)) {
            $Scenes = [];
        } else {
            $Scenes = $Scenes['data'];
        }

        $Values = [];
        $AddValue = [];
        foreach ($Scenes as $key => $Scene) {
            $RoomName = $this->getRoomName($Scene['group']['rid']);
            $Values[] = [
                'id'                          => $key + 1,
                'SceneID'                     => $Scene['id'],
                'DisplayName'                 => $Scene['metadata']['name'],
                'name'                        => $Scene['metadata']['name'],
                'Type'                        => $Scene['type'],
                'RoomName'                    => $RoomName,
                'RoomID'                      => $Scene['group']['rid'],
                'instanceID'                  => $this->getInstanceID($Scene['id']),
                'create'                      => [
                    'moduleID'      => $this->getModuleIDByType($Scene['type']),
                    'configuration' => [
                        'ResourceID'        => strval($Scene['id']),
                    ],
                    'name'     => $RoomName . ' ' . $Scene['metadata']['name'] . ' ' . ucfirst($Scene['type']),
                    'location' => $this->getPathOfCategory($this->ReadPropertyInteger('TargetCategory'))
                ]
            ];
        }
        $Form['actions'][0]['values'] = $Values;
        return json_encode($Form);
    }

    private function getScenes()
    {
        $Data = [];
        $Buffer = [];

        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'getScenes';
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

    private function getInstanceID($sID)
    {
        foreach (self::RESOURCES as $GUID) {
            $IDs = IPS_GetInstanceListByModuleID($GUID);
            foreach ($IDs as $id) {
                if ((strtolower(IPS_GetProperty($id, 'ResourceID')) == strtolower($sID))) {
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

    private function getRoomName($roomID)
    {
        $Data = [];
        $Buffer = [];

        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'getRooms';
        $Buffer['Params'] = '';
        $Data['Buffer'] = $Buffer;
        $Data = json_encode($Data);
        $result = json_decode($this->SendDataToParent($Data), true);
        if (!$result) {
            return '';
        }

        foreach ($result['data'] as $key => $room) {
            if ($roomID == $room['id']) {
                return $room['metadata']['name'];
            }
        }
        return 'Undefined Name';
    }
}
