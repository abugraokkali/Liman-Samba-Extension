<?php 
    function index(){
        return view('index');
    }
    // == Tab 1 FSMO ==
    function tab1(){
        $allData = runCommand(sudo()."samba-tool fsmo show");
        $allDataList = explode("\n",$allData);
        $dict = [
            "SchemaMasterRole" => "schema",
            "InfrastructureMasterRole" => "infrastructure",
            "RidAllocationMasterRole" => "rid",
            "PdcEmulationMasterRole" => "pdc",
            "DomainNamingMasterRole" => "naming",
            "DomainDnsZonesMasterRole" => "domaindns",
            "ForestDnsZonesMasterRole" => "forestdns",
        ];
        $data = [];
        for($i=0; $i<count($allDataList); $i++){
            $item = $allDataList[$i];
            $itemList = explode(",",$item);

            $nameItem = explode("=",$itemList[1]);
            $nameItem = $nameItem[1];

            if ($nameItem != "") {
                $roleItem = explode(" ",$itemList[0]);
                $roleItem = $roleItem[0];
                $data[] = [
                    "role" => $roleItem,
                    "name" => $nameItem,
                    "contraction" => $dict[$roleItem]
                    
                ];  
            }
      
        }
        return view('table', [
            "value" => $data,
            "title" => ["Rol","Sunucu","*hidden*"],
            "display" => ["role","name","contraction:contraction"],
            "menu" => [

                "Bu rolü al" => [
                    "target" => "takeTheRole",
                    "icon" => "fa-share"
                ],
    
            ],
        ]);
    }

    function takeTheRole(){
        $contraction = request("contraction");
        $output=runCommand(sudo()."samba-tool fsmo transfer --role=$contraction -UAdministrator");
        if($output == ""){
            $output=runCommand(sudo()."samba-tool fsmo transfer --role=$contraction -UAdministrator 2>&1");
        }
        return respond($output,200);
    }
    function takeAllRoles(){
        $output=runCommand(sudo()."samba-tool fsmo transfer --role=all -UAdministrator");
        return respond($output,200);
    }
    function seizeTheRole(){
        $contraction = request("contraction");
        $output=runCommand(sudo()."samba-tool fsmo seize --role=$contraction -UAdministrator");
        return respond($output,200);
    }
    

    // == Tab 2 Migration ==
    function migrate(){
        $ip = request("ip");
        $username = request("username");
        $password = request("password");
        $output=runCommand(sudo()."smb-migrate-domain -s ".$ip." -a ".$username." -p ".$password,200);
        return respond($output,200);
    }

?>