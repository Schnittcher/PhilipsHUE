<?php

declare(strict_types=1);
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php') . '}');

class Discovery extends IPSModule
{
    use \PhilipsHUE\DebugHelper;

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

        foreach ($Bridges as $Bridge) {
            $instanceID = $this->getHUEBridgeInstances($Bridge['serialNumber']);

            $AddValue = [
                'IPAddress'             => $Bridge['IPv4'],
                'name'                  => $Bridge['deviceName'],
                'ModelName'             => $Bridge['modelName'],
                'ModelNumber'           => $Bridge['modelNumber'],
                'SerialNumber'          => $Bridge['serialNumber'],
                'instanceID'            => $instanceID
            ];

            $AddValue['create'] = [
                [
                    'moduleID'      => '{52399872-F02A-4BEB-ACA0-1F6AE04D9663}',
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

            ];

            $Values[] = $AddValue;
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

    private function getHUEBridgeInstances($Serialnumber)
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID('{EE92367A-BB8B-494F-A4D2-FAD77290CCF4}');
        foreach ($InstanceIDs as $id) {
            if (IPS_GetProperty($id, 'Serialnumber') == $Serialnumber) {
                return $id;
            }
        }
        return 0;
    }
}
