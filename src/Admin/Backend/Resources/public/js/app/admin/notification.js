angular.module("app")
.controller("NotificationController", ['$scope', '$http', '$q', 
function ($scope, $http, $q) {
    var vm=this;
    
    vm.$onInit = function () {
        var ctx=$("#nt_context").attr("value");
        $http.get("/arfa/web/app_dev.php/administration/Notification?type="+ctx)
        .then(function (Resp){
            var ary=Resp.data;
            ary.forEach(function (N) {                
                N.objcode = getObjCode(N);
                N.days_left = parseInt(N.days_left || 0);
                N.type_label = getTypeLabel(ctx, N.type);
            });

            console.info("Ary: ", ary);
            $scope.all_notifications = ary;
            // $scope.notifications = ary.slice(0, 2);
            $scope.notifications = ary;
        });
    }

    $scope.loadMore = function () {
        $scope.notifications = $scope.all_notifications;
    }

    function getObjCode(obj){
        console.info("obj: ", obj);

        if (obj.type=='queixa') {
            return obj.id+'/QX/'+obj.department+'/'+obj.year;

        } else if (obj.type=='denuncia') {
            return obj.id+'/DN/'+obj.department+'/'+obj.year;

        } else if (obj.type=='reclamacao') {
            return obj.id+'/RE/'+obj.department+'/'+obj.year;

        } else if (obj.type=='sugestao') {
            return obj.id+'/SG/'+obj.department+'/'+obj.year;

        } else if (obj.type=='reclamation_internal') {
            return obj.id+'/RI/'+obj.department+'/'+obj.year;

        } else {
            return '#'+obj.id;
        }
    }

    function getTypeLabel(m, l) {
        if (l=='comp_book') {
            return "Livro de Reclamação"

        } else if (l=='queixa') {
            return 'Queixa';

        } else if (l=='denuncia') {
            return 'Denuncia';

        } else if (l=='reclamacao') {
            return 'Reclamação';

        } else if (l=='sugestao') {
            return 'Sugestão';

        } else if (m=='reclamation_internal') {
            return 'Reclamação interna';
        } else {
            return l;
        }
    }
}]);

