angular.module("app")
.controller("ComplaintViewController", ['$http', '$scope', 'UploadService', '$timeout',
function ($http, $scope, UploadService, $timeout) {
    var url = new URL(location.href);
    var isNew=url.searchParams.get('is_new');
    var uploadedAdded=url.searchParams.get('upload_added');
    this.$onInit = init;
    $scope.entity_cat = {val: 'm1'}
    $scope.sector_descr1='Outros'
    $scope.sector_descr2='Outros';

    $scope.loadForm = function (sector, category, sector_descr) {
        $timeout(function () {
            $scope.entity_sector = sector;

            if (category) {
                console.info("category: ", category);
                $scope.entity_cat.val = category;
                $("#admin_backend_complaint_complaintCategory").val($scope.entity_cat.val);
            }

            setTimeout(function() {            
                if ($scope.entity_sector=='farm') {
                    $("#sector_descr1").val(sector_descr);
                } else {
                    $("#sector_descr2").val(sector_descr);
                }
            });

            // sector_descr
            $scope.$apply();
        });
    }

    $("#admin_backend_complaint_sector").on("change", function (tgt){
        $scope.entity_sector=tgt.target.value;
        $scope.$apply();
    });

    function init() {
        // body...
        console.info("onInit:: yesss");
    }

    $scope.onSubmitForm = function() {
        // console.info($scope.categoryConf);
        // console.info("set ", $scope.categoryConf);
        // console.info("val: ", $scope.entity_cat.val);
        // console.info("value: ", $("#admin_backend_complaint_complaintCategory").val());
        $("#admin_backend_complaint_complaintCategory").val($scope.entity_cat.val);
        
        var others="";
        var sector_descr1=$("#sector_descr1").val();
        var sector_descr2=$("#sector_descr2").val();
        if ($scope.entity_sector=='farm') {
            others = sector_descr1;
        } else {
            others = sector_descr2;
        }

        console.info("sector_descr1: ", sector_descr1);
        console.info("sector_descr2: ", sector_descr2);
        console.info("others: ", others);

        $("#admin_backend_complaint_sectorDescr").val(others);

        setTimeout(function(){
            $("#admin_backend_complaint_submit").click();
        }, 120);
    }
}]);
