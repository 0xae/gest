angular.module("app")
.controller("StatisticsController", ['$scope', 'Statistics',
function ($scope, Statistics) {
    console.info("--- init statistics controller ---");
    var startMonth=moment().format("YYYY-MM-DD");
    var endMonth=moment(startMonth).add('1', 'month').format('YYYY-MM-DD');
    var currYear=moment().format('YYYY');

    $('input[name="daterange"]').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
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
        },
        linkedCalendars: false,
        startDate: startMonth,
        endDate: endMonth
    }, function(start, end, label) {
        var startFmt = start.format('YYYY-MM-DD') + " 00:00:00";
        var endFmt = end.format('YYYY-MM-DD') + " 23:59:59";
        var yearFmt = start.format('YYYY');

        renderResponseTimeAvg(startFmt, endFmt);
        renderPerMonth(yearFmt);
        renderByDepartments(startFmt, endFmt);
        renderTypeDistribution(startFmt, endFmt);
        renderImcumprimentoPerDirection(startFmt, endFmt);
    });

    function renderByDepartments(start, end) {
        Statistics.fetchData('by_department', {start: start, end: end})
        .then(function (data) {
            var rows = data.rows;
            var categories = Object.keys(rows);
            var series = [{
                name: 'Denúncias',
                data: Statistics.produceArray(rows, 'denuncia'),
                color: '#681133'
                },{
                    name: 'Queixas',
                    data: Statistics.produceArray(rows, 'queixa'),
                    color: '#c82061'
                },{
                    name: 'Reclamaçao Interna',
                    data: Statistics.produceArray(rows, 'reclamacao_interna'),
                    color: '#6eb63e'
                },{
                    name: 'Reclamaçao Externa',
                    data: Statistics.produceArray(rows, 'reclamacao'),
                    color: '#4e802c'
                },{
                    name: 'Sugestões',
                    data: Statistics.produceArray(rows, 'sugestao'),
                    color: '#1155cc'
                },{
                    name: 'Livro de reclamações',
                    data: Statistics.produceArray(rows, 'comp_book'),
                    color: '#f39c12'
                }
            ];

            Statistics.renderStack(
                "by_department", 
                'Total de ocorrência por direção',
                categories, 
                series
            );
        });
    }

    function renderResponseTimeAvg(start, end) {
        // start=start + " 00:00:00";
        // var end=moment(start).add('7', 'days').format("YYYY-MM-DD") + " 23:59:59";
        Statistics.fetchData('avgResponseTime', {start:start, end:end})
        .then(function (data){
            var rows=data.rows;
            var categories=Object.keys(rows);
            var series = [{
                name: 'Denúncias',
                data: Statistics.produceArray(rows, 'denuncia'),
                color: '#681133'
            }, {
                name: 'Queixas',
                data: Statistics.produceArray(rows, 'queixa'),
                color: '#c82061'
            }, {
                name: 'Reclamaçao Interna',
                data: Statistics.produceArray(rows, 'reclamacao_interna'),
                color: '#6eb63e'
            }, {
                name: 'Reclamaçao Externa',
                data: Statistics.produceArray(rows, 'reclamacao'),
                color: '#4e802c'
            }, {
                name: 'Sugestões',
                data: Statistics.produceArray(rows, 'sugestao'),
                color: '#1155cc'
            }, {
                name: 'Livro de reclamações',
                data: Statistics.produceArray(rows, 'comp_book'),
                color: '#f39c12'
            }];

            Statistics.renderStack(
                "responseAvg", 
                "Tempo médio de resposta por direção",
                categories, 
                series
            );
        });
    }

    function renderImcumprimentoPerDirection(start, end) { 
        Statistics.fetchData('by_incump', {start: start, end: end})
        .then(function (data) {
            var rows=data.rows;
            var categories=Object.keys(rows);
            var series = [{
                name: 'Denúncias',
                data: Statistics.produceArray(rows, 'denuncia'),
                color: '#681133'
            }, {
                name: 'Queixas',
                data: Statistics.produceArray(rows, 'queixa'),
                color: '#c82061'
            }, {
                name: 'Reclamação Interna',
                data: Statistics.produceArray(rows, 'reclamacao_interna'),
                color: '#6eb63e'
            }, {
                name: 'Reclamação Externa',
                data: Statistics.produceArray(rows, 'reclamacao'),
                color: '#4e802c'
            }, {
                name: 'Sugestões',
                data: Statistics.produceArray(rows, 'sugestao'),
                color: '#1155cc'
            }];

            Statistics.renderStack(
                "graph4", 
                'Incumprimento de Tratamento das ocorrências por Direção',
                categories, 
                series
            );
        });
    }

    function renderPerMonth(year) {
        Statistics.fetchData('by_month', {year: year})
        .then(function (data) {
            var objects = data.rows;
            var keys = _.sortBy(Object.keys(objects))
            var series = [
                {
                    name: "Queixa",
                    data: Statistics.produceYearArray(objects, 'queixa'),
                    color: '#681133'
                },
                {
                    name: "Denúncias",
                    data: Statistics.produceYearArray(objects, 'denuncia'),
                    color: '#c82061'
                },
                {
                    name: "Sugestão",
                    data: Statistics.produceYearArray(objects, 'sugestao'),
                    color: '#1155cc'
                },
                {
                    name: "Reclamação Externa",
                    data: Statistics.produceYearArray(objects, 'reclamacao'),
                    color: '#4e802c'
                },
                {
                    name: "Reclamação Interna",
                    data: Statistics.produceYearArray(objects, 'reclamacao_interna'),
                    color: '#6eb63e'
                },
                {
                    name: "Livro de reclamação",
                    data: Statistics.produceYearArray(objects, 'comp_book'),
                    color: '#f39c12'
                }
            ];

            var months = [
                'Janeiro',
                'Fevereiro',
                'Março',
                'Abril',
                'Maio',
                'Junho',
                'Julho',
                'Agosto',
                'Setembro',
                'Outubro',
                'Novembro',
                'Dezembro'
            ];

            Statistics.renderBar('Ocorrência por mês',
                'Ocorrencias',
                '',
                'by_month',
                months,
                series
            );    
        });
    }

    function renderTypeDistribution(start, end) {
        Statistics.fetchData('by_type', {start: start, end: end})
        .then(function (data){
            $scope.complaintCount=data.counts.queixa;
            $scope.denounceCount=data.counts.denuncia;
            $scope.internalReclCount=data.counts.reclamacao_interna;
            $scope.reclamationCount=data.counts.reclamacao;
            $scope.sugestionCount=data.counts.sugestao;
            $scope.compbookCount=data.counts.comp_book;
        });
    }

    renderPerMonth(currYear);
    renderByDepartments(startMonth+" 00:00:00", endMonth+" 23:59:59");
    renderResponseTimeAvg(startMonth+" 00:00:00", endMonth+" 23:59:59");
    renderImcumprimentoPerDirection(startMonth+" 00:00:00", endMonth+" 23:59:59");
    renderTypeDistribution(startMonth+" 00:00:00", endMonth+" 23:59:59");
}]);
