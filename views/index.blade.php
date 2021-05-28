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
    </div>

    <div id="tab2" class="tab-pane">
    </div>

</div>


<script>
//java script
   if(location.hash === ""){
        tab1();
    }

    function tab1(){
        showSwal('{{__("Yükleniyor...")}}','info',2000);
        var form = new FormData();
        request(API('tab1'), form, function(response) {
            $('#tab1').html(response).find('table').DataTable({
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

    function tab2(){
        var form = new FormData();
        request("{{API('tab2')}}", form, function(response) {
            message = JSON.parse(response)["message"];
            $('#tab2').html(message);
        }, function(error) {
            $('#tab2').html("Hata oluştu");
        });
    }

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
    
</script>