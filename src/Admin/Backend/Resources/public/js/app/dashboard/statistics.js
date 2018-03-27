angular.module("app")
.controller("StatisticsController", ['$scope', 'Statistics',
function ($scope, Statistics) {
    console.info("--- init statistics controller ---");

    function renderByDepartments(year) {
        Statistics.fetchData('by_department', {year: year})
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

    function renderResponseTimeAvg() {
        Statistics.fetchData('ajax/avgResponseTime', {})
        .then(function (data){
            var render = [
                {
                    name: "Denúncias",
                    data: [parseInt(data['denuncia'][0].count)],
                    color: "#c82061"
                },
                {
                    name: "Queixas",
                    data: [parseInt(data['queixa'][0].count)],
                    color: "#681133"
                },
                {
                    name: "Reclamações externas",
                    data: [parseInt(data['reclamacao'][0].count)],
                    color: "#4e802c"
                },
                {
                    name: "Reclamações internas",
                    data: [parseInt(data['reclamacao_interna'][0].count)],
                    color: "#6eb63e"
                },
                {
                    name: "Sugestões",
                    data: [parseInt(data['sugestao'][0].count)],
                    color: "#1155cc"
                },
            ];

            Statistics.renderBar('Tempo médio de resposta por direção',
                'Dias',
                '',
                'responseAvg',
                ["Dias"],
                render
            );
        });

    }

    function renderImcumprimentoPerDirection() { 
        Statistics.fetchData('by_incump', {year: 2018})
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
            }];

            Statistics.renderStack(
                "graph4", 
                'Incumprimento de Tratamento das ocorrências por Direção',
                categories, 
                series
            );
        });
    }

    renderByDepartments(2018);
    renderResponseTimeAvg();
    renderImcumprimentoPerDirection();
}]);