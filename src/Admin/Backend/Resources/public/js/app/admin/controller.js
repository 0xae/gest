angular.module("app")
.controller("AdminController", ['$scope', '$http', '$q', function ($scope, $http, $q) {
    var url = new URL(location.href);
    var tab=url.searchParams.get('tab');
    var permissionMap=null;
    $scope.permissions = [];

    if (tab) { 
        $('#adminTab a[href="#'+tab+'"]').tab('show');  
    }

    $scope.profileChanged = function(id) {
        $scope.profileId = id;
        $scope.isLoading = true;
        $scope.profilePermissions = [];

        fetchPermissions(id)
        .then(function (rawPermissions){
            fetchPermissionsMap()
            .then(function (map){
                // $scope.profilePermissions = data;
                var data = {};
                var keys=Object.keys(map);

                rawPermissions.forEach(function (p){
                    // get group entry which this permission belongs to
                    var entry = getEntryWith(keys, map, function (v){ 
                        return v.code.toLowerCase() === p.permission.toLowerCase();
                    });
                    // create cache if it doenst exists
                    if (!data[entry.k]) {
                        data[entry.k]=[];                        
                    }
                    // update it
                    data[entry.k].push(p);
                });

                $scope.profilePermissions = data;
                $scope.isLoading = false;
            });
        }, function () {
            $scope.isLoading = true;            
        });
    }

    $scope.assocPermissions = function () {
        var profileId=$scope.profileId;
        var queue = $("#permissionsToAdd").val()
        .map(function (permission){
            return addPermissionPromise(profileId, permission);
        });

        $scope.isLoading = true;

        $q.all(queue)
        .then(function (_resp) {
            var resp=_resp.data;
            $.notify("Permissões adicionadas com sucesso", "success");
            $scope.profileChanged(profileId);
            return resp;
        }, function (error) {            
            $scope.isLoading = false;
            $.notify("A operacao nao pode ser efectuada.Tente novamente!", "danger");
        });
    }

    $scope.removeAssocPermissions = function() {
        if (!confirm("Deseja mesmo remover as permissões selecionadas?")) {
            return;
        }

        var permissions = $("#permissionsAssoc").val();
        var queue = permissions.map(function (p){
            return $http.get('/arfa/web/app_dev.php/administration/remove_permission/' + p); 
        });

        $q.all(queue)
        .then(function (done){
            $scope.profileChanged($scope.profileId);
            $.notify("Permissões removidas com sucesso", "success");            
        }, function (error){
            $.notify("A operacao nao pode ser efectuada.Tente novamente!", "danger");
        });
    }

    function addPermissionPromise(profileId, permissionId) {
        var permissionLabel = $("#perm-" + permissionId).attr("data-label");
        return $http.get('/arfa/web/app_dev.php/administration/add_permission?' + 
            'profile_id=' + profileId+
            '&permission=' + permissionId+
            '&permission_label=' + permissionLabel);
    }

    function fetchPermissions (profileId) { 
        return $http.get('/arfa/web/app_dev.php/administration/permissions_of/' + profileId)
        .then(function (_resp) {
            var resp=_resp.data;
            return resp;
        });
    }

    function fetchPermissionsMap() {
        var defer = $q.defer();
        if (permissionMap == null) {
            $http.get('/arfa/web/app_dev.php/administration/permission_map')
            .then(function (_resp) {
                permissionMap=_resp.data;
                defer.resolve(_resp.data);
            }, defer.reject);
        } else {
            defer.resolve(permissionMap);
        }
        return defer.promise;
    }

    function getEntryWith(keys, values, fn) {
        var value=null, found=false;    
        keys.forEach(function (k){
            var v=values[k];
            v.forEach(function (v1){
                if (fn(v1)) {
                    value={k:k, v:v1};
                    found=true;
                    return;
                }
            });

            if (found) return;
        });
        return value;
    }
}])

.controller('UploadController', ['$http', '$scope', function ($http, $scope) {
    $scope.remove = function (id, index) {        
        if (!confirm("Tem a certeza que pretende eliminar este anexo?")){
            return;
        }

        $http.get('/arfa/web/app_dev.php/administration/Upload/' + id+"/remove")
        .then(function (resp){
            var file = "#upload_" + id;
            $.notify("Anexo removido com sucesso.", "success");
            $(file).addClass('danger');
            setTimeout(function(){
                $(file).remove();                
            }, 1200);
        }, function (error) {
            $.notify("A operacao nao pode ser efectuada.Tente novamente!", "danger");            
        });
    }
}])

.controller('NavbarController', ['$scope', function ($scope){
    console.info("::NavbarController::");
    var url = new URL(location.href);
    var q=url.searchParams.get('q');

    if (q) {
        $scope.searchMode=true;
        setTimeout(function(){
            $("#query").val(q);
        });
    }

    $scope.enableSearch = function () {
        $scope.searchMode=true;
    }

    $scope.search = function() {
        console.info('query: ',$scope.query);
        var query=$("#query").val();
        if (!query || query.trim().length==0) {
            console.info(";NOP");
            return;
        }

        console.info("do the search");
        $('#search').submit();
    }
}]);
