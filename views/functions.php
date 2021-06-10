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
        runCommand(sudo()."smb-migrate-domain -s ".$ip." -a ".$username." -p ".$password,200);

        if(check2() == true){
            //migrate edilebilir yani migrate edilmemiş.
            return respond(true,200);
        }
        else{
            return respond(false,200);
        }
        

    }
    function check(){
        //check => true ise migrate edilebilir.
        $output=runCommand(sudo()."net ads info",200);
        if($output==""){
            $output=runCommand(sudo()."net ads info 2>&1",200);
        }
        if(str_contains($output, "Can't load /etc/samba/smb.conf")){
            return respond(true,200);
        }
        else{
            return respond(false,200);
        }
    }
    function check2(){
        //check => true migrate edilebilir.
        $output=runCommand(sudo()."net ads info",200);
        if($output==""){
            $output=runCommand(sudo()."net ads info 2>&1",200);
        }
        if(str_contains($output, "Can't load /etc/samba/smb.conf")){
            return true;
        }
        else{
            return false;
        }
    }
   

    
?>