angular.module("app")
.controller("ComplaintViewController", ['$http', '$scope', function ($http, $scope) {
    var url = new URL(location.href);
    var isNew=url.searchParams.get('is_new');
    var uploadedAdded=url.searchParams.get('upload_added');
    
    $scope.categoryConf = {};

    // if (isNew) {
    //     $.notify("Objecto guardado com sucesso", "success");
    // }

    // if (uploadedAdded) {
    //     $.notify(
    //         "Anexo adicionado", 
    //         "success"
    //     );

    //     $('#editTab a[href="#tab2"]').tab('show');          
    // }
    $scope.setCategory = function (conf) {
        $scope.categoryConf = JSON.parse(conf);
        console.info("get ", $scope.categoryConf);        
    }

    $scope.onSubmitForm = function() {
        console.info($scope.categoryConf);
        console.info("set ", $scope.categoryConf);
        $("#admin_backend_complaint_complaintCategory").val(JSON.stringify($scope.categoryConf));
    }
}]);
