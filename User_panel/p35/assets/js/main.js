// Pie Chart Start

var data = [{
    data: [50, 55],
    backgroundColor: [
      "#00e396",
      "#008ffb",
    ],
    borderColor: "#fff"
  }];
  
  var options = {
    tooltips: {
      enabled: true
    },
    plugins: {
      datalabels: {
        formatter: (value, ctx) => {
  
          let sum = ctx.dataset._meta[0].total;
          let percentage = (value * 100 / sum).toFixed(2) + "%";
          return percentage;
  
  
        },
        color: '#fff',
      }
    }
  };
  
  
  var ctx = document.getElementById("pie-chart").getContext('2d');
  var myChart = new Chart(ctx, {
    type: 'pie',
    data: {
    labels: ['Paid', 'Unpaid'],
      datasets: data
    },
    options: options
  });

  // Pir chart end