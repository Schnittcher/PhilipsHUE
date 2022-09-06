<?php

declare(strict_types=1);
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php') . '}');

class HUEDiscovery extends IPSModule
{
    use \PhilipsHUE\DebugHelper;

    const CONFIGURATORS =
    [
        'Device Configurator'                 => '{52399872-F02A-4BEB-ACA0-1F6AE04D9663}',
        'Room Configurator'                   => '{943D4F07-294C-4FFC-98E1-82E78D3B4584}',
        'Zone Configurator'                   => '{2DCB7BB9-4634-4419-AE68-C0CC771547E5}'
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Bridges = $this->mDNSDiscoverBridges();

        $Values = [];
        $configuratorID = 9000;

        foreach ($Bridges as $key => $Bridge) {
            $Values[] = [
                'id'                    => $key + 1,
                'IPAddress'             => $Bridge['IPv4'],
                'name'                  => $Bridge['deviceName'],
                'ModelName'             => $Bridge['modelName'],
                'ModelNumber'           => $Bridge['modelNumber'],
                'SerialNumber'          => $Bridge['serialNumber']
            ];

            foreach (self::CONFIGURATORS as $configuratorKey => $Configurator) {
                $configuratorID++;
                $Values[] = [
                    'parent'                => $key + 1,
                    'id'                    => $configuratorID,
                    'IPAddress'             => '',
                    'name'                  => $this->Translate($configuratorKey),
                    'ModelName'             => '',
                    'ModelNumber'           => '',
                    'SerialNumber'          => '',
                    'instanceID'            => $this->getInstanceID($Bridge['serialNumber'], $this->getModuleIDByType($configuratorKey)),
                    'create'                => [
                        [
                            'moduleID'      => $this->getModuleIDByType($configuratorKey),
                            'configuration' => [
                                'Serialnumber' => $Bridge['serialNumber']
                            ]
                        ],
                        [
                            'moduleID'      => '{6786AF05-B089-4BD0-BABA-B2B864CF92E3}',
                            'configuration' => [
                                'Host' => $Bridge['IPv4']
                            ]
                        ]
                    ]
                ];
            }
        }
        $Form['actions'][0]['values'] = $Values;
        return json_encode($Form);
    }

    public function mDNSDiscoverBridges()
    {
        $mDNSInstanceIDs = IPS_GetInstanceListByModuleID('{780B2D48-916C-4D59-AD35-5A429B2355A5}');
        $resultServiceTypes = ZC_QueryServiceType($mDNSInstanceIDs[0], '_hue._tcp', '');
        $this->SendDebug('mDNS resultServiceTypes', print_r($resultServiceTypes, true), 0);
        $bridges = [];
        foreach ($resultServiceTypes as $key => $device) {
            $hue = [];
            $deviceInfo = ZC_QueryService($mDNSInstanceIDs[0], $device['Name'], '_hue._tcp', 'local.');
            $this->SendDebug('mDNS QueryService', $device['Name'] . ' ' . $device['Type'] . ' ' . $device['Domain'] . '.', 0);
            $this->SendDebug('mDNS QueryService Result', print_r($deviceInfo, true), 0);
            if (!empty($deviceInfo)) {
                $hue['Hostname'] = $deviceInfo[0]['Host'];
                if (empty($deviceInfo[0]['IPv4'])) { //IPv4 und IPv6 sind vertauscht
                    $hue['IPv4'] = $deviceInfo[0]['IPv6'][0];
                } else {
                    $hue['IPv4'] = $deviceInfo[0]['IPv4'][0];
                }
                $hueData = $this->readBridgeDataFromXML($hue['IPv4']);
                $hue['deviceName'] = (string) $hueData->device->friendlyName;
                $hue['modelName'] = (string) $hueData->device->modelName;
                $hue['modelNumber'] = (string) $hueData->device->modelNumber;
                $hue['serialNumber'] = (string) $hueData->device->serialNumber;
                array_push($bridges, $hue);
            }
        }
        return $bridges;
    }

    private function readBridgeDataFromXML($ip)
    {
        $XMLData = file_get_contents('http://' . $ip . ':80/description.xml');
        if ($XMLData === false) {
            return;
        }
        $Xml = new SimpleXMLElement($XMLData);

        $modelName = (string) $Xml->device->modelName;
        if (strpos($modelName, 'Philips hue bridge') === false) {
            return;
        }
        return $Xml;
    }

    private function getModuleIDByType($type)
    {
        return isset(self::CONFIGURATORS[$type]) ? self::CONFIGURATORS[$type] : self::CONFIGURATORS['Device Configurator']; //TODO Default
    }

    private function getInstanceID($Serialnumber, $GUID)
    {
        $IDs = IPS_GetInstanceListByModuleID($GUID);
        foreach ($IDs as $id) {
            if ((strtolower(IPS_GetProperty($id, 'Serialnumber')) == strtolower($Serialnumber))) {
                return $id;
            }
        }
        return 0;
    }
}
