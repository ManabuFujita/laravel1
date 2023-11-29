var chartVal = []; // グラフデータ（描画するデータ）

var date = [];
var temperature_for = [];
var temperature_cur = [];

var rain_for = [];
var rain_cur = [];

var pressure_for = [];
var pressure_cur = [];

var pressure_for_diff_small = [];
var pressure_for_diff_large = [];
var pressure_cur_diff_small = [];
var pressure_cur_diff_large = [];

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
                    label: '実際の気温',
                    data: temperature_cur,
                    borderColor: "#FF3366",
                    fill: false,
                    backgroundColor: "#FF3366",
                    yAxisID: "temp"
                },
                {
                    label: '予想気温',
                    data: temperature_for,
                    borderColor: "#FFBBBB",
                    backgroundColor: "#00000000",
                    pointRadius: 0,
                    yAxisID: "temp"
                },
                {
                    label: '実際の降水量',
                    data: rain_cur,
                    borderColor: "#0099FF",
                    backgroundColor: "#0099FF",
                    // fill: true,
                    pointRadius: 0,
                    yAxisID: "rain",
                    borderWidth: 1,
                    // showLine: false
                },
                {
                    label: '予想降水量',
                    data: rain_for,
                    borderColor: "#99FFFF",
                    backgroundColor: "#99FFFF",
                    // fill: true,
                    pointRadius: 0,
                    yAxisID: "rain",
                    borderWidth: 1,
                    // showLine: false
                }
            ],
        },
        options: {
            title: {
                display: false,
                text: '気温'
            },
            legend: {
                display: false
            },
            scales: {
                xAxes: xAxes,
                yAxes: [
                    {
                        id: 'temp',
                        position: 'left',
                        ticks: {
                            suggestedMax: getMax(temperature_for),
                            suggestedMin: getMin(temperature_for),
                            stepSize: 10,  // 縦メモリのステップ数
                            callback: function(value, index, values){
                                return  '     ' + value + '\u{00B0}C'  // 各メモリのステップごとの表記（valueは各ステップの値）
                            }
                        },
                        // grace: '10%'
                    },
                    {
                        id: 'rain',
                        position: 'right',
                        ticks: {
                            suggestedMax: 15,
                            suggestedMin: 0,
                            stepSize: 10,  // 縦メモリのステップ数
                            callback: function(value, index, values){
                                return  '     ' + value + 'mm'  // 各メモリのステップごとの表記（valueは各ステップの値）
                            }
                        },
                        // grace: '10%'
                    },
                ]
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
                    label: '実際の気圧',
                    data: pressure_cur,
                    borderColor: "dimgray",
                    fill: false,
                    backgroundColor: "dimgray",
                },
                {
                    label: '気圧差大（予想）',
                    data: pressure_for_diff_large,
                    borderColor: "lightgray",
                    backgroundColor: "#FFCC66",
                    pointRadius: 0,
                    spanGaps: false // データがない点は途切れる
                },
                {
                    label: '気圧差中（予想）',
                    data: pressure_for_diff_small,
                    borderColor: "lightgray",
                    backgroundColor: "#FFFFCC",
                    pointRadius: 0,
                    spanGaps: false // データがない点は途切れる
                },
                {
                    label: '気圧差大（実際）',
                    data: pressure_cur_diff_large,
                    borderColor: "lightgray",
                    backgroundColor: "#FFCC66",
                    fill: true,
                    pointRadius: 0,
                    spanGaps: false // データがない点は途切れる
                },
                {
                    label: '気圧差中（実際）',
                    data: pressure_cur_diff_small,
                    borderColor: "lightgray",
                    backgroundColor: "#FFFFCC",
                    pointRadius: 0,
                    spanGaps: false // データがない点は途切れる
                },
                {
                    label: '予想気圧',
                    data: pressure_for,
                    borderColor: "lightgray",
                    backgroundColor: "#00000000",
                    pointRadius: 0
                },
            ],
        },
        options: {
            title: {
                display: false,
                text: '気圧'
            },
            legend: {
                display: false
            },
            scales: {
                xAxes: xAxes,
                yAxes: [
                    {
                        id: 'pressure',
                        position: 'left',
                        ticks: {
                            suggestedMax: getMax(pressure_for),
                            suggestedMin: getMin(pressure_for),
                            stepSize: 10,  // 縦メモリのステップ数
                            callback: function(value, index, values){
                                return  value +  'hPa'  // 各メモリのステップごとの表記（valueは各ステップの値）
                            }
                        }
                    },
                    {
                        id: 'none',
                        position: 'right',
                        ticks: {
                            suggestedMax: getMax(pressure_for),
                            suggestedMin: getMin(pressure_for),
                            stepSize: 10,  // 縦メモリのステップ数
                            callback: function(value, index, values){
                                return  value +  'hPa'  // 各メモリのステップごとの表記（valueは各ステップの値）
                            }
                        }
                    },
                ]
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
    var pressure_prev = 0;
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

        // 気圧差初期化
        if (pressure_prev == 0) {
            pressure_prev = element['pressure'];
        }


        // グラフ値の設定
        if (element['mode'] == 'forecast') {
            temperature_for.push(element['temp']);                    
            rain_for.push(element['rain'] + 0.2);                    
            pressure_for.push(element['pressure']);  

            // 気圧差
            if (Math.abs(pressure_prev - element['pressure']) > 1) {
                pressure_for_diff_large.push(element['pressure']);
            } else {
                pressure_for_diff_large.push(null);
            }

            if (Math.abs(pressure_prev - element['pressure']) >= 1) {
                pressure_for_diff_small.push(element['pressure']);
            } else {
                pressure_for_diff_small.push(null);
            }

        }
        if (element['mode'] == 'current') {
            temperature_cur.push(element['temp']);
            rain_cur.push(element['rain'] * 3 + 0.2); // 現在天気の降水量は1時間単位のため3倍する              
            pressure_cur.push(element['pressure']);  

            // 気圧差
            if (Math.abs(pressure_prev - element['pressure']) > 1) {
                pressure_cur_diff_large.push(element['pressure']);
            } else {
                pressure_cur_diff_large.push(null);
            }

            if (Math.abs(pressure_prev - element['pressure']) >= 1) {
                pressure_cur_diff_small.push(element['pressure']);
            } else {
                pressure_cur_diff_small.push(null);
            }

        }
        pressure_prev = element['pressure'];

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

    // console.log(gridline_color);
    // console.log(gridline_width);
    
}

function getMax(array) {
    return array.reduce((a, b) => Math.max(a, b), -Infinity);
}

function getMin(array) {
    return array.reduce((a, b) => Math.min(a, b), Infinity);
}


