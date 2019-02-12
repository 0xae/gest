angular.module('app', []);

angular.module('app')
.controller('Category2Controller', [
    '$scope', '$http',
    function ($scope, $http) {
        $scope.delete_holy = function(id) {
            if (!confirm('Deseja mesmo eliminar esta data?')) {
                return;
            }

            $http.post('./'+id+'/delete')
            .then(function (Resp){
                var data=Resp.data;
                if (data.status=='deleted') {
                    var to_delete="#holy-"+id;
                    $(to_delete).fadeOut(350);
                    $.notify("Eliminado com sucesso", "success");
                } else {
                    $.notify("Nao foi possivel eliminar este objecto. Tente de novo", "danger");
                }
            }, function (Err){
                $.notify("Nao foi possivel eliminar este objecto. Tente de novo", "danger");
                console.error('Err: ', Err);
            });
        }
    }
]);

$("#business-privacy-descr").trumbowyg({
    btnsAdd: ['foreColor', 'backColor']
}).on('tbwchange', function(e){
    $("#privacy_content_input").val(e.target.value);
});

var url = new URL(location.href);
var isNew=url.searchParams.get('is_new');
var tab=url.searchParams.get('tab');
var isUpdated=url.searchParams.get('is_updated') || url.searchParams.get('is_update');
var uploadedAdded=url.searchParams.get('upload_added');
var flashMsg=url.searchParams.get('flash_msg');

if (isNew) {
    $.notify("Objecto guardado com sucesso", "success");
}

if (isUpdated) {
    $.notify("Objecto actualizado com sucesso", "success");    
}

if (flashMsg) {
    $.notify(flashMsg, "success");
}

if (uploadedAdded) {
    $.notify("Anexo adicionado",  "success");
    $('#editTab a[href="#tab2"]').tab('show');          
}

if (tab) {
    $('a[href="#'+tab+'"]').tab('show');    
}

$(".app-print-page").on("click", function () {
    window.print();
});

$("ul.pagination").addClass("hidden-print");

$(window).load(function() {
    $(".loading").fadeOut("slow");;
});

$(document).ready(function() {
    $(".datatable").dataTable({
        "language" : {
            "sProcessing" : "A processar...",
            "sLengthMenu" : "Mostrar _MENU_ registos",
            "sZeroRecords" : "Não foram encontrados resultados",
            "sInfo" : "Mostrando de _START_ até _END_ de _TOTAL_ registos",
            "sInfoEmpty" : "Mostrando de 0 até 0 de 0 registos",
            "sInfoFiltered" : "(filtrado de _MAX_ registos no total)",
            "sInfoPostFix" :  "",
            "sSearch" : "Pesquisar ",
            "sUrl" : "",
            "oPaginate" : {
                "sFirst" : "Primeiro",
                "sPrevious" : "Anterior",
                "sNext" : "Seguinte",
                "sLast" : "Último"
            }
        }        
    });

    var currYear=moment().format('YYYY');

    $('#admin_backend_category_name').daterangepicker({
        locale: {
            format: 'DD/MM/YYYY',
        },
        singleDatePicker: true,
        showDropdowns: true,
        minYear: currYear,
        maxYear: parseInt(currYear, 10),
        "applyLabel": "Aplicar",
        "cancelLabel": "Cancelar",
        "fromLabel": "Comeco",
        "toLabel": "Fim",
        "daysOfWeek": [
            "Dom",
            "Seg",
            "Ter",
            "Qua",
            "Qui",
            "Sex",
            "Sab"
        ],
        "monthNames": [
            "Janeiro",
            "Fevereiro",
            "Março",
            "Abril",
            "Maio",
            "Junho",
            "Julho",
            "Agosto",
            "Setembro",
            "Outubro",
            "Novembro",
            "Dezembro"
        ],
    }, function(start, end, label) {
        // var years = moment().diff(start, 'years');
        setTimeout(function () {
            // body...
            var date=moment(start).format('DD/MM/YYYY');
            $('#admin_backend_category_name').val(date);
            console.info('date: ', date);
        }, 0);
    });
});


$("input[data-disabled='data-disabled'], select[data-disabled='data-disabled']").each(function (){
    $(this).attr("ng-disabled", "true");
});

