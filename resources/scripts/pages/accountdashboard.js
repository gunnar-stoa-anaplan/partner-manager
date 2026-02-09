import ApexCharts from 'apexcharts';

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    /***************************************************************/
    /* Opportunity Chart #opportunity-chart                        */
    /***************************************************************/

    if (document.getElementById('opportunity-chart')) {
        const categories = ['2026-01', '2026-02', '2026-03', '2026-04', '2026-05', '2026-06', '2026-07', '2026-08', '2026-09', '2026-10', '2026-11', '2026-12'];
        const newOptysData = [23686, 30820, 59622, 146465, 78160, 79520, 36148, 48721, 158303, 155174, 104830, 86895];
        const sourcedData = [1545, 1350, 1270, 1830, 1955, 1865, 2034, 2544, 1956, 2211, 1540, 1670];

        const opportunityChartOptions = {
            series: [
                {
                    name: 'New Optys',
                    type: 'area',
                    data: newOptysData
                },
                {
                    name: 'Sourced',
                    type: 'line',
                    data: sourcedData
                }
            ],
            chart: {
                height: 335,
                type: 'line',
                zoom: {
                    enabled: false
                },
                stacked: false,
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit',
                parentHeightOffset: 0
            },
            colors: [
                window.colorMap.bootstrapVars.bodyColor.rgba(0.1),
                window.colorMap.primary[400].hex
            ],
            stroke: {
                width: [1, 2],
                curve: 'smooth',
                colors: [window.colorMap.bootstrapVars.bodyColor.rgba(0.8), window.colorMap.primary[400].hex],
                dashArray: [4, 0],
            },
            fill: {
                type: ['solid', 'solid'],
                opacity: [0.15, 1],
            },
            markers: {
                size: [3, 3],
                colors: [window.colorMap.bootstrapVars.bodyColor.rgba(0.7), window.colorMap.primary[600].hex],
                strokeColors: [window.colorMap.bootstrapVars.bodyColor.rgba(0.7), window.colorMap.primary[600].hex],
                strokeWidth: 2,
                hover: {
                    sizeOffset: 2
                }
            },
            xaxis: {
                categories: categories,
                labels: {
                    style: {
                        colors: window.colorMap.bootstrapVars.bodyColor.hex,
                        fontSize: '12px'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: [
                {
                    seriesName: 'New Optys',
                    min: 20000,
                    max: 170000,
                    tickAmount: 6,
                    labels: {
                        style: {
                            colors: window.colorMap.bootstrapVars.bodyColor.hex,
                            fontSize: '12px'
                        },
                        formatter: function (val) {
                            return val.toLocaleString();
                        }
                    }
                },
                {
                    seriesName: 'Sourced',
                    opposite: true,
                    min: 1200,
                    max: 2700,
                    tickAmount: 6,
                    labels: {
                        style: {
                            colors: window.colorMap.bootstrapVars.bodyColor.hex,
                            fontSize: '12px'
                        },
                        formatter: function (val) {
                            return val.toLocaleString();
                        }
                    }
                }
            ],
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                labels: {
                    colors: window.colorMap.bootstrapVars.bodyColor.hex
                },
                markers: {
                    width: 18,
                    height: 6,
                    radius: 2
                },
                itemMargin: {
                    horizontal: 12,
                    vertical: 0
                }
            },
            grid: {
                borderColor: window.colorMap.bootstrapVars.bodyColor.rgba(0.1),
                strokeDashArray: 3,
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                xaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'dark',
                y: [
                    {
                        formatter: function (val) {
                            return val.toLocaleString();
                        }
                    },
                    {
                        formatter: function (val) {
                            return val.toLocaleString();
                        }
                    }
                ]
            }
        };

        const opportunityChart = new ApexCharts(
            document.getElementById('opportunity-chart'),
            opportunityChartOptions
        );
        opportunityChart.render();
    }

    /***************************************************************/
    /* Enablement Activity Chart #enablement-activity-chart        */
    /***************************************************************/

    if (document.getElementById('enablement-activity-chart')) {
        const categories = ['Finance', 'Supply Chain', 'HR & Workforce', 'Sales & Marketng', 'Other', 'AI', 'Polaris'];

        const enablementActivityChartOptions = {
            series: [
                {
                    name: 'Delivery',
                    data: [65, 59, 90, 81, 56, 55, 40]
                },
                {
                    name: 'Sales',
                    data: [28, 48, 40, 19, 96, 27, 100]
                }
            ],
            chart: {
                height: 350,
                width: '100%',
                type: 'radar',
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit',
                parentHeightOffset: 0,
                sparkline: {
                    enabled: false
                },
                margin: 0,
                padding: {
                    top: -10,
                    right: -10,
                    bottom: -10,
                    left: -10
                }
            },
            colors: [
                window.colorMap.success[400].hex,
                window.colorMap.primary[400].hex
            ],
            stroke: {
                width: 0,
                colors: [window.colorMap.success[400].hex, window.colorMap.primary[400].hex]
            },
            fill: {
                opacity: 0.2
            },
            markers: {
                size: 5,
                colors: [window.colorMap.success[400].hex, window.colorMap.primary[400].hex],
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: {
                    sizeOffset: 2
                }
            },
            xaxis: {
                categories: categories,
                labels: {
                    style: {
                        colors: window.colorMap.bootstrapVars.bodyColor.hex,
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                max: 100,
                tickAmount: 5,
                show: true,
                labels: {
                    show: true,
                    style: {
                        colors: window.colorMap.bootstrapVars.bodyColor.hex,
                        fontSize: '10px'
                    }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                labels: {
                    colors: window.colorMap.bootstrapVars.bodyColor.hex
                },
                markers: {
                    width: 18,
                    height: 6,
                    radius: 2
                },
                itemMargin: {
                    horizontal: 12,
                    vertical: 0
                }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return val;
                    }
                }
            },
            grid: {
                borderColor: window.colorMap.bootstrapVars.bodyColor.rgba(0.1),
                strokeDashArray: 3
            },
            plotOptions: {
                radar: {
                    size: undefined,
                    offsetX: 0,
                    offsetY: 0,
                    padding: 0,
                    polygons: {
                        strokeColors: window.colorMap.bootstrapVars.bodyColor.rgba(0.1),
                        connectorColors: window.colorMap.bootstrapVars.bodyColor.rgba(0.1),
                        fill: {
                            colors: undefined
                        }
                    }
                }
            }
        };

        const enablementActivityChart = new ApexCharts(
            document.getElementById('enablement-activity-chart'),
            enablementActivityChartOptions
        );
        enablementActivityChart.render();
    }

    /***************************************************************/
    /* Data Stream Chart #data-stream-chart                        */
    /***************************************************************/

    if (document.getElementById('data-stream-chart')) {
        const categories = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
        const positiveData = [0, 25, 70, 85, 25, 0, 60];
        const negativeData = [-45, -50, -30, -25, -60, -120, 0];

        const dataStreamChartOptions = {
            series: [
                {
                    name: 'Positive',
                    data: positiveData
                },
                {
                    name: 'Negative',
                    data: negativeData
                }
            ],
            chart: {
                type: 'bar',
                height: 350,
                maxHeight: '100%',
                stacked: true,
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit',
                parentHeightOffset: 0
            },
            colors: [
                window.colorMap.success[400].hex,
                window.colorMap.primary[500].hex
            ],
            plotOptions: {
                bar: {
                    columnWidth: '50%',
                    borderRadius: 1
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: categories,
                labels: {
                    style: {
                        colors: window.colorMap.bootstrapVars.bodyColor.hex,
                        fontSize: '12px'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                min: -150,
                max: 150,
                tickAmount: 6,
                labels: {
                    style: {
                        colors: window.colorMap.bootstrapVars.bodyColor.hex,
                        fontSize: '12px'
                    },
                    formatter: function (val) {
                        return val;
                    }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                labels: {
                    colors: window.colorMap.bootstrapVars.bodyColor.hex
                },
                markers: {
                    width: 18,
                    height: 6,
                    radius: 2
                },
                itemMargin: {
                    horizontal: 12,
                    vertical: 0
                }
            },
            grid: {
                borderColor: window.colorMap.bootstrapVars.bodyColor.rgba(0.1),
                strokeDashArray: 3,
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                xaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return val;
                    }
                }
            }
        };

        const dataStreamChart = new ApexCharts(
            document.getElementById('data-stream-chart'),
            dataStreamChartOptions
        );
        dataStreamChart.render();
    }

    /***************************************************************/
    /* Product Area Distribution Chart #product-area-distribution-chart */
    /***************************************************************/

    if (document.getElementById('product-area-distribution-chart')) {
        const countries = ['USA', 'Germany', 'Australia', 'Canada', 'France'];
        const data = [25, 30, 15, 10, 20];

        const productAreaDistributionChartOptions = {
            series: data,
            chart: {
                type: 'pie',
                height: 350,
                maxHeight: '100%',
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit',
                parentHeightOffset: 0
            },
            colors: [
                window.colorMap.primary[100].hex,
                window.colorMap.primary[400].hex,
                window.colorMap.success[100].hex,
                window.colorMap.success[300].hex,
                window.colorMap.success[500].hex
            ],
            labels: countries,
            dataLabels: {
                enabled: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '0%'
                    }
                }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                labels: {
                    colors: window.colorMap.bootstrapVars.bodyColor.hex
                },
                markers: {
                    width: 12,
                    height: 12,
                    radius: 2
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 2
                }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return val + '%';
                    }
                },
                style: {
                    fontSize: '14px',
                    fontFamily: 'inherit'
                },
                custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                    const country = w.globals.labels[seriesIndex];
                    const value = series[seriesIndex];
                    return '<div class="apexcharts-tooltip-box" style="padding: 2px 7px; background-color: var(--bs-body-bg);">' +
                        '<span style="color: var(--bs-body-color); font-size: 0.825rem !important;">' + country + ': ' + value + '%</span>' +
                        '</div>';
                }
            },
            stroke: {
                width: 1,
                colors: ['var(--bs-body-bg)']
            }
        };

        const productAreaDistributionChart = new ApexCharts(
            document.getElementById('product-area-distribution-chart'),
            productAreaDistributionChartOptions
        );
        productAreaDistributionChart.render();
    }

    /***************************************************************/
    /* Campaign Modal Functionality                                */
    /***************************************************************/

    const discountSlider = document.getElementById('discountAmount');
    const discountValue = document.getElementById('discountAmountValue');

    if (discountSlider && discountValue) {
        discountSlider.addEventListener('input', function () {
            discountValue.textContent = this.value;
        });
    }

    // Toggle discount amount field visibility based on offer type
    const offerTypeRadios = document.querySelectorAll('input[name="offerType"]');
    const discountAmountContainer = document.getElementById('discountAmount')?.closest('.mb-3');

    if (offerTypeRadios.length && discountAmountContainer) {
        offerTypeRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                discountAmountContainer.style.display = (this.value === 'discount') ? 'block' : 'none';
            });
        });
    }

    // Show/hide custom audience options when "Custom Segment" is selected
    const targetAudienceSelect = document.getElementById('targetAudience');
    if (targetAudienceSelect) {
        targetAudienceSelect.addEventListener('change', function () {
            if (this.value === 'custom') {
                if (!document.getElementById('customAudienceOptions')) {
                    const customOptions = document.createElement('div');
                    customOptions.id = 'customAudienceOptions';
                    customOptions.className = 'mt-3 p-3 border border-light rounded';
                    customOptions.innerHTML = `
                        <p class="fw-bold mb-2">Define Custom Audience</p>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="segmentRecent">
                                <label class="form-check-label" for="segmentRecent">
                                    Recently active (last 30 days)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="segmentHighValue">
                                <label class="form-check-label" for="segmentHighValue">
                                    High-value subscribers
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="segmentLocation">
                                <label class="form-check-label" for="segmentLocation">
                                    Specific locations
                                </label>
                            </div>
                        </div>
                    `;
                    targetAudienceSelect.parentNode.appendChild(customOptions);
                } else {
                    document.getElementById('customAudienceOptions').style.display = 'block';
                }
            } else if (document.getElementById('customAudienceOptions')) {
                document.getElementById('customAudienceOptions').style.display = 'none';
            }
        });
    }

    // Update form fields based on campaign type
    const campaignTypeSelect = document.getElementById('campaignType');
    if (campaignTypeSelect) {
        campaignTypeSelect.addEventListener('change', function () {
            const offerRadios = document.querySelectorAll('input[name="offerType"]');

            switch (this.value) {
                case 'acquisition':
                    offerRadios.forEach(radio => {
                        if (radio.value === 'freeTrial') radio.checked = true;
                        else radio.checked = false;
                    });
                    document.getElementById('offerFreeTrial').dispatchEvent(new Event('change'));
                    break;

                case 'retention':
                    offerRadios.forEach(radio => {
                        if (radio.value === 'discount') radio.checked = true;
                        else radio.checked = false;
                    });
                    document.getElementById('offerDiscount').dispatchEvent(new Event('change'));
                    if (discountSlider) {
                        discountSlider.value = 25;
                        discountValue.textContent = '25';
                    }
                    break;

                case 'upgrade':
                    offerRadios.forEach(radio => {
                        if (radio.value === 'upgrade') radio.checked = true;
                        else radio.checked = false;
                    });
                    document.getElementById('offerUpgrade').dispatchEvent(new Event('change'));
                    break;

                case 'winback':
                    offerRadios.forEach(radio => {
                        if (radio.value === 'bundle') radio.checked = true;
                        else radio.checked = false;
                    });
                    document.getElementById('offerBundle').dispatchEvent(new Event('change'));
                    break;
            }
        });
    }

    // Handle form submission - Save Draft
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function () {
            const campaignName = document.getElementById('campaignName').value;
            if (!campaignName) {
                showAlert('danger', 'Please enter a campaign name');
                return;
            }

            showAlert('success', 'Campaign draft saved successfully');
        });
    }

    // Handle form submission - Launch Campaign
    const launchCampaignBtn = document.getElementById('launchCampaignBtn');
    if (launchCampaignBtn) {
        launchCampaignBtn.addEventListener('click', function () {
            const campaignName = document.getElementById('campaignName').value;
            const campaignType = document.getElementById('campaignType').value;
            const startDate = document.getElementById('startDate').value;
            const targetAudience = document.getElementById('targetAudience').value;

            if (!campaignName || !campaignType || !startDate || !targetAudience) {
                showAlert('danger', 'Please fill in all required fields');
                return;
            }

            showAlert('success', 'Campaign launched successfully!');

            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('buildCampaignModal'));
                if (modal) modal.hide();
            }, 1500);
        });
    }

    // Update discount value display
    document.getElementById('discountAmount').addEventListener('input', function () {
        document.getElementById('discountAmountValue').textContent = this.value + '%';
    });

    // Toggle discount section based on offer type selection
    document.querySelectorAll('input[name="offerType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('discountSection').style.display =
                this.value === 'discount' ? 'block' : 'none';
        });
    });

    // Show alert function
    function showAlert(type, message) {
        let alertContainer = document.querySelector('.campaign-alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.className = 'campaign-alert-container position-fixed top-0 end-0 p-3';
            alertContainer.style.zIndex = '5000';
            document.body.appendChild(alertContainer);
        }

        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type} alert-dismissible fade show`;
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        alertContainer.appendChild(alertElement);

        setTimeout(() => {
            alertElement.classList.remove('show');
            setTimeout(() => {
                alertElement.remove();
            }, 150);
        }, 5000);
    }
});
