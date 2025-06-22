const ctx = document.getElementById('fileChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['PDF', 'Word', 'Excel'],
        datasets: [{
            label: 'File Type Distribution',
            data: [35, 25, 40],
            backgroundColor: [
                'rgba(255, 99, 132, 0.6)',
                'rgba(255, 159, 64, 0.6)',
                'rgba(153, 102, 255, 0.6)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#444',
                    padding: 15,
                    font: {
                        size: 14
                    }
                }
            }
        }
    }
});
