import JsBarcode from "jsbarcode";
import Chart from 'chart.js/auto';

const chartUrl = `${window.location.pathname}/logs`

async function getChartData() {
    try {
        const response = await fetch(chartUrl);
        if (response.ok) {
            const data = await response.json();
            return data;
        } else {
            Toast.fire({
                icon: "error",
                text: response.message || "Erreur de création des graphiques."
            });
        }
    } catch (error) {
        Toast.fire({
            icon: "error",
            text: response.message || "Erreur de création des graphiques."
        });
    }
}

function getFrenchMonth(month) {
    const months = [
        "Janvier",
        "Février",
        "Mars",
        "Avril",
        "Mai",
        "Juin",
        "Juillet",
        "Août",
        "Septembre",
        "Octobre",
        "Novembre",
        "Décembre"
    ];

    const monthIndex = parseInt(month) - 1;

    return months[monthIndex];
}

function syncLabels(labels, soldData, acquiredData) {
    const allLabels = new Set([...Object.keys(soldData), ...Object.keys(acquiredData)]);

    let returnArray = Array.from(allLabels).map(label => {
        return {
            label: label,
            sold: soldData[label] || 0,
            acquired: acquiredData[label] || 0
        };
    });

    returnArray.sort((a, b) => a.label.localeCompare(b.label));

    return returnArray
}

function buildWeeksChart(logs) {
    const weekLabels = Object.keys(logs.weeks.sold);
    const weekSoldData = logs.weeks.sold;
    const weekAcquiredData = logs.weeks.acquired;

    let syncedData = syncLabels(weekLabels, weekSoldData, weekAcquiredData);

    if (syncedData.length !== 0) {
        const firstWeek = parseInt(syncedData[0].label, 10);

        const missingWeeks = [];
        for (let week = 1; week < firstWeek; week++) {
            const weekString = week.toString().padStart(2, '0');
            missingWeeks.push({
                label: weekString,
                sold: 0,
                acquired: 0
            });
        }

        for (let i = 0; i < syncedData.length - 1; i++) {
            const currentWeek = parseInt(syncedData[i].label, 10);
            const nextWeek = parseInt(syncedData[i + 1].label, 10);

            for (let week = currentWeek + 1; week < nextWeek; week++) {
                const weekString = week.toString().padStart(2, '0');
                missingWeeks.push({
                    label: weekString,
                    sold: 0,
                    acquired: 0
                });
            }
        }

        syncedData = [...missingWeeks, ...syncedData];
    }

    syncedData.sort((a, b) => a.label.localeCompare(b.label));

    syncedData.forEach(week => {
        week.label = `S${week.label}`;
    });

    const weekChartData = {
        labels: syncedData.map(item => item.label),
        datasets: [
            {
                label: 'Ventes',
                data: syncedData.map(item => item.sold),
                backgroundColor: '#BAAB5C',
                fill: false
            },
            {
                label: 'Entrées en stock',
                data: syncedData.map(item => item.acquired),
                backgroundColor: '#542292',
                fill: false
            }
        ]
    };

    return new Chart("weekChart", {
        type: "bar",
        data: weekChartData,
        options: {
            scales: {
                y: {
                    ticks: {
                        stepSize: function (context) {
                            const range = context.max - context.min;
                            return range >= 1 ? Math.max(1, Math.floor(range / 10)) : 1;
                        }
                    },
                    beginAtZero: true
                }
            }
        }
    });
}

function buildMonthsChart(months) {
    const monthLabels = Object.keys(months.sold);
    const monthSoldData = months.sold;
    const monthAcquiredData = months.acquired;

    let syncedData = syncLabels(monthLabels, monthSoldData, monthAcquiredData);

    if (syncedData.length !== 0) {
        const firstMonth = parseInt(syncedData[0].label, 10);

        const missingMonths = [];
        for (let month = 1; month < firstMonth; month++) {
            const monthString = month.toString().padStart(2, '0');
            missingMonths.push({
                label: monthString,
                sold: 0,
                acquired: 0
            });
        }

        for (let i = 0; i < syncedData.length - 1; i++) {
            const currentMonth = parseInt(syncedData[i].label, 10);
            const nextMonth = parseInt(syncedData[i + 1].label, 10);

            for (let month = currentMonth + 1; month < nextMonth; month++) {
                const monthString = month.toString().padStart(2, '0');
                missingMonths.push({
                    label: monthString,
                    sold: 0,
                    acquired: 0
                });
            }
        }

        syncedData = [...missingMonths, ...syncedData];
    }


    syncedData.sort((a, b) => a.label.localeCompare(b.label));

    syncedData.forEach(month => {
        month.label = getFrenchMonth(month.label);
    });

    const monthChartData = {
        labels: syncedData.map(item => item.label),
        datasets: [
            {
                label: 'Ventes',
                data: syncedData.map(item => item.sold),
                backgroundColor: '#BAAB5C',  
                borderWidth: 0
            },
            {
                label: 'Entrées en stock',
                data: syncedData.map(item => item.acquired),
                backgroundColor: '#542292',
                borderWidth: 1
            }
        ]
    };

    return new Chart("monthChart", {
        type: "bar",
        data: monthChartData,
        options: {
            scales: {
                y: {
                    ticks: {
                        stepSize: function (context) {
                            const range = context.max - context.min;
                            return range >= 1 ? Math.max(1, Math.floor(range / 10)) : 1;
                        }
                    },
                    beginAtZero: true // Ensures the y-axis starts at 0
                }
            }
        }
    });
}

function buildYearsChart(years) {
    const yearLabels = Object.keys(years.sold);
    const yearSoldData = years.sold;
    const yearAcquiredData = years.acquired;

    const syncedData = syncLabels(yearLabels, yearSoldData, yearAcquiredData);

    const yearChartData = {
        labels: syncedData.map(item => item.label),
        datasets: [
            {
                label: 'Ventes',
                data: syncedData.map(item => item.sold),
                backgroundColor: '#BAAB5C',
                fill: false
            },
            {
                label: 'Entrées en stock',
                data: syncedData.map(item => item.acquired),
                backgroundColor: '#542292',
                fill: false
            }
        ]
    };

    return new Chart("yearChart", {
        type: "bar",
        data: yearChartData,
        options: {
            scales: {
                y: {
                    ticks: {
                        stepSize: function (context) {
                            const range = context.max - context.min;
                            return range >= 1 ? Math.max(1, Math.floor(range / 10)) : 1;
                        }
                    },
                    beginAtZero: true // Ensures the y-axis starts at 0
                }
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", async function () {
    JsBarcode("#barcode").init();

    let logs = await getChartData();

    if (logs && logs.logs) {
        buildWeeksChart(logs.logs);
        buildMonthsChart(logs.logs.months);
        buildYearsChart(logs.logs.years);
    } else {
        console.error("Error: logs is undefined or has an unexpected structure.");
    }
});