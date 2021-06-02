@component('modal-component',[
        "id" => "infoModal",
        "title" => "Sonuç Bilgisi",
        "footer" => [
            "text" => "OK",
            "class" => "btn-success",
            "onclick" => "hideInfoModal()"
        ]
    ])
@endcomponent

@component('modal-component',[
        "id" => "changeModal",
        "title" => "Rol Seçimi",
        "footer" => [
            "text" => "AL",
            "class" => "btn-success",
            "onclick" => "hideChangeModal()"
        ]
    ])
    @include('inputs', [
        "inputs" => [
            "Roller:newType" => [
                "SchemaMasterRole" => "schema",
                "InfrastructureMasterRole" => "infrastructure",
                "RidAllocationMasterRole" => "rid",
                "PdcEmulationMasterRole" => "pdc",
                "DomainNamingMasterRole" => "naming",
                "DomainDnsZonesMasterRole" => "domaindns",
                "ForestDnsZonesMasterRole" => "forestdns",
                "All" => "all"
            ]
        ]
    ])
@endcomponent

@component('modal-component',[
        "id" => "migrationModal",
        "title" => "Giriş",
        "footer" => [
            "text" => "OK",
            "class" => "btn-success",
            "onclick" => "hideMigrationModal()"
        ]
    ])
    @include('inputs', [
        "inputs" => [
            "IP Addresi" => "ipAddr:text",
            "Kullanıcı Adı" => "username:text",
            "Şifre" => "password:password"
        ]
    ])
@endcomponent


<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
    <li class="nav-item">
        <a class="nav-link active"  onclick="tab1()" href="#tab1" data-toggle="tab">FSMO Rol Yönetimi</a>
    </li>
    <li class="nav-item">
        <a class="nav-link " href="#tab2" data-toggle="tab">Migration İşlemi</a>
    </li>
</ul>

<div class="tab-content">
    <div id="tab1" class="tab-pane active">
        <h1>{{ __(' FSMO Rol Yönetimi') }}</h1>
        <br />
        <button class="btn btn-success mb-2" id="btn1" onclick="showInfoModal()" type="button">Tüm rolleri al</button>
        <button class="btn btn-success mb-2" id="btn2" onclick="showChangeModal()" type="button">Belirli bir rolü al</button>
        <div class="table-responsive" id="fsmoTable"></div>
    </div>

    <div id="tab2" class="tab-pane">
        <h1>{{ __(' Migration İşlemleri') }}</h1>
        <br />
        <button class="btn btn-success mb-2" id="btn3" onclick="showMigrationModal()" type="button">Migrate</button>
        <button class="btn btn-success mb-2" id="btn4" onclick="ldap()" type="button">LDAP</button>
        <div class="text-area" id="textarea"></div>
    </div>
    
</div>

<script>

   if(location.hash === ""){
        tab1();
    }
    // #### Tab1 FSMO ####

    // == Printing Table ==
    function tab1(){
        showSwal('Yükleniyor...','info',2000);
        var form = new FormData();
        request(API('tab1'), form, function(response) {
            $('#fsmoTable').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
            });;
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
        
    }
    // == Transfer Role ==
    function takeTheRole(line){
        var form = new FormData();
        let contraction = line.querySelector("#contraction").innerHTML;
        form.append("contraction",contraction);

        request(API('takeTheRole'), form, function(response) {
            message = JSON.parse(response)["message"];
            if(message.includes("successful")){
                tab1();
                showSwal(message,'success',7000);
            }
            else if(message.includes("already")){
                showSwal(message,'info',7000);
            }
            else if(message.includes("WERR_FILE_NOT_FOUND")){
                showSwal('WERR_FILE_NOT_FOUND \nTrying to seize... ','info',5000);
                seizeTheRole(contraction);
            }                
            else{
                showSwal(message, 'error', 7000);
            }
        }, function(error) {
            showSwal(error.message, 'error', 5000);
        });
    }

    // == Seize Role ==
    function seizeTheRole(contraction){
        var form = new FormData();
        form.append("contraction",contraction);
        request(API('seizeTheRole'), form, function(response) {
            message = JSON.parse(response)["message"];
            tab1();
            showSwal(message, 'success', 5000); 
            
        }, function(error) {
            showSwal(error.message, 'error', 5000);
        });
    }

    // == Information Modal ==
    function showInfoModal(line){
        showSwal('Yükleniyor...','info',3500);
        var form = new FormData();
        request(API('takeAllRoles'), form, function(response) {
            message = JSON.parse(response)["message"];
            $('#infoModal').find('.modal-body').html(
                "<pre>"+message+"</pre>"
            );
            $('#infoModal').modal("show");
        }, function(error) {
            showSwal(error.message, 'error', 5000);

        });
    }
    function hideInfoModal(line){
        $('#infoModal').modal("hide");
        tab1();
    }

    // == Change Modal ==
    function showChangeModal(){
        showSwal('Yükleniyor...','info',2000);
        $('#changeModal').modal("show");
    }
    function hideChangeModal(){
        var form = new FormData();
        let contraction = $('#changeModal').find('select[name=newType]').val();
        form.append("contraction",contraction);
        $('#changeModal').modal("hide");
        showSwal('Yükleniyor...','info',5000);
        request(API('takeTheRole'), form, function(response) {
            message = JSON.parse(response)["message"];
            if(message.includes("successful")){
                tab1();
                showSwal(message,'success',7000);
            }
            else if(message.includes("already")){
                showSwal(message,'info',7000);
            }
            else if(message.includes("WERR_FILE_NOT_FOUND")){                
                showSwal('WERR_FILE_NOT_FOUND \nTrying to seize... ','info',5000);
                seizeTheRole(contraction);
            }                
            else{
                showSwal('Hata oluştu.', 'error', 7000);
            }
        }, function(error) {
            $('#changeModal').modal("hide");
            showSwal(error.message, 'error', 5000);
        });
    }


    // #### Tab2 Migration ####

    function showMigrationModal(){
        showSwal('Yükleniyor...','info',2000);
        $('#migrationModal').modal("show");
    }
    function hideMigrationModal(){
        var form = new FormData();
        $('#migrationModal').modal("hide");
        form.append("ip", $('#migrationModal').find('input[name=ipAddr]').val());
        form.append("username", $('#migrationModal').find('input[name=username]').val());
        form.append("password", $('#migrationModal').find('input[name=password]').val());
        
        request(API('migrate'), form, function(response) {
            message = JSON.parse(response)["message"];
            $('#textarea').html(
                "<pre>"+message+"</pre>"
            );
        }, function(error) {
            showSwal(error.message, 'error', 5000);
        });
    }


</script>