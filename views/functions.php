<?php 
    function index(){
        return view('index');
    }

    function tab1(){
        $allData = runCommand(sudo()."samba-tool fsmo show");
        $allDataList = explode("\n",$allData);

        $data = [];
        for($i=0; $i<count($allDataList); $i++){
            $item = $allDataList[$i];
            $itemList = explode(",",$item);

            $nameList = explode("=",$itemList[1]);
            $nameItem = $nameList[1];

            if ($nameItem != "") {
                $roleList = explode(" ",$itemList[0]);
                $roleItem = $roleList[0];

                $data[] = [
                    "role" => $roleItem,
                    "name" => $nameItem
                    
                ];
                
            }
      
        }
        
           
        return view('table', [
            "value" => $data,
            "title" => ["Role","Server"],
            "menu" => [

                "Take on the role" => [

                ],
    
            ],
            "display" => ["role","name"],
        ]);
    }

    function tab2(){
        return respond(runCommand(sudo()."samba-tool fsmo show"),200);
    }
    
?>