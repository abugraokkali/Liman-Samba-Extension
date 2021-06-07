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
    // == LDAP ==
    function connect(){
        $domainname= "ali.lab";
        $user = "administrator@".$domainname;
        $pass = "123123Aa";
        $server = 'ldaps://192.168.1.68';
        $port="636";
        $binddn = "DC=ali,DC=lab";
        $ldap = ldap_connect($server);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        //ldap_start_tls($ldap);
    
        $bind=ldap_bind($ldap, $user, $pass);
        if (!$bind) {
            exit('Binding failed');
        }
        return $ldap;
    }
    function close($ldap){
        ldap_close($ldap);
    }
    function ldapsearchuser($cn,$dn,$domainname,$ldap) {
        $dn_user="CN=".$cn;
        $search = ldap_search($ldap, $dn, $dn_user);
        $info = ldap_get_entries($ldap, $search);
        return $info;
    }

    function list_users(){
        $ldap = connect();

        $filter = "objectClass=user";
        $result = ldap_search($ldap, "CN=Users,DC=ali,DC=lab", $filter);
        $entries = ldap_get_entries($ldap,$result);

        $count = ldap_count_entries($ldap, $result);
        $data = [];
        for($i=0 ; $i<$count ; $i++){
            $nameItem = $entries[$i]["name"][0];
            $data[] = [
                "name" => $nameItem
            ];
        }
        close($ldap);

        return view('table', [
            "value" => $data,
            "title" => ["Users"],
            "display" => ["name"]
        ]);

    }

    function list_computers(){
        $ldap = connect();

        $filter = "objectClass=computer";
        $result = ldap_search($ldap, "DC=ali,DC=lab", $filter);
        $entries = ldap_get_entries($ldap,$result);

        $count = ldap_count_entries($ldap, $result);
        $data = [];
        for($i=0 ; $i<$count ; $i++){
            $nameItem = $entries[$i]["name"][0];
            $data[] = [
                "name" => $nameItem
            ];
        }
        close($ldap);

        return view('table', [
            "value" => $data,
            "title" => ["Computers"],
            "display" => ["name"]
        ]);

    }
    function list_attributes(){
        $ldap = connect();
        $cn="administrator";

        $dn_user="CN=".$cn;

        $result = ldap_search($ldap, "DC=ali,DC=lab", $dn_user);
        $entries = ldap_get_entries($ldap,$result);

        $data=[];
        for($i=0 ; $i<$entries[0]["count"] ; $i++){
            $name = $entries[0][$i];
            for($j=0 ; $j<$entries[0][$name]["count"] ; $j++){
                $value = $entries[0][$name][$j];
                $data[] = [
                    "name" => $name,
                    "value" => $value
                ];
            }
        }
        ldap_close($ldap);
        return view('table', [
            "value" => $data,
            "title" => ["Attribute Name","Value"],
            "display" => ["name","value"]
        ]);

    }

    
?>