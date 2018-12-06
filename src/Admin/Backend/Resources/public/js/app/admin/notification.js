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

    // /arfa/web/app_dev.php/images/6a09227_arfa_1.jpg

    function pad(n) {
        var width=3;
        var z = '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

    function getObjCode(obj){
        console.info("obj: ", obj);

        if (obj.type=='queixa') {
            return pad(obj.id)+'/QX/'+obj.department+'/'+obj.year;

        } else if (obj.type=='denuncia') {
            return pad(obj.id)+'/DN/'+obj.department+'/'+obj.year;

        } else if (obj.type=='reclamacao') {
            return pad(obj.id)+'/RE/'+obj.department+'/'+obj.year;

        } else if (obj.type=='sugestao') {
            return pad(obj.id)+'/SG/'+obj.department+'/'+obj.year;

        } else if (obj.type=='reclamation_internal') {
            return pad(obj.id)+'/RI/'+obj.department+'/'+obj.year;

        } else {
            return '#'+pad(obj.id);
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

