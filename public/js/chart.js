var chartVal = []; // グラフデータ（描画するデータ）

var date = [];
var temperature_for = [];
var temperature_cur = [];

var pressure_for = [];
var pressure_cur = [];

var gridline_color = [];
var gridline_width = [];

// ページ読み込み時にグラフを描画
setData();
drawChart1(); // グラフ描画処理を呼び出す
drawChart2(); // グラフ描画処理を呼び出す


// functions ------------------------------------------------------------

function drawChart1() {
    var ctx = document.getElementById("chart1");
    window.myChart1 = new Chart(ctx, {
        // グラフの種類：折れ線グラフを指定
        type: 'line',
        data: {
            // x軸の各メモリ
            labels: date,
            datasets: [
                {
                    label: '予想気温',
                    data: temperature_for,
                    borderColor: "#FF9999",
                    backgroundColor: "#00000000",
                    pointRadius: 0
                },
                {
                    label: '実際の気温',
                    data: temperature_cur,
                    borderColor: "#FF3366",
                    backgroundColor: "#00000000",
                }
            ],
        },
        options: {
            title: {
                display: false,
                text: '気温'
            },
            scales: {
                xAxes: xAxes,
                yAxes: [{
                    ticks: {
                        suggestedMax: getMax(temperature_for),
                        suggestedMin: getMin(temperature_for),
                        stepSize: 10,  // 縦メモリのステップ数
                        callback: function(value, index, values){
                            return  '     ' + value +  '度'  // 各メモリのステップごとの表記（valueは各ステップの値）
                        }
                    },
                    // grace: '10%'
                }]
            },
            responsive: true,
            maintainAspectRatio: false,
        }
    });
}

function drawChart2() {
    var ctx = document.getElementById("chart2");
    window.myChart2 = new Chart(ctx, {
        // グラフの種類：折れ線グラフを指定
        type: 'line',
        data: {
            // x軸の各メモリ
            labels: date,
            datasets: [
                {
                    label: '予想気圧',
                    data: pressure_for,
                    borderColor: "lightgray",
                    backgroundColor: "#00000000",
                    pointRadius: 0
                },
                {
                    label: '実際の気圧',
                    data: pressure_cur,
                    borderColor: "darkgray",
                    backgroundColor: "#00000000",
                }
            ],
        },
        options: {
            title: {
                display: false,
                text: '気圧'
            },
            scales: {
                xAxes: xAxes,
                yAxes: [{
                    ticks: {
                        suggestedMax: getMax(pressure_for),
                        suggestedMin: getMin(pressure_for),
                        stepSize: 10,  // 縦メモリのステップ数
                        callback: function(value, index, values){
                            return  value +  'hPa'  // 各メモリのステップごとの表記（valueは各ステップの値）
                        }
                    }
                    }]
            },
            responsive: true,
            maintainAspectRatio: false,
        }
    });
}





function setData() {
    const weather = Laravel.weather;
    // console.log(weather);

    now = new Date();

    date_prev = '';
    weather.forEach(function(element) {

        // 軸や色の設定
        // forecast と currentで同時刻でデータが重複するため、forecastで処理する
        if (element['mode'] == 'forecast') {
            from = new Date(element['datetime']['date']);
            to = new Date(element['datetime']['date']);
            to.setHours(to.getHours() + 3);
    
            if (date_prev != element['date_j']) {
                date.push(element['date_j'] + '日');
                gridline_color.push('#222222');
                gridline_width.push(1);
            } else {
                date.push('');
                if (element['hour'] == 12) {
                    gridline_color.push('lightgray');
                    gridline_width.push(1);
                } else {
                    if (from <= now && now < to) {
                        gridline_color.push('teal');
                        gridline_width.push(3);
                    } else {
                        gridline_color.push('#ffffff');
                        gridline_width.push(1);
                    }
                }
            }

        }

        // グラフ値の設定
        if (element['mode'] == 'current') {
            temperature_cur.push(element['temp']);
            pressure_cur.push(element['pressure']);  
        }
        if (element['mode'] == 'forecast') {
            temperature_for.push(element['temp']);                    
            pressure_for.push(element['pressure']);  
        }

        date_prev = element['date_j'];                  
    });

    setAxes();
}

function setAxes() {
    xAxes = [{
        gridLines: {    // 目盛線
            color: gridline_color,
            // lineWidth: 1
        },
        ticks: {
            padding: 3,
        }
    }];

    console.log(gridline_color);
    console.log(gridline_width);
    
}

function getMax(array) {
    return array.reduce((a, b) => Math.max(a, b), -Infinity);
}

function getMin(array) {
    return array.reduce((a, b) => Math.min(a, b), Infinity);
}


