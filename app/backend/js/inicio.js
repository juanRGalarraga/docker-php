<?php 
require_once(DIR_js.'extensions'.DS.'chart.min.js'); 
require_once(DIR_js.'rbtEvalResult.2.1.js'); 
?>


evalResult = new rbtEvalResult();

function ChartInit(){
    let labels = "";
    let data = "";
    const ctx = document.getElementById('chart_visitas').getContext('2d');
    getAjax({
        archivo: 'getVisitas',
        content : 'inicio'
    }, function (a,b,c,d,e) {
        if (evalResult.Eval(c)) {
            const myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: evalResult.TheResult.labels,
                    datasets: [{
                        label: 'Visitas',
                        data: evalResult.TheResult.data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 2,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
}

// window.addEventListener('DOMContentLoaded', (event) => {
//     ChartInit();
// });

// if(typeof(EventSource) !== "undefined") {

//     var source = new EventSource('../ajax/pruebaEventSource.php');

//     source.onmessage = function(event) {
//         document.getElementById("eventSourceMain").innerHTML = event.data;
//     };

// } else {
//     document.getElementById("result").innerHTML = "Tu navegador no soporta los eventos server-sent...";
// }