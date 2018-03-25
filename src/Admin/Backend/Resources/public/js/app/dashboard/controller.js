angular.module("app")
.controller("DashboardController", ['$scope', 'Statistics',
function ($scope, Statistics) {
    console.info("--- init dash controller ---");
    function renderDepartments(year) {
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
            }];

            Statistics.renderStack(
                "by_department", 
                'Total de ocorrência por direção',
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
                    name: "Sugestao",
                    data: Statistics.produceYearArray(objects, 'sugestao'),
                    color: '#1155cc'
                },
                {
                    name: "Reclamacao Externa",
                    data: Statistics.produceYearArray(objects, 'reclamacao'),
                    color: '#4e802c'
                },
                {
                    name: "Reclamacao Interna",
                    data: Statistics.produceYearArray(objects, 'reclamacao_interna'),
                    color: '#6eb63e'
                },
                {
                    name: "Livro de reclamacao",
                    data: Statistics.produceYearArray(objects, 'comp_book'),
                    color: '#f39c12'
                },
            ];

            var months = [
                'Janeiro',
                'Fevereiro',
                'Marco',
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

    renderPerMonth(2018);
    renderDepartments(2018);
}]);