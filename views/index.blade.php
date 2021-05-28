@component('modal-component',[
        "id" => "infoModal",
        "title" => "Informations",
        "submit_text" => "Hostname Değiştir",
        "footer" => [
            "text" => "OK",
            "class" => "btn-success",
            "onclick" => "hideInfoModal()"
        ]
        
    ])
@endcomponent

<h1>{{ __(' FSMO Role Management') }}</h1>
<br />
<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
    <li class="nav-item">
        <a class="nav-link active"  onclick="tab1()" href="#tab1" data-toggle="tab">Table</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="tab2()" href="#tab2" data-toggle="tab">Text</a>
    </li>
</ul>

<div class="tab-content">
    <div id="tab1" class="tab-pane active">
        <button class="btn btn-success mb-2" id="asd" onclick="showInfoModal()" type="button">Take on all roles</button>
        <div class="table-responsive" id="fsmoTable"></div>
    </div>

    <div id="tab2" class="tab-pane">
    </div>
    
</div>

<script>
//java script
   if(location.hash === ""){
        tab1();
    }
    //table
    function tab1(){
        showSwal('{{__("Loading...")}}','info',2000);
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
    //text
    function tab2(){
        var form = new FormData();
        request("{{API('tab2')}}", form, function(response) {
            message = JSON.parse(response)["message"];
            $('#tab2').html(message);
        }, function(error) {
            $('#tab2').html("Hata oluştu");
        });
    }
    //role transfer
    function takeTheRole(line,a){
        var form = new FormData();
        let contraction = line.querySelector("#contraction").innerHTML;
        form.append("contraction",contraction);

        request("{{API('takeTheRole')}}", form, function(response) {
            message = JSON.parse(response)["message"];
            if(message == ""){
                showSwal('Error', 'error', 7000);
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
    //modal
    function showInfoModal(line,a){
        showSwal('{{__("Loading...")}}','info',3500);
        var form = new FormData();
        request("{{API('takeAllRoles')}}", form, function(response) {
            message = JSON.parse(response)["message"];
            /*var x = ""; 
            for(var i=0;i<message.length;i++){
                x += message[i] + "<br>";
            }*/
            $('#infoModal h4.modal-title').html("Result");
            $('#infoModal').find('.modal-body').html(
                "<pre>"+message+"</pre>"
            );
            $('#infoModal').modal("show");
        }, function(error) {
            showSwal(error.message, 'error', 5000);

        });
    }
    function hideInfoModal(line,a){
        $('#infoModal').modal("hide");
    }
    
</script>