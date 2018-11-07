angular.module("app")
.controller("CompBookController", 
['$scope', 'CompBook', function ($scope, CompBook) {
    var MODAL="#viewCompBook";

    $scope.viewObject = function (id) {
        $scope.entity = undefined;
        $scope.req = {};

        CompBook.get(id)
        .then(function (data){
            console.info("Compbook: ", data);
            $scope.entity = data;    
            $scope.files = data.files;
            $scope.req.sendTo = data.sendTo;  
            $scope.req.sendDate = data.sendDate;

            $("#admin_backend_upload_reference").val(data.annexReference);
            $("#admin_backend_upload_context").val(JSON.stringify({}));
            $(MODAL).modal();
        });
    }

    $scope.req = {
        req: new Date()
    };

    $scope.sendAcomp = function() {
        if (!confirm("Confirmar resposta para o livro?")) {
            return;            
        }

        CompBook.updateComp($scope.entity.id, $scope.req)
        .then(function (done) {
            $("#td-"+$scope.entity.id+"-sendTo").text($scope.req.sendTo);
            $("#td-"+$scope.entity.id+"-sendDate").text($scope.req.sendDate);            
            $.notify("Respondido com sucesso.", "success");

            $(MODAL).modal('close');
            $scope.entity = undefined;
        });
    }
}]);
