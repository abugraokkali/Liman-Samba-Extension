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
        <button class="btn btn-success mb-2" id="btn3" onclick="showMigrationModal()" type="button">Migrate et</button>
        <div class="text-area" id="textarea"></div>
    </div>
    
</div>

<script>

   if(location.hash === ""){
        tab1();
    }
    // == Tab1 FSMO ==
    //table
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
    //role transfer
    function takeTheRole(line){
        var form = new FormData();
        let contraction = line.querySelector("#contraction").innerHTML;
        form.append("contraction",contraction);

        request(API('takeTheRole'), form, function(response) {
            message = JSON.parse(response)["message"];
            if(message == ""){
                showSwal('Hata oluştu.', 'error', 7000);
            }
            else if(message.includes("successful")){
                tab1();
                showSwal(message,'success',7000);
            }
            else{
                showSwal(message,'info',7000);
            }
        }, function(error) {
            showSwal(error.message, 'error', 5000);

        });
    }
    //information modal
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
    //change modal
    function showChangeModal(){
        showSwal('Yükleniyor...','info',2000);
        $('#changeModal').modal("show");
    }
    function hideChangeModal(){
        var form = new FormData();
        form.append("contraction", $('#changeModal').find('select[name=newType]').val());
        request(API('takeTheRole'), form, function(response) {
            message = JSON.parse(response)["message"];
            $('#changeModal').modal("hide");
            if(message == ""){
                showSwal('Hata oluştu.', 'error', 7000);
            }
            else if(message.includes("successful")){
                tab1();
                showSwal(message,'success',7000);
            }
            else{
                showSwal(message,'info',7000);
            }

        }, function(error) {
            $('#changeModal').modal("hide");
            showSwal(error.message, 'error', 5000);
        });
    }
    // == Tab2 Migration ==

    function showMigrationModal(){
        showSwal('Yükleniyor...','info',2000);
        $('#migrationModal').modal("show");
    }
    function hideMigrationModal(){
        var form = new FormData();
        
        form.append("ip", $('#migrationModal').find('input[name=ipAddr]').val());
        form.append("username", $('#migrationModal').find('input[name=username]').val());
        form.append("password", $('#migrationModal').find('input[name=password]').val());

        request(API('migrate'), form, function(response) {
            message = JSON.parse(response)["message"];
            showSwal(message,'info',7000);
            $('#migrationModal').modal("hide");

        }, function(error) {
            $('#migrationModal').modal("hide");
            showSwal(error.message, 'error', 5000);
        });
    }


</script>